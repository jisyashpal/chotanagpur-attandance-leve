<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Course;
use App\Models\Section;
use App\Models\StudentProfile;
use App\Services\SemesterPromotionService;
use Illuminate\Http\Request;

class StudentPromotionController extends Controller
{
    public function show(Request $request)
    {
        $courses = Course::orderBy('name')->get();
        $targetSessions = collect();
        $targetSections = collect();

        $students = collect();
        if ($request->filled(['course_id', 'batch_id', 'session_id', 'semester_id'])) {
            $query = StudentProfile::with(['user', 'section', 'semester', 'batch', 'session'])
                ->where('course_id', $request->course_id)
                ->where('batch_id', $request->batch_id)
                ->where('session_id', $request->session_id)
                ->where('semester_id', $request->semester_id);

            if ($request->filled('section_id')) {
                $query->where('section_id', $request->section_id);
            }

            $students = $query->get();

            // Same batch sessions for year-end promote; course sections for optional target.
            $targetSessions = AcademicSession::where('batch_id', $request->batch_id)
                ->orderBy('session_year')
                ->get();
            $targetSections = Section::with('semester')
                ->whereHas('semester', fn ($q) => $q->where('course_id', $request->course_id))
                ->orderBy('name')
                ->get();
        }

        return view('admin.users.promote_students', compact(
            'courses',
            'targetSessions',
            'targetSections',
            'students'
        ));
    }

    public function store(Request $request, SemesterPromotionService $service)
    {
        $request->validate([
            'promotion_type' => 'required|in:mid_year,year_end',
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:users,id',
            'target_session_id' => 'required_if:promotion_type,year_end|nullable|exists:academic_sessions,id',
            'target_section_id' => 'nullable|exists:sections,id',
        ]);

        $studentIds = $request->student_ids;

        if ($request->promotion_type === 'mid_year') {
            $summary = $service->promoteMidYear($studentIds, $request->target_section_id);
        } else {
            $summary = $service->promoteYearEnd(
                $studentIds,
                (int) $request->target_session_id,
                $request->target_section_id
            );
        }

        $message = "Promoted {$summary['promoted']} student(s), skipped {$summary['skipped']}.";
        if ($summary['errors'] !== []) {
            $preview = implode(' ', array_slice($summary['errors'], 0, 5));
            $message .= ' Notes: '.$preview;
        }

        return back()->with(
            $summary['promoted'] > 0 ? 'success' : 'error',
            $message
        );
    }
}
