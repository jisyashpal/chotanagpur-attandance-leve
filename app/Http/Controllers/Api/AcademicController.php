<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Batch;
use App\Models\Course;
use App\Models\Section;
use App\Models\Semester;
use App\Models\Subject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AcademicController extends Controller
{
    /**
     * Get academic sessions (optionally filtered by batch_id).
     */
    public function sessions(Request $request): JsonResponse
    {
        $query = AcademicSession::with('batch')->where('is_active', true);

        if ($request->filled('batch_id')) {
            $query->where('batch_id', $request->batch_id);
        }

        $sessions = $query->latest()->get();

        return response()->json(['sessions' => $sessions]);
    }

    /**
     * Get batches, optionally filtered by course_id.
     */
    public function batches(Request $request): JsonResponse
    {
        $query = Batch::with('course')->where('is_active', true);

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        return response()->json(['batches' => $query->orderBy('start_year', 'desc')->get()]);
    }

    public function getBatchesByCourse($course_id): JsonResponse
    {
        $course = Course::find($course_id);
        if (! $course) {
            return response()->json(['status' => 'error', 'message' => 'Course not found'], 404);
        }

        $batches = Batch::where('course_id', $course_id)->orderBy('start_year', 'desc')->get();

        return response()->json([
            'status' => 'success',
            'course' => $course->name,
            'batches' => $batches,
        ]);
    }

    public function getSessionsByBatch(Request $request, $batch_id): JsonResponse
    {
        $batch = Batch::with('course')->find($batch_id);
        if (! $batch) {
            return response()->json(['status' => 'error', 'message' => 'Batch not found'], 404);
        }

        $query = AcademicSession::where('batch_id', $batch_id);
        if ($request->boolean('active')) {
            $query->where('is_active', true);
        }

        return response()->json([
            'status' => 'success',
            'batch' => $batch->name,
            'course' => $batch->course->name ?? null,
            'sessions' => $query->orderBy('session_year')->get(),
        ]);
    }

    /**
     * Get all courses.
     */
    public function courses(): JsonResponse
    {
        $courses = Course::latest()->get();
        return response()->json(['courses' => $courses]);
    }

    /**
     * Get semesters, optionally filtered by course_id.
     */
    public function semesters(Request $request): JsonResponse
    {
        $query = Semester::with('course');
        
        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        $semesters = $query->latest()->get();
        return response()->json(['semesters' => $semesters]);
    }

    /**
     * Get subjects, optionally filtered by semester_id.
     */
    public function subjects(Request $request): JsonResponse
    {
        $query = Subject::with('semester.course');

        if ($request->filled('semester_id')) {
            $query->where('semester_id', $request->semester_id);
        }

        $subjects = $query->latest()->get();
        return response()->json(['subjects' => $subjects]);
    }

    /**
     * Get sections, optionally filtered by semester_id.
     */
    public function sections(Request $request): JsonResponse
    {
        $query = Section::with('semester.course');

        if ($request->filled('semester_id')) {
            $query->where('semester_id', $request->semester_id);
        }

        $sections = $query->latest()->get();
        return response()->json(['sections' => $sections]);
    }

    /**
     * Get semesters for a specific course.
     */
    public function getSemestersByCourse($course_id): JsonResponse
    {
        $course = Course::find($course_id);
        if (!$course) {
            return response()->json([
                'status' => 'error',
                'message' => 'Course not found'
            ], 404);
        }

        $semesters = Semester::where('course_id', $course_id)->orderBy('semester_number')->get();
        
        return response()->json([
            'status' => 'success',
            'course' => $course->name,
            'semesters' => $semesters
        ]);
    }

    /**
     * Get subjects for a specific semester.
     */
    public function getSubjectsBySemester($semester_id): JsonResponse
    {
        $semester = Semester::with('course')->find($semester_id);
        if (!$semester) {
            return response()->json([
                'status' => 'error',
                'message' => 'Semester not found'
            ], 404);
        }

        $subjects = Subject::where('semester_id', $semester_id)->get();

        return response()->json([
            'status' => 'success',
            'course' => $semester->course->name,
            'semester' => $semester->name,
            'subjects' => $subjects
        ]);
    }

    /**
     * Get sections for a specific semester.
     */
    public function getSectionsBySemester($semester_id): JsonResponse
    {
        $semester = Semester::with('course')->find($semester_id);
        if (!$semester) {
            return response()->json([
                'status' => 'error',
                'message' => 'Semester not found'
            ], 404);
        }

        $sections = Section::where('semester_id', $semester_id)->orderBy('name')->get();

        return response()->json([
            'status' => 'success',
            'course' => $semester->course->name,
            'semester' => $semester->name,
            'sections' => $sections
        ]);
    }
}
