<?php

namespace App\Http\Controllers;

use App\Models\AcademicSession;
use App\Models\Attendance;
use App\Models\Course;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use App\Models\StudentAttendance;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        if (auth()->user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        } elseif (auth()->user()->role === 'student') {
            return redirect()->route('student.dashboard');
        } elseif (auth()->user()->role === 'faculty') {
            return redirect()->route('faculty.dashboard');
        } elseif (auth()->user()->role === 'staff') {
            return redirect()->route('staff.dashboard');
        }

        return view('dashboard');
    }

    public function admin()
    {
        $today = now()->toDateString();

        $stats = [
            'students' => User::where('role', 'student')->count(),
            'faculty' => User::where('role', 'faculty')->count(),
            'staff' => User::where('role', 'staff')->count(),
            'pending_profiles' => User::where('role', 'student')->where('is_profile_complete', false)->count(),
            'courses' => Course::count(),
            'active_sessions' => AcademicSession::where('is_active', true)->count(),
            'pending_leaves' => LeaveRequest::where('status', 'pending')->count(),
            'today_student_present' => StudentAttendance::whereDate('date', $today)
                ->where('status', 'present')
                ->distinct()
                ->count('student_id'),
            'today_staff_present' => Attendance::whereDate('date', $today)
                ->whereNotNull('check_in')
                ->distinct()
                ->count('user_id'),
            'active_logins' => DB::table('personal_access_tokens')
                ->where('tokenable_type', User::class)
                ->count(),
        ];

        $pendingLeaves = LeaveRequest::with('user')
            ->where('status', 'pending')
            ->latest()
            ->limit(6)
            ->get();

        $upcomingHolidays = Holiday::whereDate('date', '>=', $today)
            ->orderBy('date')
            ->limit(5)
            ->get();

        $attendanceTrend = collect(range(6, 0))->map(function ($daysAgo) {
            $date = now()->subDays($daysAgo)->toDateString();

            return [
                'label' => now()->subDays($daysAgo)->format('D'),
                'date' => $date,
                'students' => StudentAttendance::whereDate('date', $date)
                    ->where('status', 'present')
                    ->distinct()
                    ->count('student_id'),
                'staff' => Attendance::whereDate('date', $date)
                    ->whereNotNull('check_in')
                    ->distinct()
                    ->count('user_id'),
            ];
        });

        return view('admin.dashboard', compact(
            'stats',
            'pendingLeaves',
            'upcomingHolidays',
            'attendanceTrend'
        ));
    }
}
