<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentProfileController;
use App\Http\Controllers\FacultyProfileController;
use App\Http\Controllers\StaffProfileController;
use App\Http\Controllers\FacultyController;
use App\Http\Controllers\StaffController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;


Route::get('/test-mail', function () {
    try {
        Mail::raw('This is a test email', function ($message) {
            $message->to('jisyashpal@gmail.com')
                    ->subject('Test Mail');
        });

        return response()->json(['status' => 'success', 'message' => 'Mail sent successfully.']);
    } catch (\Exception $e) {
        Log::error('Mail sending failed: ' . $e->getMessage());

        return response()->json([
            'status' => 'error',
            'message' => 'Mail sending failed.',
            'error' => $e->getMessage()
        ], 500);
    }
});

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified', 'profile.complete'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware('role:student')->prefix('student')->name('student.')->group(function () {
        Route::get('/dashboard', [StudentController::class, 'dashboard'])->name('dashboard');
        Route::get('/attendance', [StudentController::class, 'attendance'])->name('attendance');
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });

    Route::middleware('role:faculty')->prefix('faculty')->name('faculty.')->group(function () {
        Route::get('/dashboard', [FacultyController::class, 'dashboard'])->name('dashboard');
        Route::get('/attendance', [FacultyController::class, 'attendance'])->name('attendance');
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });

    Route::middleware('role:staff')->prefix('staff')->name('staff.')->group(function () {
        Route::get('/dashboard', [StaffController::class, 'dashboard'])->name('dashboard');
        Route::get('/attendance', [StaffController::class, 'attendance'])->name('attendance');
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    
    Route::middleware('role:student')->group(function () {
        Route::get('/student/profile/complete', [StudentProfileController::class, 'completeShow'])->name('student.profile.complete.show');
        Route::post('/student/profile/complete', [StudentProfileController::class, 'completeStore'])->name('student.profile.complete.store');
    });

    Route::middleware('role:faculty')->group(function () {
        Route::get('/faculty/profile/complete', [FacultyProfileController::class, 'completeShow'])->name('faculty.profile.complete.show');
        Route::post('/faculty/profile/complete', [FacultyProfileController::class, 'completeStore'])->name('faculty.profile.complete.store');
    });

    Route::middleware('role:staff')->group(function () {
        Route::get('/staff/profile/complete', [StaffProfileController::class, 'completeShow'])->name('staff.profile.complete.show');
        Route::post('/staff/profile/complete', [StaffProfileController::class, 'completeStore'])->name('staff.profile.complete.store');
    });
});

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
