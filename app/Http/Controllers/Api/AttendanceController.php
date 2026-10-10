<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AcademicSession;
use App\Models\Location;
use App\Models\Section;
use App\Models\Semester;
use App\Models\StudentAttendance;
use App\Models\Subject;
use App\Models\User;
use App\Models\StudentProfile;
use App\Services\AttendanceService;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AttendanceController extends Controller
{
    public function __construct(protected AttendanceService $attendanceService)
    {
    }

    /**
     * Mark Staff/Faculty Attendance (Self)
     */

    public function getAttendanceRecords(Request $request)
    {
        $user = $request->user();
        $records = Attendance::where('user_id', $user->id)
            ->orderBy('date', 'desc')
            ->get();
        $leaves = $this->attendanceService->approvedLeavesForStudent($user->id);
        $summary = $this->attendanceService->staffPortalSummary($user->id);

        $data = $records->map(function ($record) use ($leaves) {
            $row = $record->toArray();
            $row['status'] = $this->attendanceService->staffPunchDisplayStatus(
                $record->status,
                $record->date,
                $leaves
            );

            return $row;
        })->values();

        return response()->json([
            'status' => 'success',
            'summary' => [
                'present' => $summary['present'],
                'absent' => $summary['absent'],
                'leave' => $summary['leave'],
                'percentage' => $summary['percentage'],
            ],
            'data' => $data,
        ]);
    }
    
    public function markStaffAttendance(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $user = $request->user();
        $today = Carbon::today();
        $now = Carbon::now();

        // Check if already checked in today
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        // Geofencing Check
        $locations = Location::where('is_active', true)->get();
        $isWithinRange = false;
        $nearestLocation = null;

        foreach ($locations as $location) {
            $distance = $this->calculateDistance(
                $request->latitude,
                $request->longitude,
                $location->latitude,
                $location->longitude
            );

            if ($distance <= $location->radius) {
                $isWithinRange = true;
                $nearestLocation = $location;
                break;
            }
        }

        if (!$isWithinRange) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are outside the allowed attendance area.'
            ], 403);
        }

        if (!$attendance) {
            // Check-in
            $attendance = Attendance::create([
                'user_id' => $user->id,
                'location_id' => $nearestLocation->id,
                'date' => $today,
                'check_in' => $now->toTimeString(),
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'status' => 'present'
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Check-in successful at ' . $nearestLocation->name,
                'data' => $attendance
            ]);
        } else {
            // Check-out
            if ($attendance->check_out) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You have already checked out for today.'
                ], 400);
            }

            $attendance->update([
                'check_out' => $now->toTimeString()
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Check-out successful.',
                'data' => $attendance
            ]);
        }
    }

    /**
     * Get Student List for Attendance
     */
    public function getStudentsForAttendance(Request $request)
    {
        $request->validate([
            'batch_id' => 'nullable|exists:batches,id',
            'session_id' => 'required|exists:academic_sessions,id',
            'course_id' => 'required|exists:courses,id',
            'semester_id' => 'required|exists:semesters,id',
            'section_id' => 'required|exists:sections,id',
        ]);

        $batchId = $request->input('batch_id');
        $sessionBatchId = AcademicSession::where('id', $request->session_id)->value('batch_id');

        if ($batchId) {
            if ((int) $sessionBatchId !== (int) $batchId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Selected session does not belong to the selected batch.',
                ], 422);
            }
        } else {
            $batchId = $sessionBatchId;
        }

        if ($batchId) {
            $batchBelongsToCourse = \App\Models\Batch::where('id', $batchId)
                ->where('course_id', $request->course_id)
                ->exists();

            if (! $batchBelongsToCourse) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Selected batch does not belong to the selected course.',
                ], 422);
            }
        }

        $semesterBelongsToCourse = Semester::where('id', $request->semester_id)
            ->where('course_id', $request->course_id)
            ->exists();

        if (!$semesterBelongsToCourse) {
            return response()->json([
                'status' => 'error',
                'message' => 'Selected semester does not belong to the selected course.'
            ], 422);
        }

        $sectionBelongsToSemester = Section::where('id', $request->section_id)
            ->where('semester_id', $request->semester_id)
            ->exists();

        if (!$sectionBelongsToSemester) {
            return response()->json([
                'status' => 'error',
                'message' => 'Selected section does not belong to the selected semester.'
            ], 422);
        }

        $students = User::where('role', 'student')
            ->whereHas('studentProfile', function ($query) use ($request, $batchId) {
                $query->where('session_id', $request->session_id)
                    ->where('course_id', $request->course_id)
                    ->where('semester_id', $request->semester_id)
                    ->where('section_id', $request->section_id);

                if ($batchId) {
                    $query->where('batch_id', $batchId);
                }
            })
            ->with('studentProfile.batch')
            ->get()
            ->map(function (User $user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'roll_no' => $user->studentProfile->roll_no,
                    'batch_id' => $user->studentProfile->batch_id,
                    'batch_name' => $user->studentProfile->batch->name ?? null,
                    'profile_photo' => $user->getProfilePhotoUrl()
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $students
        ]);
    }

    /**
     * Mark Student Attendance (By Faculty/Staff)
     */
    public function markStudentAttendance(Request $request)
    {
        $request->validate([
            'batch_id' => 'nullable|exists:batches,id',
            'session_id' => 'required|exists:academic_sessions,id',
            'course_id' => 'required|exists:courses,id',
            'semester_id' => 'required|exists:semesters,id',
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'date' => 'required|date',
            'attendance' => 'required|array',
            'attendance.*' => 'required|in:present,absent',
        ]);

        $studentIds = array_keys($request->attendance);

        $semesterBelongsToCourse = Semester::where('id', $request->semester_id)
            ->where('course_id', $request->course_id)
            ->exists();

        if (!$semesterBelongsToCourse) {
            return response()->json([
                'status' => 'error',
                'message' => 'Selected semester does not belong to the selected course.'
            ], 422);
        }

        $sectionBelongsToSemester = Section::where('id', $request->section_id)
            ->where('semester_id', $request->semester_id)
            ->exists();

        if (!$sectionBelongsToSemester) {
            return response()->json([
                'status' => 'error',
                'message' => 'Selected section does not belong to the selected semester.'
            ], 422);
        }

        $subjectBelongsToSemester = Subject::where('id', $request->subject_id)
            ->where('semester_id', $request->semester_id)
            ->exists();

        if (!$subjectBelongsToSemester) {
            return response()->json([
                'status' => 'error',
                'message' => 'Selected subject does not belong to the selected semester.'
            ], 422);
        }

        $batchId = $request->input('batch_id');
        if (! $batchId) {
            $batchId = AcademicSession::where('id', $request->session_id)->value('batch_id');
        } elseif ((int) AcademicSession::where('id', $request->session_id)->value('batch_id') !== (int) $batchId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Selected batch does not match the selected session.',
            ], 422);
        }

        // Validate that all student IDs belong to the same session/course/semester/section.
        $validStudentsCount = User::whereIn('id', $studentIds)
            ->where('role', 'student')
            ->whereHas('studentProfile', function ($query) use ($request, $batchId) {
                $query->where('session_id', $request->session_id)
                    ->where('course_id', $request->course_id)
                    ->where('semester_id', $request->semester_id)
                    ->where('section_id', $request->section_id);

                if ($batchId) {
                    $query->where('batch_id', $batchId);
                }
            })
            ->count();

        if ($validStudentsCount !== count($studentIds)) {
            return response()->json([
                'status' => 'error',
                'message' => 'One or more students do not belong to the selected academic section.'
            ], 422);
        }

        $markedBy = $request->user()->id;
        $date = Carbon::parse($request->date)->format('Y-m-d');
        $createdCount = 0;
        $updatedCount = 0;

        DB::beginTransaction();
        try {
            AuditLogger::withoutLogging(function () use ($request, $date, $batchId, $markedBy, &$createdCount, &$updatedCount) {
                foreach ($request->attendance as $studentId => $status) {
                    $record = StudentAttendance::updateOrCreate(
                        [
                            'student_id' => $studentId,
                            'subject_id' => $request->subject_id,
                            'section_id' => $request->section_id,
                            'date' => $date,
                        ],
                        [
                            'batch_id' => $batchId,
                            'session_id' => $request->session_id,
                            'course_id' => $request->course_id,
                            'semester_id' => $request->semester_id,
                            'section_id' => $request->section_id,
                            'status' => $status,
                            'marked_by' => $markedBy
                        ]
                    );

                    if ($record->wasRecentlyCreated) {
                        $createdCount++;
                    } else {
                        $updatedCount++;
                    }
                }
            });
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to mark attendance: ' . $e->getMessage()
            ], 500);
        }

        $this->logClassAttendance($request, $batchId, $date, $markedBy, $createdCount, $updatedCount);

        return response()->json([
            'status' => 'success',
            'message' => 'Student attendance marked successfully.'
        ]);
    }

    /**
     * One activity log for the whole class, not one row per student.
     */
    private function logClassAttendance(Request $request, $batchId, string $date, int $markedBy, int $createdCount, int $updatedCount): void
    {
        $total = count($request->attendance);
        if ($total === 0) {
            return;
        }

        $sectionName = Section::whereKey($request->section_id)->value('name');
        $subjectName = Subject::whereKey($request->subject_id)->value('name');
        $students = User::whereIn('id', array_keys($request->attendance))
            ->with('studentProfile:id,user_id,roll_no')
            ->get(['id', 'name'])
            ->keyBy('id');

        $studentList = collect($request->attendance)->map(function ($status, $studentId) use ($students) {
            $student = $students->get((int) $studentId);

            return [
                'student_id' => (int) $studentId,
                'name' => $student->name ?? null,
                'roll_no' => $student->studentProfile->roll_no ?? null,
                'status' => $status,
            ];
        })->values();

        $presentCount = $studentList->where('status', 'present')->count();

        AuditLogger::log(
            $createdCount > 0 ? 'created' : 'updated',
            null,
            null,
            [
                'batch_id' => $batchId ? (int) $batchId : null,
                'session_id' => (int) $request->session_id,
                'course_id' => (int) $request->course_id,
                'semester_id' => (int) $request->semester_id,
                'section_id' => (int) $request->section_id,
                'section_name' => $sectionName,
                'subject_id' => (int) $request->subject_id,
                'subject_name' => $subjectName,
                'date' => $date,
                'marked_by' => $markedBy,
                'total_students' => $total,
                'present_count' => $presentCount,
                'absent_count' => $total - $presentCount,
                'created_count' => $createdCount,
                'updated_count' => $updatedCount,
                'students' => $studentList->all(),
            ],
            sprintf(
                'Student attendance marked for %s · %s on %s',
                $sectionName ?: 'section',
                $subjectName ?: 'subject',
                $date
            )
        );
    }

    /**
     * Calculate distance between two points in meters (Haversine formula)
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // in meters

        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Get Student Attendance List (Self)
     */
    public function getStudentAttendanceList(Request $request)
    {
        $user = $request->user();

        $query = StudentAttendance::with('subject')->where('student_id', $user->id);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('subject', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $period = null;
        if ($request->filled('filter') || $request->filled('month') || $request->filled('semester_id')) {
            $defaultFilter = $request->filled('semester_id') && ! $request->filled('month') ? 'semester' : 'month';
            $period = $this->studentAttendancePeriod($request, $defaultFilter);
            $this->applyStudentAttendancePeriod($query, $period);
        }

        $records = $query->orderBy('date', 'desc')->get();
        $approvedLeaves = $this->attendanceService->approvedLeavesForStudent($user->id);

        $grouped = $records->groupBy('subject_id')->map(function ($subjectRecords) use ($approvedLeaves) {
            $subject = $subjectRecords->first()->subject;
            $counts = $this->attendanceService->studentAttendanceCounts($subjectRecords, $approvedLeaves, 2);

            return [
                'subject_id' => $subject->id ?? null,
                'subject_name' => $subject->name ?? 'Unknown',
                'total_classes' => $counts['total'],
                'attended_classes' => $counts['present'],
                'leave_classes' => $counts['leave'],
                'percentage' => $counts['percentage'],
                'records' => $subjectRecords->map(function ($record) use ($approvedLeaves) {
                    return [
                        'id' => $record->id,
                        'date' => $record->date,
                        'status' => $this->attendanceService->studentRecordDisplayStatus($record, $approvedLeaves),
                    ];
                })->values()
            ];
        })->values();

        $payload = [
            'status' => 'success',
            'data' => $grouped,
        ];

        if ($period) {
            $payload['filter'] = $period['filter'];
            $payload['month'] = $period['month'];
            $payload['semester_id'] = $period['semester_id'];
            $payload['semester_name'] = $period['semester_name'];
        }

        return response()->json($payload);
    }

    /**
     * Overall attendance % for the logged-in student.
     * filter=month (default) or filter=semester. Same present / total formula as the admin monthly report.
     */
    public function getStudentOverallAttendance(Request $request)
    {
        $defaultFilter = $request->filled('semester_id') && ! $request->filled('month') ? 'semester' : 'month';
        $period = $this->studentAttendancePeriod($request, $defaultFilter);

        $query = StudentAttendance::where('student_id', $request->user()->id);
        $this->applyStudentAttendancePeriod($query, $period);
        $records = $query->get();
        $counts = $this->attendanceService->studentAttendanceCounts(
            $records,
            $this->attendanceService->approvedLeavesForStudent($request->user()->id)
        );

        return response()->json([
            'status' => 'success',
            'data' => [
                'filter' => $period['filter'],
                'month' => $period['month'],
                'semester_id' => $period['semester_id'],
                'semester_name' => $period['semester_name'],
                'total_classes' => $counts['total'],
                'attended' => $counts['present'],
                'absent' => $counts['absent'],
                'leave' => $counts['leave'],
                'percentage' => $counts['percentage'],
            ],
        ]);
    }

    /**
     * @return array{filter: string, month: ?string, semester_id: ?int, semester_name: ?string}
     */
    private function studentAttendancePeriod(Request $request, string $defaultFilter): array
    {
        $validated = $request->validate([
            'filter' => ['nullable', 'in:month,semester'],
            'month' => ['nullable', 'date_format:Y-m'],
            'semester_id' => ['nullable', 'integer', 'exists:semesters,id'],
        ]);

        $filter = $validated['filter'] ?? $defaultFilter;

        if ($filter === 'semester') {
            $semesterId = isset($validated['semester_id'])
                ? (int) $validated['semester_id']
                : $request->user()->studentProfile?->semester_id;

            if (! $semesterId) {
                throw ValidationException::withMessages([
                    'semester_id' => ['Semester is not set on your profile.'],
                ]);
            }

            $semester = Semester::query()->find($semesterId);

            return [
                'filter' => 'semester',
                'month' => null,
                'semester_id' => (int) $semesterId,
                'semester_name' => $semester?->name,
            ];
        }

        return [
            'filter' => 'month',
            'month' => $validated['month'] ?? Carbon::now()->format('Y-m'),
            'semester_id' => null,
            'semester_name' => null,
        ];
    }

    private function applyStudentAttendancePeriod($query, array $period): void
    {
        if ($period['filter'] === 'semester') {
            $query->where('semester_id', $period['semester_id']);

            return;
        }

        $date = Carbon::createFromFormat('Y-m-d', $period['month'].'-01');
        $query->whereYear('date', $date->year)->whereMonth('date', $date->month);
    }

    /**
     * Get Faculty Attendance Summary
     */
    public function getFacultyAttendanceSummary(Request $request)
    {
        $query = StudentAttendance::query();

        if ($request->filled('date')) {
            $query->where('date', $request->date);
        }

        if ($request->filled('marked_by')) {
            $query->where('marked_by', $request->marked_by);
        }

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        if ($request->filled('semester_id')) {
            $query->where('semester_id', $request->semester_id);
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }

        if ($request->filled('session_id')) {
            $query->where('session_id', $request->session_id);
        }

        $summary = (clone $query)
            ->select(
                'marked_by',
                'session_id',
                'course_id',
                'semester_id',
                'section_id',
                'subject_id',
                'date',
                DB::raw('COUNT(*) as total_students'),
                DB::raw('SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present_count'),
                DB::raw('SUM(CASE WHEN status = "absent" THEN 1 ELSE 0 END) as absent_count')
            )
            ->with([
                'marker:id,name',
                'subject:id,name',
                'course:id,name',
                'semester:id,name',
                'section:id,name',
                'session:id,name'
            ])
            ->groupBy('marked_by', 'session_id', 'course_id', 'semester_id', 'section_id', 'subject_id', 'date')
            ->orderBy('date', 'desc')
            ->get();

        $classMarkQuery = StudentAttendance::query();
        foreach (['date', 'course_id', 'semester_id', 'subject_id', 'section_id', 'session_id'] as $filter) {
            if ($request->filled($filter)) {
                $classMarkQuery->where($filter, $request->input($filter));
            }
        }

        $marks = $summary->isEmpty()
            ? collect()
            : (clone $query)->get([
                'student_id',
                'status',
                'date',
                'marked_by',
                'session_id',
                'course_id',
                'semester_id',
                'section_id',
                'subject_id',
            ]);

        $classMarks = $summary->isEmpty()
            ? collect()
            : $classMarkQuery->get([
                'student_id',
                'date',
                'session_id',
                'course_id',
                'semester_id',
                'section_id',
                'subject_id',
            ]);

        $profiles = $summary->isEmpty()
            ? collect()
            : StudentProfile::query()
                ->where(function ($outer) use ($summary) {
                    foreach ($summary as $row) {
                        $outer->orWhere(function ($inner) use ($row) {
                            $inner->where('session_id', $row->session_id)
                                ->where('course_id', $row->course_id)
                                ->where('semester_id', $row->semester_id)
                                ->where('section_id', $row->section_id);
                        });
                    }
                })
                ->get(['user_id', 'session_id', 'course_id', 'semester_id', 'section_id']);

        $approvedLeaves = $this->attendanceService->approvedLeavesForStudents(
            $marks->pluck('student_id')->merge($classMarks->pluck('student_id'))->merge($profiles->pluck('user_id'))
        );

        $marksByGroup = $marks->groupBy(function ($mark) {
            return implode('|', [
                $mark->marked_by,
                $mark->session_id,
                $mark->course_id,
                $mark->semester_id,
                $mark->section_id,
                $mark->subject_id,
                Carbon::parse($mark->date)->toDateString(),
            ]);
        });

        $markedIdsByClassDate = $classMarks->groupBy(function ($mark) {
            return implode('|', [
                $mark->session_id,
                $mark->course_id,
                $mark->semester_id,
                $mark->section_id,
                $mark->subject_id,
                Carbon::parse($mark->date)->toDateString(),
            ]);
        })->map(fn ($rows) => $rows->pluck('student_id'));

        $profilesByClass = $profiles->groupBy(function ($profile) {
            return implode('|', [
                $profile->session_id,
                $profile->course_id,
                $profile->semester_id,
                $profile->section_id,
            ]);
        });

        $summary = $summary->map(function ($record) use ($marksByGroup, $markedIdsByClassDate, $profilesByClass, $approvedLeaves) {
            $date = Carbon::parse($record->date)->toDateString();
            $groupKey = implode('|', [
                $record->marked_by,
                $record->session_id,
                $record->course_id,
                $record->semester_id,
                $record->section_id,
                $record->subject_id,
                $date,
            ]);
            $classDateKey = implode('|', [
                $record->session_id,
                $record->course_id,
                $record->semester_id,
                $record->section_id,
                $record->subject_id,
                $date,
            ]);
            $classKey = implode('|', [
                $record->session_id,
                $record->course_id,
                $record->semester_id,
                $record->section_id,
            ]);

            $absentOnLeave = $marksByGroup->get($groupKey, collect())
                ->filter(function ($mark) use ($approvedLeaves, $date) {
                    return strtolower((string) $mark->status) === 'absent'
                        && $this->attendanceService->dateIsApprovedLeave(
                            $date,
                            $approvedLeaves->get($mark->student_id, collect())
                        );
                })
                ->count();

            $alreadyMarked = $markedIdsByClassDate->get($classDateKey, collect());
            $unmarkedOnLeave = $profilesByClass->get($classKey, collect())
                ->reject(fn ($profile) => $alreadyMarked->contains($profile->user_id))
                ->filter(function ($profile) use ($approvedLeaves, $date) {
                    return $this->attendanceService->dateIsApprovedLeave(
                        $date,
                        $approvedLeaves->get($profile->user_id, collect())
                    );
                })
                ->count();

            return [
                'marked_by' => $record->marked_by,
                'session_id' => $record->session_id,
                'course_id' => $record->course_id,
                'semester_id' => $record->semester_id,
                'section_id' => $record->section_id,
                'subject_id' => $record->subject_id,
                'teacher_name' => $record->marker->name ?? 'Unknown',
                'session_name' => $record->session->name ?? 'N/A',
                'course_name' => $record->course->name ?? 'N/A',
                'semester_name' => $record->semester->name ?? 'N/A',
                'section_name' => $record->section->name ?? 'N/A',
                'subject_name' => $record->subject->name ?? 'N/A',
                'date' => $record->date,
                'total_students' => $record->total_students,
                'present_count' => (int) $record->present_count,
                'absent_count' => max((int) $record->absent_count - $absentOnLeave, 0),
                'leave_count' => $absentOnLeave + $unmarkedOnLeave,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $summary
        ]);
    }

    /**
     * Get Student List with Attendance Status for a Specific Summary Entry
     */
    public function getFacultyAttendanceStudents(Request $request)
    {
        $request->validate([
            'session_id' => 'required|exists:academic_sessions,id',
            'course_id' => 'required|exists:courses,id',
            'semester_id' => 'required|exists:semesters,id',
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'date' => 'required|date',
            'marked_by' => 'nullable|exists:users,id',
        ]);

        // Get all students enrolled in this class
        $enrolledStudents = User::where('role', 'student')
            ->whereHas('studentProfile', function ($q) use ($request) {
                $q->where('session_id', $request->session_id)
                    ->where('course_id', $request->course_id)
                    ->where('semester_id', $request->semester_id)
                    ->where('section_id', $request->section_id);
            })
            ->with(['studentProfile'])
            ->get();

        // Get all attendance records for this date/subject/session
        $attendanceQuery = StudentAttendance::with(['student.studentProfile'])
            ->where('session_id', $request->session_id)
            ->where('course_id', $request->course_id)
            ->where('semester_id', $request->semester_id)
            ->where('section_id', $request->section_id)
            ->where('subject_id', $request->subject_id)
            ->where('date', $request->date);

        if ($request->filled('marked_by')) {
            $attendanceQuery->where('marked_by', $request->marked_by);
        }

        $attendanceRecords = $attendanceQuery->get();
        $attendanceMap = $attendanceRecords->pluck('status', 'student_id');

        // Merge enrolled students and any additional users who have an attendance record
        $allUsers = $enrolledStudents->keyBy('id');
        foreach ($attendanceRecords as $record) {
            if ($record->student && !$allUsers->has($record->student_id)) {
                $allUsers->put($record->student_id, $record->student);
            }
        }

        $approvedLeaves = $this->attendanceService->approvedLeavesForStudents($allUsers->keys());

        $records = $allUsers->values()->map(function ($user) use ($attendanceMap, $approvedLeaves, $request) {
            $leaves = $approvedLeaves->get($user->id, collect());

            return [
                'id' => $user->id,
                'name' => $user->name,
                'roll_no' => $user->studentProfile->roll_no ?? 'N/A',
                'profile_photo' => $user->getProfilePhotoUrl(),
                'status' => $this->attendanceService->classDayStatus(
                    $attendanceMap->get($user->id),
                    $request->date,
                    $leaves
                ),
            ];
        })->sortBy('roll_no', SORT_NATURAL)->values();

        return response()->json([
            'status' => 'success',
            'data' => $records
        ]);
    }

    /**
     * Single student attendance performance (subject-wise + overall).
     */
    public function getSingleStudentPerformance(Request $request, int $student_id)
    {
        
        $request->validate([
            'semester_id' => 'required|exists:semesters,id',
            'session_id' => 'nullable|exists:academic_sessions,id',
            'course_id' => 'nullable|exists:courses,id',
            'section_id' => 'nullable|exists:sections,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'month' => 'nullable|integer|between:1,12',
            'year' => 'nullable|integer|between:2000,2100',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
        ]);

        $student = User::where('id', $student_id)
            ->where('role', 'student')
            ->with([
                'studentProfile.session',
                'studentProfile.course',
                'studentProfile.semester',
                'studentProfile.section',
                'studentProfiles' => function ($query) use ($request) {
                    $query->where('semester_id', $request->semester_id)
                        ->with(['session', 'course', 'semester', 'section'])
                        ->latest();
                },
            ])
            ->first();

        if (! $student) {
            return response()->json([
                'status' => 'error',
                'message' => 'Student not found.',
            ], 404);
        }

        if ($request->filled('course_id')) {
            $semesterBelongsToCourse = Semester::where('id', $request->semester_id)
                ->where('course_id', $request->course_id)
                ->exists();

            if (! $semesterBelongsToCourse) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Selected semester does not belong to the selected course.',
                ], 422);
            }
        }

        if ($request->filled('section_id')) {
            $sectionBelongsToSemester = Section::where('id', $request->section_id)
                ->where('semester_id', $request->semester_id)
                ->exists();

            if (! $sectionBelongsToSemester) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Selected section does not belong to the selected semester.',
                ], 422);
            }
        }

        if ($request->filled('subject_id')) {
            $subjectBelongsToSemester = Subject::where('id', $request->subject_id)
                ->where('semester_id', $request->semester_id)
                ->exists();

            if (! $subjectBelongsToSemester) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Selected subject does not belong to the selected semester.',
                ], 422);
            }
        }

        $profileFilterQuery = StudentProfile::where('user_id', $student->id)
            ->where('semester_id', $request->semester_id);

        if ($request->filled('session_id')) {
            $profileFilterQuery->where('session_id', $request->session_id);
        }

        if ($request->filled('course_id')) {
            $profileFilterQuery->where('course_id', $request->course_id);
        }

        if ($request->filled('section_id')) {
            $profileFilterQuery->where('section_id', $request->section_id);
        }

        $hasMatchingProfile = (clone $profileFilterQuery)->exists();
        $hasAttendanceInSemester = StudentAttendance::where('student_id', $student->id)
            ->where('semester_id', $request->semester_id)
            ->when($request->filled('session_id'), fn ($q) => $q->where('session_id', $request->session_id))
            ->when($request->filled('course_id'), fn ($q) => $q->where('course_id', $request->course_id))
            ->when($request->filled('section_id'), fn ($q) => $q->where('section_id', $request->section_id))
            ->exists();

        // Allow past-semester performance after promotion (profile semester may have changed).
        if (! $hasMatchingProfile && ! $hasAttendanceInSemester) {
            return response()->json([
                'status' => 'error',
                'message' => 'Student does not belong to the selected academic filters.',
            ], 422);
        }

        $attendanceQuery = StudentAttendance::with('subject:id,name,code')
            ->where('student_id', $student->id)
            ->where('semester_id', $request->semester_id);

        if ($request->filled('session_id')) {
            $attendanceQuery->where('session_id', $request->session_id);
        }

        if ($request->filled('course_id')) {
            $attendanceQuery->where('course_id', $request->course_id);
        }

        if ($request->filled('section_id')) {
            $attendanceQuery->where('section_id', $request->section_id);
        }

        if ($request->filled('subject_id')) {
            $attendanceQuery->where('subject_id', $request->subject_id);
        }

        if ($request->filled('month')) {
            $filterYear = $request->filled('year') ? (int) $request->year : Carbon::now()->year;
            $attendanceQuery->whereMonth('date', (int) $request->month)
                ->whereYear('date', $filterYear);
        } elseif ($request->filled('year')) {
            $attendanceQuery->whereYear('date', (int) $request->year);
        }

        if ($request->filled('from_date')) {
            $attendanceQuery->whereDate('date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $attendanceQuery->whereDate('date', '<=', $request->to_date);
        }

        $records = $attendanceQuery
            ->orderBy('date', 'desc')
            ->get();

        $approvedLeaves = $this->attendanceService->approvedLeavesForStudent($student->id);
        $counts = $this->attendanceService->studentAttendanceCounts($records, $approvedLeaves, 2);

        $subjectWise = $records
            ->groupBy('subject_id')
            ->map(function ($subjectRecords) use ($approvedLeaves) {
                $subject = $subjectRecords->first()->subject;
                $subjectCounts = $this->attendanceService->studentAttendanceCounts($subjectRecords, $approvedLeaves, 2);

                return [
                    'subject_id' => $subject?->id,
                    'subject_name' => $subject?->name ?? 'Unknown',
                    'subject_code' => $subject?->code,
                    'total_classes' => $subjectCounts['total'],
                    'present_classes' => $subjectCounts['present'],
                    'absent_classes' => $subjectCounts['absent'],
                    'leave_classes' => $subjectCounts['leave'],
                    'percentage' => $subjectCounts['percentage'],
                ];
            })
            ->values();

        $matchedProfile = $student->studentProfiles->first()
            ?? $profileFilterQuery->with(['session', 'course', 'semester', 'section'])->first()
            ?? $student->studentProfile;

        return response()->json([
            'status' => 'success',
            'data' => [
                'student' => [
                    'id' => $student->id,
                    'name' => $student->name,
                    'email' => $student->email,
                    'roll_no' => $matchedProfile?->roll_no,
                    'university_registration_number' => $matchedProfile?->university_registration_number,
                    'profile_photo' => $student->getProfilePhotoUrl(),
                    'session' => $matchedProfile?->session?->name,
                    'course' => $matchedProfile?->course?->name,
                    'semester' => $matchedProfile?->semester?->name,
                    'section' => $matchedProfile?->section?->name,
                ],
                'summary' => [
                    'total_classes' => $counts['total'],
                    'present_classes' => $counts['present'],
                    'absent_classes' => $counts['absent'],
                    'leave_classes' => $counts['leave'],
                    'percentage' => $counts['percentage'],
                ],
                'subject_wise' => $subjectWise,
                'filters' => [
                    'semester_id' => (int) $request->semester_id,
                    'session_id' => $request->session_id ? (int) $request->session_id : null,
                    'course_id' => $request->course_id ? (int) $request->course_id : null,
                    'section_id' => $request->section_id ? (int) $request->section_id : null,
                    'subject_id' => $request->subject_id ? (int) $request->subject_id : null,
                    'month' => $request->month ? (int) $request->month : null,
                    'year' => $request->year ? (int) $request->year : null,
                    'from_date' => $request->from_date,
                    'to_date' => $request->to_date,
                ],
                'records' => $records->map(function ($record) use ($approvedLeaves) {
                    return [
                        'id' => $record->id,
                        'date' => $record->date,
                        'status' => $this->attendanceService->studentRecordDisplayStatus($record, $approvedLeaves),
                        'subject' => [
                            'id' => $record->subject?->id,
                            'name' => $record->subject?->name,
                            'code' => $record->subject?->code,
                        ],
                    ];
                })->values(),
            ],
        ]);
    }
}

