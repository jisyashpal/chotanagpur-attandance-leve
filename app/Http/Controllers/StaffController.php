<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\Attendance;
use App\Services\AttendanceService;

class StaffController extends Controller
{
    public function __construct(protected AttendanceService $attendanceService)
    {
    }

    /**
     * Display the staff portal dashboard.
     */
    public function dashboard(): View
    {
        return view('staff.dashboard');
    }

    /**
     * Display the staff attendance history log.
     */
    public function attendance(): View
    {
        $user = auth()->user();
        
        // Fetch paginated check-in/out attendance logs for staff
        $attendances = Attendance::where('user_id', $user->id)
            ->with('location')
            ->latest('date')
            ->latest('check_in')
            ->paginate(20);

        $leaves = $this->attendanceService->approvedLeavesForStudent($user->id);
        $attendances->getCollection()->transform(function ($attendance) use ($leaves) {
            $attendance->display_status = $this->attendanceService->staffPunchDisplayStatus(
                $attendance->status,
                $attendance->date,
                $leaves
            );

            return $attendance;
        });

        $summary = $this->attendanceService->staffPortalSummary($user->id, 2);

        return view('staff.attendance', [
            'attendances' => $attendances,
            'totalDays' => $summary['total'],
            'presentDays' => $summary['present'],
            'leaveDays' => $summary['leave'],
            'absentDays' => $summary['absent'],
            'attendancePercentage' => $summary['percentage'],
        ]);
    }
}
