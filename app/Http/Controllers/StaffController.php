<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\Attendance;

class StaffController extends Controller
{
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

        // Compute metrics
        $allAttendances = Attendance::where('user_id', $user->id)->get();
        $totalDays = $allAttendances->count();
        $presentDays = $allAttendances->filter(function($record) {
            return in_array(strtolower($record->status), ['present', 'late']);
        })->count();
        $lateDays = $allAttendances->filter(function($record) {
            return strtolower($record->status) === 'late';
        })->count();
        $absentDays = $totalDays - $presentDays;
        
        $attendancePercentage = $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 2) : 0;

        return view('staff.attendance', compact(
            'attendances',
            'totalDays',
            'presentDays',
            'lateDays',
            'absentDays',
            'attendancePercentage'
        ));
    }
}
