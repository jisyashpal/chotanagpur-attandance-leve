<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StudentAttendance;
use App\Models\Subject;
use Carbon\Carbon;

class StudentController extends Controller
{
    /**
     * Display the student portal dashboard.
     */
    public function dashboard()
    {
        $user = auth()->user();
        
        // Fetch all attendance records for this student
        $attendances = StudentAttendance::where('student_id', $user->id)
            ->with('subject')
            ->get();
            
        // Calculate overall stats
        $totalClasses = $attendances->count();
        $presentClasses = $attendances->filter(function ($record) {
            return strtolower($record->status) === 'present';
        })->count();
        $absentClasses = $totalClasses - $presentClasses;
        $overallPercentage = $totalClasses > 0 ? round(($presentClasses / $totalClasses) * 100, 2) : 0;
        
        // Calculate subject-wise breakdown
        $subjectSummary = [];
        $grouped = $attendances->groupBy('subject_id');
        
        // Get all subjects for the student's current semester
        $studentProfile = $user->studentProfile;
        $subjects = collect();
        if ($studentProfile && $studentProfile->semester_id) {
            $subjects = Subject::where('semester_id', $studentProfile->semester_id)->get();
        }
        
        foreach ($subjects as $subject) {
            $subjectRecords = $grouped->get($subject->id, collect());
            $subTotal = $subjectRecords->count();
            $subPresent = $subjectRecords->filter(function ($record) {
                return strtolower($record->status) === 'present';
            })->count();
            $subAbsent = $subTotal - $subPresent;
            $subPercentage = $subTotal > 0 ? round(($subPresent / $subTotal) * 100, 2) : 0;
            
            $subjectSummary[] = [
                'name' => $subject->name,
                'code' => $subject->code,
                'total' => $subTotal,
                'present' => $subPresent,
                'absent' => $subAbsent,
                'percentage' => $subPercentage
            ];
        }
        
        return view('student.dashboard', compact(
            'totalClasses', 
            'presentClasses', 
            'absentClasses', 
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
            
        // Calculate overall statistics
        $allAttendances = StudentAttendance::where('student_id', $user->id)->get();
        $totalClasses = $allAttendances->count();
        $presentClasses = $allAttendances->filter(function ($record) {
            return strtolower($record->status) === 'present';
        })->count();
        $absentClasses = $totalClasses - $presentClasses;
        $overallPercentage = $totalClasses > 0 ? round(($presentClasses / $totalClasses) * 100, 2) : 0;
        $studentProfile = $user->studentProfile;

        return view('student.attendance', compact(
            'attendances',
            'totalClasses',
            'presentClasses',
            'absentClasses',
            'overallPercentage',
            'studentProfile'
        ));
    }
}
