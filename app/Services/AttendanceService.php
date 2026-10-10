<?php

namespace App\Services;

use App\Models\User;
use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\StudentAttendance;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use App\Models\Subject;

class AttendanceService
{
    /**
     * Get monthly attendance summary for a list of users.
     *
     * @param int $month
     * @param int $year
     * @param Request $request
     * @return Collection
     */
    public function getMonthlySummary(int $month, int $year, Request $request): Collection
    {
        $start = Carbon::createFromDate($year, $month, 1)->startOfDay();
        $end = $start->copy()->endOfMonth()->endOfDay();
        $totalDays = $start->daysInMonth;
        $scope = $request->input('scope');
        $isStaffFaculty = $scope === 'staff_faculty';
        $isStudents = $scope === 'students';

        $roles = match ($scope) {
            'staff_faculty' => ['staff', 'faculty'],
            'students' => ['student'],
            default => ['student', 'faculty', 'staff'],
        };

        $usersQuery = User::select('id', 'name', 'email', 'role')
            ->with([
                'studentProfile.course',
                'studentProfile.semester',
                'studentProfile.section',
                'studentProfile.session',
                'studentProfile.batch',
                'facultyProfile',
                'staffProfile',
            ])
            ->whereIn('role', $roles)
            ->orderBy('name');

        if ($isStaffFaculty) {
            $this->applyStaffFacultyFilters($usersQuery, $request);
        } else {
            $this->applyAcademicFilters($usersQuery, $request);
            if ($isStudents) {
                $this->applyStudentExportFilters($usersQuery, $request);
            }
        }

        $users = $usersQuery->get();
        $userIds = $users->pluck('id');

        $staffPresent = ($isStudents || $isStaffFaculty) ? collect() : $this->getStaffPresentDays($userIds, $start, $end, includeLate: true);
        $studentPresent = $isStaffFaculty ? collect() : $this->getStudentPresentDays($userIds, $start, $end, $request);
        $approvedLeaves = $isStaffFaculty ? collect() : $this->getApprovedLeaveDays($userIds, $start, $end);
        $pendingLeaves = $this->getPendingLeaveCounts($userIds, $start, $end);
        $staffPunches = $isStaffFaculty ? $this->staffPunchesByUser($userIds, $start, $end) : collect();
        $staffLeaves = $isStaffFaculty ? $this->approvedLeavesForStudents($userIds) : collect();
        $holidays = $isStaffFaculty ? $this->holidaysForMonth($start) : collect();

        $subject = (! $isStaffFaculty && $request->filled('subject_id')) ? Subject::find($request->subject_id) : null;

        return $users->map(function (User $user) use ($staffPresent, $staffPunches, $staffLeaves, $holidays, $start, $studentPresent, $approvedLeaves, $pendingLeaves, $totalDays, $subject, $isStaffFaculty, $isStudents) {
            if ($isStaffFaculty) {
                $profile = $user->role === 'faculty' ? $user->facultyProfile : $user->staffProfile;
                $report = $this->staffMonthReport(
                    $staffPunches->get($user->id, collect()),
                    $staffLeaves->get($user->id, collect()),
                    $holidays,
                    $start
                );

                return [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'employee_id' => $profile?->employee_id ?? 'N/A',
                    'designation' => $profile?->designation ?? 'N/A',
                    'department' => $profile?->department ?? 'N/A',
                    'total_days' => $report['total'],
                    'present_days' => $report['present'],
                    'approved_leave_days' => $report['leave'],
                    'absent_days' => $report['absent'],
                    'pending_leave_requests' => (int) ($pendingLeaves[$user->id] ?? 0),
                ];
            }

            $presentDays = $user->role === 'student'
                ? (int) ($studentPresent[$user->id] ?? 0)
                : (int) ($staffPresent[$user->id] ?? 0);

            $leaveDays = (int) ($approvedLeaves[$user->id] ?? 0);
            $absentDays = max($totalDays - $presentDays - $leaveDays, 0);

            $row = [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ];

            if ($isStudents) {
                $row['roll_no'] = $user->studentProfile?->roll_no ?? 'N/A';
                $row['university_registration_number'] = $user->studentProfile?->university_registration_number ?? 'N/A';
                $row['batch'] = $user->studentProfile?->batch?->name ?? 'N/A';
            }

            return array_merge($row, [
                'course' => $user->studentProfile?->course?->name ?? 'N/A',
                'semester' => $user->studentProfile?->semester?->name ?? 'N/A',
                'section' => $user->studentProfile?->section?->name ?? 'N/A',
                'subject' => $subject ? $subject->name : 'All Subjects',
                'session' => $user->studentProfile?->session?->name ?? 'N/A',
                'total_days' => $totalDays,
                'present_days' => $presentDays,
                'approved_leave_days' => $leaveDays,
                'absent_days' => $absentDays,
                'pending_leave_requests' => (int) ($pendingLeaves[$user->id] ?? 0),
            ]);
        });
    }

    /**
     * Limit a user query to staff/faculty members for attendance export.
     */
    public function applyStaffFacultyFilters($query, Request $request): void
    {
        if ($request->filled('role') && in_array($request->role, ['staff', 'faculty'], true)) {
            $query->where('role', $request->role);
        }

        if ($request->filled('user_id')) {
            $query->where('id', $request->user_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }
    }

    /**
     * Limit a user query to matching students for attendance export.
     */
    public function applyStudentExportFilters($query, Request $request): void
    {
        if ($request->filled('student_id')) {
            $query->where('id', $request->student_id);
        }

        if ($request->filled('student_search')) {
            $search = $request->student_search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('studentProfile', function ($sp) use ($search) {
                        $sp->where('roll_no', 'like', "%{$search}%")
                            ->orWhere('university_registration_number', 'like', "%{$search}%");
                    });
            });
        }
    }

    /**
     * Apply academic filters to a user query.
     */
    public function applyAcademicFilters($query, Request $request)
    {
        $isUserQuery = $query->getModel() instanceof User;

        if ($request->filled('role')) {
            if ($isUserQuery) {
                $query->where('role', $request->role);
            } else {
                $query->whereHas('user', function($q) use ($request) {
                    $q->where('role', $request->role);
                });
            }
        }

        if ($request->filled('user_id')) {
            $query->where($isUserQuery ? 'id' : 'user_id', $request->user_id);
        }

        if ($request->filled('course_id') || $request->filled('batch_id') || $request->filled('semester_id') || $request->filled('section_id') || $request->filled('session_id') || $request->filled('subject_id')) {
            $relation = $isUserQuery ? 'studentProfile' : 'user.studentProfile';
            $query->whereHas($relation, function ($q) use ($request) {
                if ($request->filled('course_id')) {
                    $q->where('course_id', $request->course_id);
                }
                if ($request->filled('batch_id')) {
                    $q->where('batch_id', $request->batch_id);
                }
                if ($request->filled('semester_id')) {
                    $q->where('semester_id', $request->semester_id);
                }
                if ($request->filled('section_id')) {
                    $q->where('section_id', $request->section_id);
                }
                if ($request->filled('session_id')) {
                    $q->where('session_id', $request->session_id);
                }
                // Subject is linked to Semester, but we can filter students who have this subject 
                // In this system, any student in the semester has all subjects of that semester.
                // So filtering by subject_id is essentially filtering by its semester_id.
            });
        }
    }

    protected function getStaffPresentDays(Collection $userIds, Carbon $start, Carbon $end, bool $includeLate = true): Collection
    {
        $statuses = $includeLate ? ['present', 'late'] : ['present'];

        return $this->getStaffDaysByStatus($userIds, $start, $end, $statuses);
    }

    protected function getStaffDaysByStatus(Collection $userIds, Carbon $start, Carbon $end, array $statuses): Collection
    {
        return Attendance::query()
            ->whereIn('user_id', $userIds)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->whereIn('status', $statuses)
            ->selectRaw('user_id, COUNT(DISTINCT date) as total_days')
            ->groupBy('user_id')
            ->pluck('total_days', 'user_id');
    }

    protected function getStudentPresentDays(Collection $userIds, Carbon $start, Carbon $end, Request $request): Collection
    {
        $query = StudentAttendance::query()
            ->whereIn('student_id', $userIds)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()]);

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }

        return $query->selectRaw('student_id, date, MAX(CASE WHEN LOWER(status) = "present" THEN 1 ELSE 0 END) as has_present')
            ->groupBy('student_id', 'date')
            ->get()
            ->groupBy('student_id')
            ->map(function (Collection $rows) {
                return $rows->where('has_present', 1)->count();
            });
    }

    protected function getApprovedLeaveDays(Collection $userIds, Carbon $start, Carbon $end): Collection
    {
        return LeaveRequest::query()
            ->whereIn('user_id', $userIds)
            ->where('status', 'approved')
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('from_date', [$start->toDateString(), $end->toDateString()])
                    ->orWhereBetween('to_date', [$start->toDateString(), $end->toDateString()])
                    ->orWhere(function ($q) use ($start, $end) {
                        $q->where('from_date', '<=', $start->toDateString())
                            ->where('to_date', '>=', $end->toDateString());
                    });
            })
            ->get()
            ->groupBy('user_id')
            ->map(function (Collection $leaves) use ($start, $end) {
                $days = 0;
                foreach ($leaves as $leave) {
                    $from = Carbon::parse($leave->from_date)->max($start)->startOfDay();
                    $to = Carbon::parse($leave->to_date)->min($end)->startOfDay();
                    if ($from->lte($to)) {
                        $days += CarbonPeriod::create($from, $to)->count();
                    }
                }
                return $days;
            });
    }

    /**
     * Approved leave requests for one student. Pending and rejected leaves are ignored.
     */
    public function approvedLeavesForStudent(int $studentId): Collection
    {
        return LeaveRequest::query()
            ->where('user_id', $studentId)
            ->where('status', 'approved')
            ->get();
    }

    /**
     * Approved leaves for many students, grouped by user id.
     */
    public function approvedLeavesForStudents(Collection $studentIds): Collection
    {
        $ids = $studentIds->filter()->unique()->values();

        if ($ids->isEmpty()) {
            return collect();
        }

        return LeaveRequest::query()
            ->whereIn('user_id', $ids)
            ->where('status', 'approved')
            ->get()
            ->groupBy('user_id');
    }

    /**
     * Present stays present. Absent or no class mark on an approved leave date is leave.
     */
    public function classDayStatus(?string $markedStatus, mixed $date, Collection $approvedLeaves): string
    {
        $status = strtolower((string) $markedStatus);

        if ($status === 'present') {
            return 'present';
        }

        if ($this->dateIsApprovedLeave($date, $approvedLeaves)) {
            return 'leave';
        }

        return $status !== '' ? $status : 'absent';
    }

    public function dateIsApprovedLeave(mixed $date, Collection $approvedLeaves): bool
    {
        if ($approvedLeaves->isEmpty() || $date === null || $date === '') {
            return false;
        }

        $day = Carbon::parse($date)->toDateString();

        return $approvedLeaves->contains(function ($leave) use ($day) {
            $from = Carbon::parse($leave->from_date)->toDateString();
            $to = Carbon::parse($leave->to_date)->toDateString();

            return $day >= $from && $day <= $to;
        });
    }

    /**
     * Present stays present. Absent on an approved leave date counts as leave.
     * Percentage is present / (present + absent). Leave is outside that total.
     *
     * @return array{present: int, absent: int, leave: int, total: int, percentage: float|int}
     */
    public function studentAttendanceCounts(Collection $records, Collection $approvedLeaves, int $precision = 1): array
    {
        $present = 0;
        $absent = 0;
        $leave = 0;

        foreach ($records as $record) {
            $status = strtolower((string) $record->status);

            if ($status === 'present') {
                $present++;
                continue;
            }

            if ($status === 'absent' && $this->dateIsApprovedLeave($record->date, $approvedLeaves)) {
                $leave++;
                continue;
            }

            if ($status === 'absent') {
                $absent++;
            }
        }

        $total = $present + $absent;

        return [
            'present' => $present,
            'absent' => $absent,
            'leave' => $leave,
            'total' => $total,
            'percentage' => $total > 0 ? round(($present / $total) * 100, $precision) : 0,
        ];
    }

    public function studentRecordDisplayStatus(object $record, Collection $approvedLeaves): string
    {
        $status = strtolower((string) $record->status);

        if ($status === 'absent' && $this->dateIsApprovedLeave($record->date, $approvedLeaves)) {
            return 'leave';
        }

        return (string) $record->status;
    }

    /**
     * Present or a stored late punch stays present, including when check-out is missing.
     * An absent punch on an approved leave date is leave.
     */
    public function staffPunchDisplayStatus(?string $markedStatus, mixed $date, Collection $approvedLeaves): string
    {
        $status = strtolower((string) $markedStatus);

        if (in_array($status, ['present', 'late'], true)) {
            return 'present';
        }

        if ($status === 'absent' && $this->dateIsApprovedLeave($date, $approvedLeaves)) {
            return 'leave';
        }

        return $status !== '' ? $status : 'absent';
    }

    /**
     * Present punch wins, even without check-out and even on leave, Sunday, or a holiday.
     * Sunday and holiday stay when there is no present punch. Approved leave otherwise
     * replaces an absent or missing punch. Past weekdays with neither are absent.
     */
    public function staffCalendarDayStatus(?string $markedStatus, mixed $date, Collection $approvedLeaves, Collection $holidays): string
    {
        $day = Carbon::parse($date)->startOfDay();
        $dateStr = $day->toDateString();
        $status = strtolower((string) $markedStatus);

        if (in_array($status, ['present', 'late'], true)) {
            return 'present';
        }

        if ($holidays->has($dateStr)) {
            return 'holiday';
        }

        if ($day->isSunday()) {
            return 'sunday';
        }

        if ($this->dateIsApprovedLeave($dateStr, $approvedLeaves)) {
            return 'leave';
        }

        if ($status === 'absent' || $day->lt(Carbon::today())) {
            return 'absent';
        }

        return '';
    }

    /**
     * @return array{cells: array<int, array{status: string, detail: string}>, present: int, absent: int, leave: int, total: int, percentage: float|int}
     */
    public function staffMonthReport(Collection $punchesByDate, Collection $approvedLeaves, Collection $holidays, Carbon $monthStart, int $precision = 1): array
    {
        $punches = $punchesByDate->keyBy(fn ($row) => Carbon::parse($row->date)->toDateString());
        $present = 0;
        $absent = 0;
        $leave = 0;
        $cells = [];

        for ($day = 1; $day <= $monthStart->daysInMonth; $day++) {
            $date = $monthStart->copy()->day($day)->startOfDay();
            $dateStr = $date->toDateString();
            $punch = $punches->get($dateStr);
            $status = $this->staffCalendarDayStatus($punch->status ?? null, $dateStr, $approvedLeaves, $holidays);
            $detail = '';

            if ($status === 'holiday') {
                $holiday = $holidays->get($dateStr);
                $detail = is_object($holiday) ? (string) ($holiday->name ?? '') : '';
            } elseif ($status === 'present' && $punch) {
                $checkIn = $punch->check_in ? Carbon::parse($punch->check_in)->format('h:i A') : '';
                $checkOut = $punch->check_out ? Carbon::parse($punch->check_out)->format('h:i A') : '';
                if ($checkIn || $checkOut) {
                    $detail = 'In: '.($checkIn ?: '-').' | Out: '.($checkOut ?: '-');
                }
            }

            if ($status === 'present') {
                $present++;
            } elseif ($status === 'absent') {
                $absent++;
            } elseif ($status === 'leave') {
                $leave++;
            }

            $cells[$day] = [
                'status' => $status,
                'detail' => $detail,
            ];
        }

        $total = $present + $absent;

        return [
            'cells' => $cells,
            'present' => $present,
            'absent' => $absent,
            'leave' => $leave,
            'total' => $total,
            'percentage' => $total > 0 ? round(($present / $total) * 100, $precision) : 0,
        ];
    }

    /**
     * Punch totals plus approved leave dates that are not Sunday, holiday, or a present punch.
     *
     * @return array{present: int, absent: int, leave: int, total: int, percentage: float|int}
     */
    public function staffPortalSummary(int $userId, int $precision = 1): array
    {
        $punches = Attendance::query()->where('user_id', $userId)->get();
        $leaves = $this->approvedLeavesForStudent($userId);
        $punchDates = $punches->keyBy(fn ($row) => Carbon::parse($row->date)->toDateString());

        $present = 0;
        $absent = 0;
        foreach ($punches as $punch) {
            $display = $this->staffPunchDisplayStatus($punch->status, $punch->date, $leaves);
            if ($display === 'present') {
                $present++;
            } elseif ($display === 'absent') {
                $absent++;
            }
        }

        $leaveDates = [];
        if ($leaves->isNotEmpty()) {
            $from = Carbon::parse($leaves->min(fn ($leave) => Carbon::parse($leave->from_date)->toDateString()))->startOfDay();
            $to = Carbon::parse($leaves->max(fn ($leave) => Carbon::parse($leave->to_date)->toDateString()))->startOfDay();
            $holidays = $this->holidayDatesBetween($from, $to);

            foreach ($leaves as $leave) {
                $start = Carbon::parse($leave->from_date)->startOfDay();
                $end = Carbon::parse($leave->to_date)->startOfDay();
                foreach (CarbonPeriod::create($start, $end) as $day) {
                    $dateStr = $day->toDateString();
                    if ($day->isSunday() || $holidays->has($dateStr)) {
                        continue;
                    }

                    $punch = $punchDates->get($dateStr);
                    $marked = strtolower((string) ($punch->status ?? ''));
                    if (in_array($marked, ['present', 'late'], true)) {
                        continue;
                    }

                    $leaveDates[$dateStr] = true;
                }
            }
        }

        $total = $present + $absent;

        return [
            'present' => $present,
            'absent' => $absent,
            'leave' => count($leaveDates),
            'total' => $total,
            'percentage' => $total > 0 ? round(($present / $total) * 100, $precision) : 0,
        ];
    }

    protected function staffPunchesByUser(Collection $userIds, Carbon $start, Carbon $end): Collection
    {
        if ($userIds->isEmpty()) {
            return collect();
        }

        return Attendance::query()
            ->whereIn('user_id', $userIds)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->groupBy('user_id');
    }

    protected function holidaysForMonth(Carbon $monthStart): Collection
    {
        return Holiday::query()
            ->whereYear('date', $monthStart->year)
            ->whereMonth('date', $monthStart->month)
            ->get()
            ->keyBy(fn ($holiday) => $holiday->date->format('Y-m-d'));
    }

    protected function holidayDatesBetween(Carbon $start, Carbon $end): Collection
    {
        return Holiday::query()
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->pluck('date')
            ->mapWithKeys(fn ($date) => [Carbon::parse($date)->toDateString() => true]);
    }

    protected function getPendingLeaveCounts(Collection $userIds, Carbon $start, Carbon $end): Collection
    {
        return LeaveRequest::query()
            ->whereIn('user_id', $userIds)
            ->where('status', 'pending')
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('from_date', [$start->toDateString(), $end->toDateString()])
                    ->orWhereBetween('to_date', [$start->toDateString(), $end->toDateString()])
                    ->orWhere(function ($q) use ($start, $end) {
                        $q->where('from_date', '<=', $start->toDateString())
                            ->where('to_date', '>=', $end->toDateString());
                    });
            })
            ->selectRaw('user_id, COUNT(*) as pending_count')
            ->groupBy('user_id')
            ->pluck('pending_count', 'user_id');
    }
}
