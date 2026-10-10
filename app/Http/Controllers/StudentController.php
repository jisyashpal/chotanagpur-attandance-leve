<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StudentAttendance;
use App\Models\Subject;
use App\Services\AttendanceService;

class StudentController extends Controller
{
    public function __construct(protected AttendanceService $attendanceService)
    {
    }

    /**
     * Display the student portal dashboard.
     */
    public function dashboard()
    {
        $user = auth()->user();

        $attendances = StudentAttendance::where('student_id', $user->id)
            ->with('subject')
            ->get();

        $approvedLeaves = $this->attendanceService->approvedLeavesForStudent($user->id);
        $overall = $this->attendanceService->studentAttendanceCounts($attendances, $approvedLeaves, 2);
        $totalClasses = $overall['total'];
        $presentClasses = $overall['present'];
        $absentClasses = $overall['absent'];
        $leaveClasses = $overall['leave'];
        $overallPercentage = $overall['percentage'];

        $subjectSummary = [];
        $grouped = $attendances->groupBy('subject_id');

        $studentProfile = $user->studentProfile;
        $subjects = collect();
        if ($studentProfile && $studentProfile->semester_id) {
            $subjects = Subject::where('semester_id', $studentProfile->semester_id)->get();
        }

        foreach ($subjects as $subject) {
            $subjectRecords = $grouped->get($subject->id, collect());
            $counts = $this->attendanceService->studentAttendanceCounts($subjectRecords, $approvedLeaves, 2);

            $subjectSummary[] = [
                'name' => $subject->name,
                'code' => $subject->code,
                'total' => $counts['total'],
                'present' => $counts['present'],
                'absent' => $counts['absent'],
                'leave' => $counts['leave'],
                'percentage' => $counts['percentage'],
            ];
        }

        return view('student.dashboard', compact(
            'totalClasses',
            'presentClasses',
            'absentClasses',
            'leaveClasses',
            'overallPercentage',
            'subjectSummary',
            'studentProfile'
        ));
    }

    /**
     * Display the student attendance history.
     */
    public function attendance()
    {
        $user = auth()->user();
        
        // Fetch all attendance records for this student with subject and marker, sorted by date desc
        $attendances = StudentAttendance::where('student_id', $user->id)
            ->with(['subject', 'marker'])
            ->latest('date')
            ->paginate(20);
            
        $allAttendances = StudentAttendance::where('student_id', $user->id)->get();
        $overall = $this->attendanceService->studentAttendanceCounts(
            $allAttendances,
            $this->attendanceService->approvedLeavesForStudent($user->id),
            2
        );
        $totalClasses = $overall['total'];
        $presentClasses = $overall['present'];
        $absentClasses = $overall['absent'];
        $leaveClasses = $overall['leave'];
        $overallPercentage = $overall['percentage'];
        $studentProfile = $user->studentProfile;

        return view('student.attendance', compact(
            'attendances',
            'totalClasses',
            'presentClasses',
            'absentClasses',
            'leaveClasses',
            'overallPercentage',
            'studentProfile'
        ));
    }
}
