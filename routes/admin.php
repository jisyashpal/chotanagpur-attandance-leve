<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\AcademicManagementController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\AttendanceReportController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\LeaveManagementController;
use App\Http\Controllers\Admin\LocationSearchController;
use App\Http\Controllers\Admin\NoticeController;
use App\Http\Controllers\Admin\HolidayController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\StudentPromotionController;
use App\Http\Controllers\Api\LeaveManagementController as ApiLeaveManagementController;

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');
    
    // Academic Management
    Route::prefix('academy')->name('academy.')->group(function () {
        Route::get('/batches', [AcademicManagementController::class, 'batches'])->name('batches');
        Route::post('/batches', [AcademicManagementController::class, 'storeBatch'])->name('batches.store');
        Route::put('/batches/{id}', [AcademicManagementController::class, 'updateBatch'])->name('batches.update');
        Route::delete('/batches/{id}', [AcademicManagementController::class, 'destroyBatch'])->name('batches.destroy');

        Route::get('/sessions', [AcademicManagementController::class, 'sessions'])->name('sessions');
        Route::post('/sessions', [AcademicManagementController::class, 'storeSession'])->name('sessions.store');
        Route::put('/sessions/{id}', [AcademicManagementController::class, 'updateSession'])->name('sessions.update');
        Route::delete('/sessions/{id}', [AcademicManagementController::class, 'destroySession'])->name('sessions.destroy');
        
        Route::get('/courses', [AcademicManagementController::class, 'courses'])->name('courses');
        Route::post('/courses', [AcademicManagementController::class, 'storeCourse'])->name('courses.store');
        Route::put('/courses/{id}', [AcademicManagementController::class, 'updateCourse'])->name('courses.update');
        Route::delete('/courses/{id}', [AcademicManagementController::class, 'destroyCourse'])->name('courses.destroy');
        
        Route::get('/semesters', [AcademicManagementController::class, 'semesters'])->name('semesters');
        Route::post('/semesters', [AcademicManagementController::class, 'storeSemester'])->name('semesters.store');
        Route::put('/semesters/{id}', [AcademicManagementController::class, 'updateSemester'])->name('semesters.update');
        Route::delete('/semesters/{id}', [AcademicManagementController::class, 'destroySemester'])->name('semesters.destroy');

        Route::get('/sections', [AcademicManagementController::class, 'sections'])->name('sections');
        Route::post('/sections', [AcademicManagementController::class, 'storeSection'])->name('sections.store');
        Route::put('/sections/{id}', [AcademicManagementController::class, 'updateSection'])->name('sections.update');
        Route::delete('/sections/{id}', [AcademicManagementController::class, 'destroySection'])->name('sections.destroy');
        
        Route::get('/subjects', [AcademicManagementController::class, 'subjects'])->name('subjects');
        Route::post('/subjects', [AcademicManagementController::class, 'storeSubject'])->name('subjects.store');
        Route::put('/subjects/{id}', [AcademicManagementController::class, 'updateSubject'])->name('subjects.update');
        Route::delete('/subjects/{id}', [AcademicManagementController::class, 'destroySubject'])->name('subjects.destroy');
        
        Route::get('/allocations', [AcademicManagementController::class, 'allocations'])->name('allocations');
        Route::post('/allocations', [AcademicManagementController::class, 'storeAllocation'])->name('allocations.store');
        Route::delete('/allocations/{id}', [AcademicManagementController::class, 'destroyAllocation'])->name('allocations.destroy');

        Route::get('/routines', [AcademicManagementController::class, 'routines'])->name('routines');
        Route::post('/routines', [AcademicManagementController::class, 'storeRoutine'])->name('routines.store');
        Route::put('/routines/{id}', [AcademicManagementController::class, 'updateRoutine'])->name('routines.update');
        Route::delete('/routines/{id}', [AcademicManagementController::class, 'destroyRoutine'])->name('routines.destroy');

        // AJax helper routes for admin panel (to avoid querying API with VerifyAppKey middleware)
        Route::get('/courses/{course_id}/semesters-json', [AcademicManagementController::class, 'getSemestersByCourseJson'])->name('courses.semesters_json');
        Route::get('/courses/{course_id}/batches-json', [AcademicManagementController::class, 'getBatchesByCourseJson'])->name('courses.batches_json');
        Route::get('/batches/{batch_id}/sessions-json', [AcademicManagementController::class, 'getSessionsByBatchJson'])->name('batches.sessions_json');
        Route::get('/semesters-json', [AcademicManagementController::class, 'getSemestersJson'])->name('semesters_json');
        Route::get('/semesters/{semester_id}/sections-json', [AcademicManagementController::class, 'getSectionsBySemesterJson'])->name('semesters.sections_json');
        Route::get('/semesters/{semester_id}/subjects-json', [AcademicManagementController::class, 'getSubjectsBySemesterJson'])->name('semesters.subjects_json');
    });

    // User Management
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/staff-faculty', [UserManagementController::class, 'staffFaculty'])->name('staff_faculty');
        Route::get('/staff-faculty/{id}', [UserManagementController::class, 'showStaffFaculty'])->name('staff_faculty.show');
        Route::get('/staff-faculty/{id}/edit', [UserManagementController::class, 'editStaffFaculty'])->name('staff_faculty.edit');
        Route::put('/staff-faculty/{id}', [UserManagementController::class, 'updateStaffFaculty'])->name('staff_faculty.update');
        Route::delete('/staff-faculty/{id}', [UserManagementController::class, 'destroyStaffFaculty'])->name('staff_faculty.destroy');
        Route::get('/students', [UserManagementController::class, 'students'])->name('students');
        Route::get('/students/export', [UserManagementController::class, 'exportStudents'])->name('students.export');
        Route::get('/students/create', [UserManagementController::class, 'createStudent'])->name('students.create');
        Route::post('/students', [UserManagementController::class, 'storeStudent'])->name('students.store');
        
        // Student Import
        Route::get('/students/import', [AcademicManagementController::class, 'importStudentsShow'])->name('students.import.show');
        Route::post('/students/import', [AcademicManagementController::class, 'importStudentsStore'])->name('students.import.store');
        Route::get('/students/import/sample', [AcademicManagementController::class, 'downloadSample'])->name('students.import.sample');

        Route::get('/students/promote', [StudentPromotionController::class, 'show'])->name('students.promote');
        Route::post('/students/promote', [StudentPromotionController::class, 'store'])->name('students.promote.store');

        Route::get('/students/{id}', [UserManagementController::class, 'showStudent'])->name('students.show');
        Route::get('/students/{id}/edit', [UserManagementController::class, 'editStudent'])->name('students.edit');
        Route::put('/students/{id}', [UserManagementController::class, 'updateStudent'])->name('students.update');
        Route::delete('/students/{id}', [UserManagementController::class, 'destroyStudent'])->name('students.destroy');
        
        // Active Logins
        Route::get('/active-logins', [UserManagementController::class, 'activeLogins'])->name('active_logins');
        Route::delete('/active-logins/{id}', [UserManagementController::class, 'revokeLogin'])->name('active_logins.revoke');
    });

    // Location Management
    Route::get('/locations/search/autocomplete', [LocationSearchController::class, 'autocomplete'])->name('locations.search.autocomplete');
    Route::get('/locations/search/details', [LocationSearchController::class, 'details'])->name('locations.search.details');
    Route::resource('locations', LocationController::class);

    // Attendance Reports
    Route::prefix('attendance')->name('attendance.')->group(function () {
        Route::get('/students', [AttendanceReportController::class, 'studentAttendance'])->name('students');
        Route::get('/class-markings', [AttendanceReportController::class, 'classMarkings'])->name('class_markings');
        Route::get('/class-markings/students', [AttendanceReportController::class, 'classMarkingStudents'])->name('class_markings.students');
        Route::post('/class-markings/students', [AttendanceReportController::class, 'updateClassMarkingStudents'])->name('class_markings.students.update');
        Route::get('/staff-faculty', [AttendanceReportController::class, 'staffFacultyAttendance'])->name('staff_faculty');
        Route::get('/monthly', [AttendanceReportController::class, 'monthlyAttendance'])->name('monthly');
        Route::get('/monthly-export', [ApiLeaveManagementController::class, 'exportMonthlyAttendance'])->name('monthly_export');
    });

    // Holiday Management
    Route::resource('holidays', HolidayController::class);

    // Leave Requests
    Route::prefix('leaves')->name('leaves.')->group(function () {
        Route::get('/', [LeaveManagementController::class, 'index'])->name('index');
        Route::patch('/{leaveId}/status', [LeaveManagementController::class, 'updateStatus'])->name('status.update');
    });

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'create'])->name('notifications.create');
    Route::post('/notifications', [NotificationController::class, 'send'])->name('notifications.send');

    // Notices
    Route::resource('notices', NoticeController::class);

    // Activity Logs
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity_logs.index');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
