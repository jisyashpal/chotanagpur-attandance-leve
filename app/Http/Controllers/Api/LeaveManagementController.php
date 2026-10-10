<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\StudentAttendance;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Services\AttendanceService;

class LeaveManagementController extends Controller
{
    protected $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    public function requestLeave(Request $request)
    {
        $validated = $request->validate([
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
            'reason' => ['required', 'string', 'max:10000'],
        ]);

        $leave = LeaveRequest::create([
            'user_id' => $request->user()->id,
            'from_date' => $validated['from_date'],
            'to_date' => $validated['to_date'],
            'reason' => $validated['reason'],
            'status' => 'pending',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Leave request submitted successfully.',
            'data' => $leave,
        ], 201);
    }

    public function myLeaves(Request $request)
    {
        $leaves = LeaveRequest::with('approver:id,name')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $leaves,
        ]);
    }

    public function listLeaves(Request $request)
    {
        $query = LeaveRequest::with(['user:id,name,email,role', 'approver:id,name'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $leaves = $query->paginate(20)->withQueryString();

        return response()->json([
            'status' => 'success',
            'data' => $leaves,
        ]);
    }

    public function updateLeaveStatus(Request $request, int $leaveId)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
            'admin_remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        $leave = LeaveRequest::findOrFail($leaveId);

        if ($leave->status !== 'pending') {
            return response()->json([
                'status' => 'error',
                'message' => 'Only pending leave requests can be updated.',
            ], 422);
        }

        $leave->update([
            'status' => $validated['status'],
            'admin_remarks' => $validated['admin_remarks'] ?? null,
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Leave request status updated successfully.',
            'data' => $leave->load(['user:id,name,email,role', 'approver:id,name']),
        ]);
    }

    public function exportMonthlyAttendance(Request $request)
    {
        $validated = $request->validate([
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'format' => ['nullable', 'in:json,csv'],
            'scope' => ['nullable', 'in:staff_faculty,students'],
            'role' => ['nullable', 'in:staff,faculty'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'student_id' => ['nullable', 'integer', 'exists:users,id'],
            'student_search' => ['nullable', 'string', 'max:255'],
            'course_id' => ['nullable', 'integer', 'exists:courses,id'],
            'semester_id' => ['nullable', 'integer', 'exists:semesters,id'],
            'section_id' => ['nullable', 'integer', 'exists:sections,id'],
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'session_id' => ['nullable', 'integer', 'exists:academic_sessions,id'],
            'batch_id' => ['nullable', 'integer', 'exists:batches,id'],
        ]);

        $rows = $this->attendanceService->getMonthlySummary(
            $validated['month'],
            $validated['year'],
            $request
        );

        if (($validated['format'] ?? 'json') === 'csv') {
            $scope = $validated['scope'] ?? null;
            $filename = match ($scope) {
                'staff_faculty' => sprintf('staff_faculty_attendance_report_%04d_%02d.csv', $validated['year'], $validated['month']),
                'students' => sprintf('student_attendance_report_%04d_%02d.csv', $validated['year'], $validated['month']),
                default => sprintf('monthly_attendance_report_%04d_%02d.csv', $validated['year'], $validated['month']),
            };
            $headers = array_keys($rows->first() ?? match ($scope) {
                'staff_faculty' => [
                    'user_id' => '', 'name' => '', 'email' => '', 'role' => '',
                    'employee_id' => '', 'designation' => '', 'department' => '',
                    'total_days' => '', 'present_days' => '',
                    'approved_leave_days' => '', 'absent_days' => '', 'pending_leave_requests' => '',
                ],
                'students' => [
                    'user_id' => '', 'name' => '', 'email' => '', 'role' => '',
                    'roll_no' => '', 'university_registration_number' => '', 'batch' => '',
                    'course' => '', 'semester' => '', 'section' => '', 'subject' => '', 'session' => '',
                    'total_days' => '', 'present_days' => '', 'approved_leave_days' => '',
                    'absent_days' => '', 'pending_leave_requests' => '',
                ],
                default => [
                    'user_id' => '', 'name' => '', 'email' => '', 'role' => '',
                    'course' => '', 'semester' => '', 'session' => '',
                    'total_days' => '', 'present_days' => '', 'approved_leave_days' => '',
                    'absent_days' => '', 'pending_leave_requests' => '',
                ],
            });

            return response()->streamDownload(function () use ($rows, $headers) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $headers);
                foreach ($rows as $row) {
                    fputcsv($file, $row);
                }
                fclose($file);
            }, $filename, ['Content-Type' => 'text/csv']);
        }

        return response()->json([
            'status' => 'success',
            'month' => (int) $validated['month'],
            'year' => (int) $validated['year'],
            'data' => $rows,
        ]);
    }
}
