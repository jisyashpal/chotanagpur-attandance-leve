<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Attendance;
use App\Models\Batch;
use App\Models\Course;
use App\Models\Section;
use App\Models\Semester;
use App\Models\StudentAttendance;
use App\Models\Subject;
use App\Models\User;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceReportController extends Controller
{
    public function studentAttendance(Request $request)
    {
        [$courses, $batches, $sessions, $semesters, $sections, $subjects] = $this->academicFilterOptions($request);

        $query = StudentAttendance::with(['student.studentProfile', 'subject', 'session', 'course', 'semester', 'section', 'marker']);

        $this->applyStudentAttendanceFilters($query, $request);

        // Student filters
        $selectedStudent = null;
        $stats = null;

        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
            $selectedStudent = User::with('studentProfile')->find($request->student_id);
        } elseif ($request->filled('student_search')) {
            $search = $request->student_search;
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('studentProfile', function ($sp) use ($search) {
                      $sp->where('roll_no', 'like', "%{$search}%")
                                                ->orWhere('university_registration_number', 'like', "%{$search}%");
                  });
            });

            // If the search matches exactly one student, let's treat them as the selected student for stats card
            $matchingStudents = User::where('role', 'student')
                ->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhereHas('studentProfile', function ($sp) use ($search) {
                          $sp->where('roll_no', 'like', "%{$search}%")
                                                        ->orWhere('university_registration_number', 'like', "%{$search}%");
                      });
                })->get();

            if ($matchingStudents->count() === 1) {
                $selectedStudent = $matchingStudents->first()->load('studentProfile');
            }
        }

        // Calculate stats if we have a resolved selected student
        if ($selectedStudent) {
            $statsQuery = StudentAttendance::where('student_id', $selectedStudent->id);
            $this->applyStudentAttendanceFilters($statsQuery, $request);

            $total = $statsQuery->count();
            $present = (clone $statsQuery)->where('status', 'present')->count();
            $absent = (clone $statsQuery)->where('status', 'absent')->count();
            $percentage = $total > 0 ? round(($present / $total) * 100, 1) : 0;

            $stats = [
                'total' => $total,
                'present' => $present,
                'absent' => $absent,
                'percentage' => $percentage
            ];
        }

        $attendances = $query->latest()->paginate(15)->withQueryString();

        return view('admin.attendance.students', compact(
            'sessions',
            'batches',
            'courses',
            'semesters',
            'sections',
            'subjects',
            'attendances',
            'selectedStudent',
            'stats'
        ));
    }

    public function staffFacultyAttendance(Request $request)
    {
        $users = User::whereIn('role', ['staff', 'faculty'])->orderBy('name')->get();

        $query = Attendance::with(['user', 'location'])
            ->whereHas('user', function ($q) {
                $q->whereIn('role', ['staff', 'faculty']);
            });

        // Search or select user filter
        $selectedUser = null;
        $stats = null;

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
            $selectedUser = User::find($request->user_id);
        } elseif ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });

            $matchingUsers = User::whereIn('role', ['staff', 'faculty'])
                ->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                })->get();

            if ($matchingUsers->count() === 1) {
                $selectedUser = $matchingUsers->first();
            }
        }

        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        // Calculate stats if we have a resolved selected user
        if ($selectedUser) {
            $statsQuery = Attendance::where('user_id', $selectedUser->id);
            if ($request->filled('date')) {
                $statsQuery->whereDate('date', $request->date);
            }

            $total = $statsQuery->count();
            $present = (clone $statsQuery)->where('status', 'present')->count();
            $late = (clone $statsQuery)->where('status', 'late')->count();
            $absent = (clone $statsQuery)->where('status', 'absent')->count();
            
            // Attendance percentage can be present + late over total
            $presentOrLate = $present + $late;
            $percentage = $total > 0 ? round(($presentOrLate / $total) * 100, 1) : 0;

            $stats = [
                'total' => $total,
                'present' => $present,
                'late' => $late,
                'absent' => $absent,
                'percentage' => $percentage
            ];
        }

        $attendances = $query->latest()->paginate(15)->withQueryString();

        return view('admin.attendance.staff_faculty', compact(
            'users',
            'attendances',
            'selectedUser',
            'stats'
        ));
    }

    public function monthlyAttendance(Request $request)
    {
        $monthParam = $request->input('month', Carbon::now()->format('Y-m'));
        try {
            $currentDate = Carbon::createFromFormat('Y-m-d', $monthParam . '-01');
        } catch (\Exception $e) {
            $currentDate = Carbon::now()->startOfMonth();
            $monthParam = $currentDate->format('Y-m');
        }
        $year = $currentDate->year;
        $month = $currentDate->month;
        $daysInMonth = $currentDate->daysInMonth;

        // Fetch holidays for the selected month
        $holidays = Holiday::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get()
            ->keyBy(function ($h) {
                return $h->date->format('Y-m-d');
            });

        // 1. Student Attendance Logic
        [$courses, $batches, $sessions, $semesters, $sections, $subjects] = $this->academicFilterOptions($request);

        $selectedStudent = null;
        if ($request->filled('student_id')) {
            $selectedStudent = User::with('studentProfile')->find($request->student_id);
            if ($selectedStudent && $selectedStudent->studentProfile) {
                // Pre-fill parameters if student_id is set
                $request->merge([
                    'batch_id' => $request->input('batch_id', $selectedStudent->studentProfile->batch_id),
                    'session_id' => $request->input('session_id', $selectedStudent->studentProfile->session_id),
                    'course_id' => $request->input('course_id', $selectedStudent->studentProfile->course_id),
                    'semester_id' => $request->input('semester_id', $selectedStudent->studentProfile->semester_id),
                    'section_id' => $request->input('section_id', $selectedStudent->studentProfile->section_id),
                ]);
                [$courses, $batches, $sessions, $semesters, $sections, $subjects] = $this->academicFilterOptions($request);
            }
        }

        $students = collect();
        $studentAttendanceMatrix = [];
        $studentAttendanceSummary = [];
        $isStudentFiltered = false;

        $session_id = $request->session_id;
        $batch_id = $request->batch_id;
        $course_id = $request->course_id;
        $semester_id = $request->semester_id;
        $section_id = $request->section_id;
        $subject_id = $request->subject_id;

        if ($course_id && $semester_id) {
            $isStudentFiltered = true;
            $studentsQuery = User::where('role', 'student')
                ->whereHas('studentProfile', function ($q) use ($course_id, $semester_id, $session_id, $batch_id, $section_id) {
                    $q->where('course_id', $course_id)
                      ->where('semester_id', $semester_id);
                    if ($batch_id) {
                        $q->where('batch_id', $batch_id);
                    }
                    if ($session_id) {
                        $q->where('session_id', $session_id);
                    }
                    if ($section_id) {
                        $q->where('section_id', $section_id);
                    }
                });

            if ($request->filled('student_id')) {
                $studentsQuery->where('id', $request->student_id);
            }

            $students = $studentsQuery->orderBy('name')->get();
            $studentIds = $students->pluck('id');

            // Fetch approved leaves for students
            $startOfMonth = $currentDate->copy()->startOfMonth();
            $endOfMonth = $currentDate->copy()->endOfMonth();
            $studentLeaves = LeaveRequest::whereIn('user_id', $studentIds)
                ->where('status', 'approved')
                ->where(function ($query) use ($startOfMonth, $endOfMonth) {
                    $query->whereBetween('from_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
                        ->orWhereBetween('to_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
                        ->orWhere(function ($q) use ($startOfMonth, $endOfMonth) {
                            $q->where('from_date', '<=', $startOfMonth->toDateString())
                                ->where('to_date', '>=', $endOfMonth->toDateString());
                        });
                })->get()->groupBy('user_id');

            if ($subject_id) {
                // Fetch attendance records for this subject
                $attendanceRecords = StudentAttendance::whereIn('student_id', $studentIds)
                    ->where('subject_id', $subject_id)
                    ->whereYear('date', $year)
                    ->whereMonth('date', $month)
                    ->get()
                    ->groupBy('student_id');

                foreach ($students as $student) {
                    $studentAttendanceMatrix[$student->id] = [];
                    $records = $attendanceRecords->get($student->id, collect())->keyBy('date');
                    
                    $presentCount = 0;
                    $absentCount = 0;
                    $totalMarked = 0;

                    for ($day = 1; $day <= $daysInMonth; $day++) {
                        $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $day);
                        $carbonDate = Carbon::parse($dateStr);
                        
                        $status = '';
                        $detail = '';

                        if ($carbonDate->isSunday()) {
                            $status = 'sunday';
                        }
                        
                        if ($holidays->has($dateStr)) {
                            $status = 'holiday';
                            $detail = $holidays->get($dateStr)->name;
                        }

                        $userLeaves = $studentLeaves->get($student->id, collect());
                        $isOnLeave = $userLeaves->contains(function ($leave) use ($dateStr) {
                            return $dateStr >= $leave->from_date && $dateStr <= $leave->to_date;
                        });
                        if ($isOnLeave) {
                            $status = 'leave';
                        }

                        if ($records->has($dateStr)) {
                            $rec = $records->get($dateStr);
                            $status = strtolower($rec->status) === 'present' ? 'present' : 'absent';
                            if ($status === 'present') {
                                $presentCount++;
                            } else {
                                $absentCount++;
                            }
                            $totalMarked++;
                        }

                        $studentAttendanceMatrix[$student->id][$day] = [
                            'status' => $status,
                            'detail' => $detail
                        ];
                    }

                    $percentage = $totalMarked > 0 ? round(($presentCount / $totalMarked) * 100, 1) : 0;
                    $studentAttendanceSummary[$student->id] = [
                        'present' => $presentCount,
                        'absent' => $absentCount,
                        'total' => $totalMarked,
                        'percentage' => $percentage
                    ];
                }
            } else {
                // Aggregated summary table across all subjects
                $attendanceRecords = StudentAttendance::whereIn('student_id', $studentIds)
                    ->whereYear('date', $year)
                    ->whereMonth('date', $month)
                    ->get()
                    ->groupBy('student_id');

                foreach ($students as $student) {
                    $records = $attendanceRecords->get($student->id, collect());
                    $presentCount = $records->where('status', 'present')->count();
                    $absentCount = $records->where('status', 'absent')->count();
                    $totalMarked = $records->count();
                    $percentage = $totalMarked > 0 ? round(($presentCount / $totalMarked) * 100, 1) : 0;

                    $studentAttendanceSummary[$student->id] = [
                        'present' => $presentCount,
                        'absent' => $absentCount,
                        'total' => $totalMarked,
                        'percentage' => $percentage
                    ];
                }
            }
        }

        // 2. Faculty/Staff Attendance Logic
        $selectedUser = null;
        if ($request->filled('user_id')) {
            $selectedUser = User::find($request->user_id);
            if ($selectedUser) {
                $request->merge([
                    'role' => $request->input('role', $selectedUser->role)
                ]);
            }
        }

        $staffQuery = User::whereIn('role', ['staff', 'faculty']);
        if ($request->filled('role')) {
            $staffQuery->where('role', $request->role);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $staffQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        if ($request->filled('user_id')) {
            $staffQuery->where('id', $request->user_id);
        }

        $staffUsers = $staffQuery->orderBy('name')->get();
        $staffIds = $staffUsers->pluck('id');

        $staffAttendanceRecords = Attendance::whereIn('user_id', $staffIds)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get()
            ->groupBy('user_id');

        $startOfMonth = $currentDate->copy()->startOfMonth();
        $endOfMonth = $currentDate->copy()->endOfMonth();

        $staffLeaves = LeaveRequest::whereIn('user_id', $staffIds)
            ->where('status', 'approved')
            ->where(function ($query) use ($startOfMonth, $endOfMonth) {
                $query->whereBetween('from_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
                    ->orWhereBetween('to_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
                    ->orWhere(function ($q) use ($startOfMonth, $endOfMonth) {
                        $q->where('from_date', '<=', $startOfMonth->toDateString())
                            ->where('to_date', '>=', $endOfMonth->toDateString());
                    });
            })->get()->groupBy('user_id');

        $staffAttendanceMatrix = [];
        $staffAttendanceSummary = [];

        foreach ($staffUsers as $staff) {
            $staffAttendanceMatrix[$staff->id] = [];
            $records = $staffAttendanceRecords->get($staff->id, collect())->keyBy('date');
            
            $presentCount = 0;
            $lateCount = 0;
            $absentCount = 0;
            $leaveCount = 0;
            $totalMarked = 0;

            for ($day = 1; $day <= $daysInMonth; $day++) {
                $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $day);
                $carbonDate = Carbon::parse($dateStr);
                
                $status = '';
                $detail = '';

                if ($carbonDate->isSunday()) {
                    $status = 'sunday';
                }
                
                if ($holidays->has($dateStr)) {
                    $status = 'holiday';
                    $detail = $holidays->get($dateStr)->name;
                }

                $userLeaves = $staffLeaves->get($staff->id, collect());
                $isOnLeave = $userLeaves->contains(function ($leave) use ($dateStr) {
                    return $dateStr >= $leave->from_date && $dateStr <= $leave->to_date;
                });
                if ($isOnLeave) {
                    $status = 'leave';
                    $leaveCount++;
                }

                if ($records->has($dateStr)) {
                    $rec = $records->get($dateStr);
                    $status = strtolower($rec->status);
                    
                    if ($status === 'present') {
                        $presentCount++;
                    } elseif ($status === 'late') {
                        $lateCount++;
                    } elseif ($status === 'absent') {
                        $absentCount++;
                    }

                    $checkInStr = $rec->check_in ? Carbon::parse($rec->check_in)->format('h:i A') : '';
                    $checkOutStr = $rec->check_out ? Carbon::parse($rec->check_out)->format('h:i A') : '';
                    if ($checkInStr || $checkOutStr) {
                        $detail = "In: " . ($checkInStr ?: '-') . " | Out: " . ($checkOutStr ?: '-');
                    }
                    $totalMarked++;
                } else {
                    if ($carbonDate->isPast() && !$carbonDate->isToday() && $status === '') {
                        $status = 'absent';
                        $absentCount++;
                        $totalMarked++;
                    }
                }

                $staffAttendanceMatrix[$staff->id][$day] = [
                    'status' => $status,
                    'detail' => $detail
                ];
            }

            $totalWorking = $presentCount + $lateCount + $absentCount;
            $presentOrLate = $presentCount + $lateCount;
            $percentage = $totalWorking > 0 ? round(($presentOrLate / $totalWorking) * 100, 1) : 0;

            $staffAttendanceSummary[$staff->id] = [
                'present' => $presentCount,
                'late' => $lateCount,
                'absent' => $absentCount,
                'leave' => $leaveCount,
                'total' => $totalWorking,
                'percentage' => $percentage
            ];
        }

        return view('admin.attendance.monthly', compact(
            'sessions',
            'batches',
            'courses',
            'semesters',
            'sections',
            'subjects',
            'monthParam',
            'currentDate',
            'daysInMonth',
            'holidays',
            'students',
            'studentAttendanceMatrix',
            'studentAttendanceSummary',
            'isStudentFiltered',
            'staffUsers',
            'staffAttendanceMatrix',
            'staffAttendanceSummary',
            'selectedStudent',
            'selectedUser'
        ));
    }

    public function classMarkings(Request $request)
    {
        [$courses, $batches, $sessions, $semesters, $sections, $subjects] = $this->academicFilterOptions($request);
        $teachers = User::whereIn('role', ['staff', 'faculty'])->orderBy('name')->get();

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
                'subject:id,name,code',
                'course:id,name',
                'semester:id,name',
                'section:id,name',
                'session:id,name',
            ]);

        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }
        if ($request->filled('marked_by')) {
            $query->where('marked_by', $request->marked_by);
        }
        $this->applyStudentAttendanceFilters($query, $request, includeDate: false);

        $markings = $query
            ->groupBy('marked_by', 'session_id', 'course_id', 'semester_id', 'section_id', 'subject_id', 'date')
            ->orderByDesc('date')
            ->paginate(20)
            ->withQueryString();

        return view('admin.attendance.class_markings', compact(
            'sessions',
            'batches',
            'courses',
            'semesters',
            'sections',
            'subjects',
            'teachers',
            'markings'
        ));
    }

    public function classMarkingStudents(Request $request)
    {
        $validated = $request->validate([
            'session_id' => 'required|exists:academic_sessions,id',
            'course_id' => 'required|exists:courses,id',
            'semester_id' => 'required|exists:semesters,id',
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'date' => 'required|date',
            'marked_by' => 'required|exists:users,id',
        ]);

        $teacher = User::findOrFail($validated['marked_by']);
        $session = AcademicSession::findOrFail($validated['session_id']);
        $course = Course::findOrFail($validated['course_id']);
        $semester = Semester::findOrFail($validated['semester_id']);
        $section = Section::findOrFail($validated['section_id']);
        $subject = Subject::findOrFail($validated['subject_id']);
        $date = $validated['date'];

        $enrolledStudents = User::where('role', 'student')
            ->whereHas('studentProfile', function ($q) use ($validated) {
                $q->where('session_id', $validated['session_id'])
                    ->where('course_id', $validated['course_id'])
                    ->where('semester_id', $validated['semester_id'])
                    ->where('section_id', $validated['section_id']);
            })
            ->with(['studentProfile'])
            ->get();

        $attendanceRecords = StudentAttendance::with(['student.studentProfile'])
            ->where('session_id', $validated['session_id'])
            ->where('course_id', $validated['course_id'])
            ->where('semester_id', $validated['semester_id'])
            ->where('section_id', $validated['section_id'])
            ->where('subject_id', $validated['subject_id'])
            ->whereDate('date', $validated['date'])
            ->where('marked_by', $validated['marked_by'])
            ->get();

        $attendanceMap = $attendanceRecords->pluck('status', 'student_id');

        $allUsers = $enrolledStudents->keyBy('id');
        foreach ($attendanceRecords as $record) {
            if ($record->student && ! $allUsers->has($record->student_id)) {
                $allUsers->put($record->student_id, $record->student);
            }
        }

        $students = $allUsers->values()->map(function ($user) use ($attendanceMap) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'roll_no' => $user->studentProfile->roll_no ?? 'N/A',
                'profile_photo' => $user->getProfilePhotoUrl(),
                'status' => $attendanceMap->get($user->id, 'absent'),
                'was_marked' => $attendanceMap->has($user->id),
            ];
        })->sortBy('roll_no', SORT_NATURAL)->values();

        $presentCount = $attendanceRecords->where('status', 'present')->count();
        $absentCount = $attendanceRecords->where('status', 'absent')->count();
        $editMode = $request->boolean('edit');

        return view('admin.attendance.class_marking_students', compact(
            'teacher',
            'session',
            'course',
            'semester',
            'section',
            'subject',
            'date',
            'students',
            'presentCount',
            'absentCount',
            'editMode'
        ));
    }

    public function updateClassMarkingStudents(Request $request)
    {
        $validated = $request->validate([
            'session_id' => 'required|exists:academic_sessions,id',
            'course_id' => 'required|exists:courses,id',
            'semester_id' => 'required|exists:semesters,id',
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'date' => 'required|date',
            'marked_by' => 'required|exists:users,id',
            'attendance' => 'required|array|min:1',
            'attendance.*' => 'required|in:present,absent',
        ]);

        $date = Carbon::parse($validated['date'])->format('Y-m-d');
        $studentIds = array_map('intval', array_keys($validated['attendance']));

        $enrolledIds = User::whereIn('id', $studentIds)
            ->where('role', 'student')
            ->whereHas('studentProfile', function ($query) use ($validated) {
                $query->where('session_id', $validated['session_id'])
                    ->where('course_id', $validated['course_id'])
                    ->where('semester_id', $validated['semester_id'])
                    ->where('section_id', $validated['section_id']);
            })
            ->pluck('id')
            ->all();

        $alreadyMarkedIds = StudentAttendance::whereIn('student_id', $studentIds)
            ->where('session_id', $validated['session_id'])
            ->where('course_id', $validated['course_id'])
            ->where('semester_id', $validated['semester_id'])
            ->where('section_id', $validated['section_id'])
            ->where('subject_id', $validated['subject_id'])
            ->whereDate('date', $date)
            ->where('marked_by', $validated['marked_by'])
            ->pluck('student_id')
            ->all();

        $allowedIds = array_unique(array_merge($enrolledIds, $alreadyMarkedIds));
        $invalid = array_diff($studentIds, $allowedIds);

        if (! empty($invalid)) {
            return back()->withErrors(['attendance' => 'One or more students do not belong to this class marking.'])->withInput();
        }

        $batchId = AcademicSession::where('id', $validated['session_id'])->value('batch_id');

        DB::beginTransaction();
        try {
            foreach ($validated['attendance'] as $studentId => $status) {
                StudentAttendance::updateOrCreate(
                    [
                        'student_id' => (int) $studentId,
                        'subject_id' => $validated['subject_id'],
                        'section_id' => $validated['section_id'],
                        'date' => $date,
                    ],
                    [
                        'batch_id' => $batchId,
                        'session_id' => $validated['session_id'],
                        'course_id' => $validated['course_id'],
                        'semester_id' => $validated['semester_id'],
                        'status' => $status,
                        'marked_by' => $validated['marked_by'],
                    ]
                );
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['attendance' => 'Failed to update attendance: ' . $e->getMessage()])->withInput();
        }

        return redirect()
            ->route('admin.attendance.class_markings.students', [
                'marked_by' => $validated['marked_by'],
                'session_id' => $validated['session_id'],
                'course_id' => $validated['course_id'],
                'semester_id' => $validated['semester_id'],
                'section_id' => $validated['section_id'],
                'subject_id' => $validated['subject_id'],
                'date' => $date,
                'edit' => 1,
            ])
            ->with('success', 'Student attendance updated successfully.');
    }

    /**
     * Shared dropdown options for Course → Batch → Session → Semester → Section filters.
     */
    private function academicFilterOptions(Request $request): array
    {
        $courses = Course::with(['semesters.sections', 'semesters.subjects'])->orderBy('name')->get();

        if ($request->filled('course_id')) {
            $batches = Batch::where('course_id', $request->course_id)->orderBy('start_year', 'desc')->get();
            $semesters = Semester::where('course_id', $request->course_id)->orderBy('semester_number')->get();
        } else {
            $batches = collect();
            $semesters = collect();
        }

        if ($request->filled('batch_id')) {
            $sessions = AcademicSession::where('batch_id', $request->batch_id)->orderBy('name')->get();
        } else {
            $sessions = collect();
        }

        if ($request->filled('semester_id')) {
            $sections = Section::where('semester_id', $request->semester_id)->orderBy('name')->get();
            $subjects = Subject::where('semester_id', $request->semester_id)->orderBy('name')->get();
        } else {
            $sections = collect();
            $subjects = collect();
        }

        return [$courses, $batches, $sessions, $semesters, $sections, $subjects];
    }

    /**
     * Apply academic + date filters on StudentAttendance queries.
     */
    private function applyStudentAttendanceFilters($query, Request $request, bool $includeDate = true): void
    {
        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }
        if ($request->filled('batch_id')) {
            $query->where('batch_id', $request->batch_id);
        }
        if ($request->filled('session_id')) {
            $query->where('session_id', $request->session_id);
        }
        if ($request->filled('semester_id')) {
            $query->where('semester_id', $request->semester_id);
        }
        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }
        if ($includeDate && $request->filled('date')) {
            $query->whereDate('date', $request->date);
        }
    }
}
