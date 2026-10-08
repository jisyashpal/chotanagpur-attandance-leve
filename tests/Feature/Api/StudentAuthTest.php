<?php

use App\Models\User;
use App\Models\StudentProfile;
use App\Models\AcademicSession;
use App\Models\Course;
use App\Models\Semester;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('student registration is blocked in API', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'Student User',
        'email' => 'student@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'student',
        'phone_no' => '9876543210',
        'dob' => '2005-01-01',
        'gender' => 'Male',
        'university_registration_number' => 'URN001',
        'roll_no' => 'RL001',
        'course_id' => 1,
        'semester_id' => 1,
        'session_id' => 1,
    ], [
        'X-APP-KEY' => 'default-secret-key',
        'X-DEVICE-ID' => 'device123',
        'X-FCM-TOKEN' => 'fcm123',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['role']);
});

test('student registration is blocked in web', function () {
    $response = $this->post('/register', [
        'name' => 'Student User',
        'email' => 'student@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'student',
    ]);

    $response->assertSessionHasErrors(['role']);
});

test('student can login in API using roll_no, phone_no and dob', function () {
    $session = AcademicSession::create(['name' => '2024-25', 'session_year' => 2024, 'is_active' => true]);
    $course = Course::create(['name' => 'LLB', 'code' => 'LLB']);
    $semester = Semester::create(['name' => 'Sem 1', 'semester_number' => 1, 'course_id' => $course->id, 'part' => 'Part I']);

    $user = User::create([
        'name' => 'Demo Student',
        'email' => 'student@demo.com',
        'password' => Hash::make('password'),
        'role' => 'student',
        'is_profile_complete' => true
    ]);

    $profile = StudentProfile::create([
        'user_id' => $user->id,
        'session_id' => $session->id,
        'course_id' => $course->id,
        'semester_id' => $semester->id,
        'roll_no' => 'ROLL-001',
        'phone_no' => '9876543211',
        'dob' => '2005-01-01',
        'gender' => 'Male',
        'is_complete' => true
    ]);

    $response = $this->postJson('/api/student-login', [
        'roll_no' => 'ROLL-001',
        'phone_no' => '9876543211',
        'dob' => '2005-01-01',
    ], [
        'X-APP-KEY' => 'default-secret-key',
        'X-DEVICE-ID' => 'device-test-123',
        'X-FCM-TOKEN' => 'fcm-test-123',
        'X-PLATFORM' => 'android'
    ]);

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'message',
        'access_token',
        'token_type',
        'user' => [
            'id',
            'name',
            'email',
            'role',
            'roll_no',
            'phone_no',
            'dob',
        ]
    ]);
});

test('student login fails with invalid credentials', function () {
    $response = $this->postJson('/api/student-login', [
        'roll_no' => 'NON-EXISTENT',
        'phone_no' => '0000000000',
        'dob' => '2000-01-01',
    ], [
        'X-APP-KEY' => 'default-secret-key',
        'X-DEVICE-ID' => 'device-test-123',
        'X-FCM-TOKEN' => 'fcm-test-123',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['roll_no']);
});

test('student is redirected to complete profile if incomplete', function () {
    $user = User::create([
        'name' => 'Demo Student',
        'email' => 'student@demo.com',
        'password' => Hash::make('password'),
        'role' => 'student',
        'is_profile_complete' => false
    ]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertRedirect(route('student.profile.complete.show'));
});

test('student can complete profile and view dashboard stats', function () {
    $session = AcademicSession::create(['name' => '2024-25', 'session_year' => 2024, 'is_active' => true]);
    $course = Course::create(['name' => 'LLB', 'code' => 'LLB']);
    $semester = Semester::create(['name' => 'Sem 1', 'semester_number' => 1, 'course_id' => $course->id, 'part' => 'Part I']);
    $section = \App\Models\Section::create(['semester_id' => $semester->id, 'name' => 'A', 'code' => 'A']);
    $subject = \App\Models\Subject::create(['name' => 'Jurisprudence', 'code' => 'LLB101', 'semester_id' => $semester->id]);

    $user = User::create([
        'name' => 'Demo Student',
        'email' => 'student@demo.com',
        'password' => Hash::make('password'),
        'role' => 'student',
        'is_profile_complete' => false
    ]);

    // Create incomplete profile as if imported
    $profile = StudentProfile::create([
        'user_id' => $user->id,
        'session_id' => $session->id,
        'course_id' => $course->id,
        'semester_id' => $semester->id,
        'roll_no' => 'ROLL-001',
        'phone_no' => '9876543211',
        'dob' => '2005-01-01',
        'gender' => 'Male',
        'is_complete' => false
    ]);

    // Submit profile completion form
    $response = $this->actingAs($user)->post('/student/profile/complete', [
        'phone_no' => '9876543211',
        'dob' => '2005-01-01',
        'gender' => 'male',
        'session_id' => $session->id,
        'course_id' => $course->id,
        'semester_id' => $semester->id,
        'section_id' => $section->id,
        'university_registration_number' => 'URN-1001',
    ]);

    $response->assertRedirect(route('dashboard'));
    $user->refresh();
    expect($user->is_profile_complete)->toBeTrue();

    // Create some attendance records
    \App\Models\StudentAttendance::create([
        'student_id' => $user->id,
        'subject_id' => $subject->id,
        'session_id' => $session->id,
        'course_id' => $course->id,
        'semester_id' => $semester->id,
        'date' => '2026-06-25',
        'status' => 'present',
        'marked_by' => $user->id
    ]);

    // Request student dashboard
    $dashboardResponse = $this->actingAs($user)->get(route('student.dashboard'));
    $dashboardResponse->assertStatus(200);
    $dashboardResponse->assertViewIs('student.dashboard');
    $dashboardResponse->assertViewHasAll([
        'totalClasses',
        'presentClasses',
        'absentClasses',
        'overallPercentage',
        'subjectSummary',
        'studentProfile'
    ]);
});

test('student can view attendance history page', function () {
    $session = AcademicSession::create(['name' => '2024-25', 'session_year' => 2024, 'is_active' => true]);
    $course = Course::create(['name' => 'LLB', 'code' => 'LLB']);
    $semester = Semester::create(['name' => 'Sem 1', 'semester_number' => 1, 'course_id' => $course->id, 'part' => 'Part I']);
    $subject = \App\Models\Subject::create(['name' => 'Jurisprudence', 'code' => 'LLB101', 'semester_id' => $semester->id]);

    $user = User::create([
        'name' => 'Demo Student',
        'email' => 'student@demo.com',
        'password' => Hash::make('password'),
        'role' => 'student',
        'is_profile_complete' => true
    ]);

    $profile = StudentProfile::create([
        'user_id' => $user->id,
        'session_id' => $session->id,
        'course_id' => $course->id,
        'semester_id' => $semester->id,
        'roll_no' => 'ROLL-001',
        'phone_no' => '9876543211',
        'dob' => '2005-01-01',
        'gender' => 'Male',
        'is_complete' => true
    ]);

    \App\Models\StudentAttendance::create([
        'student_id' => $user->id,
        'subject_id' => $subject->id,
        'session_id' => $session->id,
        'course_id' => $course->id,
        'semester_id' => $semester->id,
        'date' => '2026-06-25',
        'status' => 'present',
        'marked_by' => $user->id
    ]);

    $response = $this->actingAs($user)->get(route('student.attendance'));

    $response->assertStatus(200);
    $response->assertViewIs('student.attendance');
    $response->assertViewHasAll([
        'attendances',
        'totalClasses',
        'presentClasses',
        'absentClasses',
        'overallPercentage',
        'studentProfile'
    ]);
});

test('student with complete profile cannot update academic details', function () {
    $session = AcademicSession::create(['name' => '2024-25', 'session_year' => 2024, 'is_active' => true]);
    $course = Course::create(['name' => 'LLB', 'code' => 'LLB']);
    $semester = Semester::create(['name' => 'Sem 1', 'semester_number' => 1, 'course_id' => $course->id, 'part' => 'Part I']);

    $user = User::create([
        'name' => 'Demo Student',
        'email' => 'student@demo.com',
        'password' => Hash::make('password'),
        'role' => 'student',
        'is_profile_complete' => true
    ]);

    $profile = StudentProfile::create([
        'user_id' => $user->id,
        'session_id' => $session->id,
        'course_id' => $course->id,
        'semester_id' => $semester->id,
        'university_registration_number' => 'URN12345',
        'roll_no' => 'ROLL-001',
        'phone_no' => '9876543211',
        'dob' => '2005-01-01',
        'gender' => 'Male',
        'is_complete' => true
    ]);

    // Create another course/session/semester to try to update to them
    $newSession = AcademicSession::create(['name' => '2025-26', 'session_year' => 2025, 'is_active' => true]);
    $newCourse = Course::create(['name' => 'BA LLB', 'code' => 'BALLB']);
    $newSemester = Semester::create(['name' => 'Sem 2', 'semester_number' => 2, 'course_id' => $newCourse->id, 'part' => 'Part I']);

    $response = $this->actingAs($user)->post('/student/profile/complete', [
        'phone_no' => '9999999999', // Should be ignored (locked)
        'dob' => '2004-02-02',      // Should be ignored (locked)
        'gender' => 'female',       // Updated basic detail
        'session_id' => $newSession->id,   // Should be ignored
        'course_id' => $newCourse->id,     // Should be ignored
        'semester_id' => $newSemester->id, // Should be ignored
        'university_registration_number' => 'URN99999',       // Should be ignored
    ]);

    $response->assertRedirect(route('dashboard'));
    
    $profile->refresh();
    
    // Phone & DOB remain locked / unchanged
    expect($profile->phone_no)->toBe('9876543211');
    expect($profile->dob->format('Y-m-d'))->toBe('2005-01-01');
    expect($profile->gender)->toBe('female');

    // Check that academic details remain unchanged
    expect($profile->session_id)->toBe($session->id);
    expect($profile->course_id)->toBe($course->id);
    expect($profile->semester_id)->toBe($semester->id);
    expect($profile->university_registration_number)->toBe('URN12345');
});

test('faculty registration via API is successful', function () {
    $course = Course::create(['name' => 'LLB', 'code' => 'LLB']);
    $semester = Semester::create(['course_id' => $course->id, 'semester_number' => 1, 'name' => 'Semester 1', 'part' => 'Part I']);
    $subject = \App\Models\Subject::create(['semester_id' => $semester->id, 'name' => 'Jurisprudence', 'code' => 'LAW101']);

    $response = $this->postJson('/api/register', [
        'name' => 'Faculty User',
        'email' => 'faculty@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'faculty',
        'phone_no' => '9876543210',
        'dob' => '1985-05-15',
        'gender' => 'Male',
        'employee_id' => 'EMP001',
        'designation' => 'Assistant Professor',
        'department' => 'Law',
        'subject_ids' => [$subject->id],
    ], [
        'X-APP-KEY' => 'default-secret-key',
        'X-DEVICE-ID' => 'device123',
        'X-FCM-TOKEN' => 'fcm123',
    ]);

    $response->assertStatus(200);
    $response->assertJsonStructure(['access_token', 'user']);
    
    $this->assertDatabaseHas('users', ['email' => 'faculty@example.com', 'role' => 'faculty']);
    $this->assertDatabaseHas('faculty_profiles', ['employee_id' => 'EMP001']);
    
    $this->assertDatabaseHas('faculty_subject_allocations', [
        'subject_id' => $subject->id,
    ]);
});


