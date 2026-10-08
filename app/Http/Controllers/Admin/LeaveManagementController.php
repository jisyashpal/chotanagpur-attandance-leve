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
        $query = LeaveRequest::with(['user.studentProfile.course', 'user.studentProfile.semester', 'user.studentProfile.session', 'approver:id,name'])
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

        $this->attendanceService->applyAcademicFilters($query, $request);

        $leaves = $query->paginate(15)->withQueryString();
        $courses = Course::with(['semesters.subjects'])->orderBy('name')->get();

        if ($request->filled('course_id')) {
            $batches = Batch::where('course_id', $request->course_id)->orderBy('start_year', 'desc')->get();
            $semesters = Semester::where('course_id', $request->course_id)->orderBy('semester_number')->get(['id', 'name', 'course_id', 'part']);
        } else {
            $batches = collect();
            $semesters = collect();
        }

        if ($request->filled('batch_id')) {
            $sessions = AcademicSession::where('batch_id', $request->batch_id)->orderBy('name')->get(['id', 'name']);
        } else {
            $sessions = collect();
        }

        if ($request->filled('semester_id')) {
            $sections = Section::where('semester_id', $request->semester_id)->orderBy('name')->get(['id', 'name', 'semester_id']);
            $subjects = Subject::where('semester_id', $request->semester_id)->orderBy('name')->get(['id', 'name', 'semester_id']);
        } else {
            $sections = collect();
            $subjects = collect();
        }

        $users = User::whereIn('role', ['student', 'faculty', 'staff'])
            ->orderBy('name')
            ->get(['id', 'name', 'role']);

        return view('admin.leaves.index', compact('leaves', 'users', 'courses', 'batches', 'semesters', 'sessions', 'sections', 'subjects'));
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
