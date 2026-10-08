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
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AttendanceController extends Controller
{
    /**
     * Mark Staff/Faculty Attendance (Self)
     */

    public function getAttendanceRecords(Request $request)
    {
        $user = $request->user();
        $records = Attendance::where('user_id', $user->id)
            ->orderBy('date', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $records
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

        DB::beginTransaction();
        try {
            foreach ($request->attendance as $studentId => $status) {
                StudentAttendance::updateOrCreate(
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
            }
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Student attendance marked successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to mark attendance: ' . $e->getMessage()
            ], 500);
        }
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

        $grouped = $records->groupBy('subject_id')->map(function ($subjectRecords) {
            $subject = $subjectRecords->first()->subject;
            $totalClasses = $subjectRecords->count();
            $attendedClasses = $subjectRecords->filter(function ($record) {
                return strtolower($record->status) === 'present';
            })->count();
            $percentage = $totalClasses > 0 ? round(($attendedClasses / $totalClasses) * 100, 2) : 0;

            return [
                'subject_id' => $subject->id ?? null,
                'subject_name' => $subject->name ?? 'Unknown',
                'total_classes' => $totalClasses,
                'attended_classes' => $attendedClasses,
                'percentage' => $percentage,
                'records' => $subjectRecords->map(function ($record) {
                    return [
                        'id' => $record->id,
                        'date' => $record->date,
                        'status' => $record->status,
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

        $presentCount = $records->where('status', 'present')->count();
        $absentCount = $records->where('status', 'absent')->count();
        $totalMarked = $records->count();
        $percentage = $totalMarked > 0 ? round(($presentCount / $totalMarked) * 100, 1) : 0;

        return response()->json([
            'status' => 'success',
            'data' => [
                'filter' => $period['filter'],
                'month' => $period['month'],
                'semester_id' => $period['semester_id'],
                'semester_name' => $period['semester_name'],
                'total_classes' => $totalMarked,
                'attended' => $presentCount,
                'absent' => $absentCount,
                'percentage' => $percentage,
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
        $query = StudentAttendance::query()
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
            ]);

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

        $summary = $query->groupBy('marked_by', 'session_id', 'course_id', 'semester_id', 'section_id', 'subject_id', 'date')
            ->orderBy('date', 'desc')
            ->get()
            ->map(function ($record) {
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
                    'absent_count' => (int) $record->absent_count,
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

        $records = $allUsers->values()->map(function ($user) use ($attendanceMap) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'roll_no' => $user->studentProfile->roll_no ?? 'N/A',
                'profile_photo' => $user->getProfilePhotoUrl(),
                'status' => $attendanceMap->get($user->id, 'absent'),
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

        $totalClasses = $records->count();
        $presentClasses = $records->filter(fn ($r) => strtolower((string) $r->status) === 'present')->count();
        $absentClasses = $records->filter(fn ($r) => strtolower((string) $r->status) === 'absent')->count();
        $percentage = $totalClasses > 0 ? round(($presentClasses / $totalClasses) * 100, 2) : 0;

        $subjectWise = $records
            ->groupBy('subject_id')
            ->map(function ($subjectRecords) {
                $subject = $subjectRecords->first()->subject;
                $subjectTotal = $subjectRecords->count();
                $subjectPresent = $subjectRecords->filter(fn ($r) => strtolower((string) $r->status) === 'present')->count();
                $subjectAbsent = $subjectRecords->filter(fn ($r) => strtolower((string) $r->status) === 'absent')->count();

                return [
                    'subject_id' => $subject?->id,
                    'subject_name' => $subject?->name ?? 'Unknown',
                    'subject_code' => $subject?->code,
                    'total_classes' => $subjectTotal,
                    'present_classes' => $subjectPresent,
                    'absent_classes' => $subjectAbsent,
                    'percentage' => $subjectTotal > 0 ? round(($subjectPresent / $subjectTotal) * 100, 2) : 0,
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
                    'total_classes' => $totalClasses,
                    'present_classes' => $presentClasses,
                    'absent_classes' => $absentClasses,
                    'percentage' => $percentage,
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
                'records' => $records->map(function ($record) {
                    return [
                        'id' => $record->id,
                        'date' => $record->date,
                        'status' => $record->status,
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

