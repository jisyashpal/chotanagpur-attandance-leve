<?php

use App\Http\Controllers\Api\AcademicController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\LeaveManagementController;
use App\Http\Controllers\Api\NoticeApiController;

// Public Data Routes
Route::get('/sessions', [AcademicController::class, 'sessions'])->name('api.sessions');
Route::get('/batches', [AcademicController::class, 'batches'])->name('api.batches');
Route::get('/courses', [AcademicController::class, 'courses'])->name('api.courses');
Route::get('/semesters', [AcademicController::class, 'semesters'])->name('api.semesters');
Route::get('/subjects', [AcademicController::class, 'subjects'])->name('api.subjects');
Route::get('/sections', [AcademicController::class, 'sections'])->name('api.sections');

// Specific Relationship Routes
Route::get('/courses/{course_id}/batches', [AcademicController::class, 'getBatchesByCourse'])->name('api.courses.batches');
Route::get('/batches/{batch_id}/sessions', [AcademicController::class, 'getSessionsByBatch'])->name('api.batches.sessions');
Route::get('/courses/{course_id}/semesters', [AcademicController::class, 'getSemestersByCourse'])->name('api.courses.semesters');
Route::get('/semesters/{semester_id}/subjects', [AcademicController::class, 'getSubjectsBySemester'])->name('api.semesters.subjects');
Route::get('/semesters/{semester_id}/sections', [AcademicController::class, 'getSectionsBySemester'])->name('api.semesters.sections');

// Public Auth Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/student-login', [AuthController::class, 'studentLogin']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Protected Routes
Route::middleware(['auth:sanctum', 'device.verify'])->group(function () {
    Route::get('/user', [ProfileController::class, 'me']);
    Route::post('/profile/update', [ProfileController::class, 'update']);
    Route::post('/profile/change-password', [ProfileController::class, 'changePassword']);
    Route::get('/profile/sessions', [ProfileController::class, 'getSessions']);
    Route::delete('/profile/sessions/{id}/revoke', [ProfileController::class, 'logoutSession']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Attendance Routes (Restricted to Staff and Faculty)
    Route::middleware('role:staff,faculty')->group(function () {
        Route::prefix('attendance')->group(function () {
            Route::get('/staff-attendance', [AttendanceController::class, 'getAttendanceRecords']);
            Route::post('/staff-mark', [AttendanceController::class, 'markStaffAttendance']);
            Route::post('/students', [AttendanceController::class, 'getStudentsForAttendance']);
            Route::post('/student-mark', [AttendanceController::class, 'markStudentAttendance']);
            Route::get('/student-performance/{student_id}', [AttendanceController::class, 'getSingleStudentPerformance']);
            Route::get('/faculty-attendance-summary', [AttendanceController::class, 'getFacultyAttendanceSummary']);
            Route::post('/faculty-attendance-summary/students', [AttendanceController::class, 'getFacultyAttendanceStudents']);
        });
    });
    
    // Student Routes
    Route::middleware('role:student')->group(function () {
        Route::get('/attendance/student/overall', [AttendanceController::class, 'getStudentOverallAttendance']);
        Route::get('/attendance/student', [AttendanceController::class, 'getStudentAttendanceList']);
    });

    // Leave Routes (User)
    Route::prefix('leaves')->group(function () {
        Route::post('/', [LeaveManagementController::class, 'requestLeave']);
        Route::get('/my', [LeaveManagementController::class, 'myLeaves']);
    });

    // Notices Route
    Route::get('/notices', [NoticeApiController::class, 'index']);

});
