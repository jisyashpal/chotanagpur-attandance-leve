<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Models\Course;
use App\Models\Batch;
use App\Models\Semester;
use App\Models\Section;
use App\Models\Subject;
use App\Models\AcademicSession;
use Illuminate\Http\Request;

class LeaveManagementController extends Controller
{
    protected $attendanceService;

    public function __construct(\App\Services\AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    public function index(Request $request)
    {
        $tab = $request->input('tab');
        if ($tab === 'teachers') {
            $tab = 'faculty';
        }
        if (! in_array($tab, ['students', 'faculty', 'staff'], true)) {
            $tab = match ($request->input('role')) {
                'faculty' => 'faculty',
                'staff' => 'staff',
                default => 'students',
            };
        }

        $role = match ($tab) {
            'faculty', 'staff' => $tab,
            default => 'student',
        };

        $query = LeaveRequest::with(['user.studentProfile.course', 'user.studentProfile.semester', 'user.studentProfile.session', 'approver:id,name'])
            ->whereHas('user', function ($userQuery) use ($role, $request) {
                $userQuery->where('role', $role);

                if ($request->filled('search') && ! $request->filled('user_id')) {
                    $search = $request->search;
                    $userQuery->where(function ($inner) use ($search, $role) {
                        $inner->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");

                        if ($role === 'student') {
                            $inner->orWhereHas('studentProfile', function ($profile) use ($search) {
                                $profile->where('roll_no', 'like', "%{$search}%");
                            });
                        } elseif ($role === 'faculty') {
                            $inner->orWhereHas('facultyProfile', function ($profile) use ($search) {
                                $profile->where('employee_id', 'like', "%{$search}%");
                            });
                        } else {
                            $inner->orWhereHas('staffProfile', function ($profile) use ($search) {
                                $profile->where('employee_id', 'like', "%{$search}%");
                            });
                        }
                    });
                }
            })
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('from_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('to_date', '<=', $request->to_date);
        }

        if ($tab === 'students') {
            $request->merge(['role' => 'student']);
            $this->attendanceService->applyAcademicFilters($query, $request);
        } elseif ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $leaves = $query->paginate(15)->withQueryString();

        $users = User::query()
            ->where('role', $role)
            ->when($role === 'student', fn ($q) => $q->with('studentProfile:id,user_id,roll_no'))
            ->when($role === 'faculty', fn ($q) => $q->with('facultyProfile:id,user_id,employee_id'))
            ->when($role === 'staff', fn ($q) => $q->with('staffProfile:id,user_id,employee_id'))
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role']);

        $people = $users->map(function (User $user) use ($role) {
            $meta = match ($role) {
                'student' => $user->studentProfile->roll_no ?? '',
                'faculty' => $user->facultyProfile->employee_id ?? '',
                default => $user->staffProfile->employee_id ?? '',
            };

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'meta' => $meta,
            ];
        })->values();

        $courses = collect();
        $batches = collect();
        $semesters = collect();
        $sessions = collect();
        $sections = collect();
        $subjects = collect();

        if ($tab === 'students') {
            $courses = Course::with(['semesters.subjects'])->orderBy('name')->get();

            if ($request->filled('course_id')) {
                $batches = Batch::where('course_id', $request->course_id)->orderBy('start_year', 'desc')->get();
                $semesters = Semester::where('course_id', $request->course_id)->orderBy('semester_number')->get(['id', 'name', 'course_id', 'part']);
            }

            if ($request->filled('batch_id')) {
                $sessions = AcademicSession::where('batch_id', $request->batch_id)->orderBy('name')->get(['id', 'name']);
            }

            if ($request->filled('semester_id')) {
                $sections = Section::where('semester_id', $request->semester_id)->orderBy('name')->get(['id', 'name', 'semester_id']);
                $subjects = Subject::where('semester_id', $request->semester_id)->orderBy('name')->get(['id', 'name', 'semester_id']);
            }
        }

        return view('admin.leaves.index', compact('leaves', 'users', 'people', 'courses', 'batches', 'semesters', 'sessions', 'sections', 'subjects', 'tab'));
    }

    public function updateStatus(Request $request, int $leaveId)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
            'admin_remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        $leave = LeaveRequest::findOrFail($leaveId);

        if ($leave->status !== 'pending') {
            return back()->with('error', 'Only pending leave requests can be updated.');
        }

        $leave->update([
            'status' => $validated['status'],
            'admin_remarks' => $validated['admin_remarks'] ?? null,
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Leave request status updated successfully.');
    }
}
