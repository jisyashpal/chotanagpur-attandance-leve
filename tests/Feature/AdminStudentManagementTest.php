<?php

use App\Models\User;
use App\Models\AcademicSession;
use App\Models\Course;
use App\Models\Semester;
use App\Models\StudentProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin can access student list page', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    
    $response = $this->actingAs($admin)->get(route('admin.users.students'));
    
    $response->assertStatus(200);
    $response->assertViewIs('admin.users.students');
});

test('non-admin cannot access student list page', function () {
    $student = User::factory()->create(['role' => 'student']);
    
    $response = $this->actingAs($student)->get(route('admin.users.students'));
    
    $response->assertStatus(403);
});

test('admin can view student details page', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $student = User::factory()->create(['role' => 'student', 'is_profile_complete' => true]);
    
    $session = AcademicSession::create(['name' => '2024-25', 'session_year' => 2024, 'is_active' => true]);
    $course = Course::create(['name' => 'LLB', 'code' => 'LLB']);
    $semester = Semester::create(['name' => 'Sem 1', 'semester_number' => 1, 'course_id' => $course->id, 'part' => 'Part I']);
    
    StudentProfile::create([
        'user_id' => $student->id,
        'session_id' => $session->id,
        'course_id' => $course->id,
        'semester_id' => $semester->id,
        'roll_no' => 'ROLL-101',
        'university_registration_number' => 'URN-101',
        'phone_no' => '9876543210',
        'dob' => '2005-01-01',
        'gender' => 'Male',
        'is_complete' => true,
    ]);

    $response = $this->actingAs($admin)->get(route('admin.users.students.show', $student->id));
    
    $response->assertStatus(200);
    $response->assertViewIs('admin.users.show_student');
    $response->assertSee('ROLL-101');
    $response->assertSee('URN-101');
});

test('admin can search students by name, email, roll number, and university registration number', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    
    $session = AcademicSession::create(['name' => '2024-25', 'session_year' => 2024, 'is_active' => true]);
    $course = Course::create(['name' => 'LLB', 'code' => 'LLB']);
    $semester = Semester::create(['name' => 'Sem 1', 'semester_number' => 1, 'course_id' => $course->id, 'part' => 'Part I']);
    
    $student1 = User::create([
        'name' => 'Alice Smith',
        'email' => 'alice@example.com',
        'password' => bcrypt('password'),
        'role' => 'student',
        'is_profile_complete' => true,
    ]);
    StudentProfile::create([
        'user_id' => $student1->id,
        'session_id' => $session->id,
        'course_id' => $course->id,
        'semester_id' => $semester->id,
        'roll_no' => 'ROLL-001',
        'university_registration_number' => 'URN-001',
        'is_complete' => true,
    ]);

    $student2 = User::create([
        'name' => 'Bob Jones',
        'email' => 'bob@example.com',
        'password' => bcrypt('password'),
        'role' => 'student',
        'is_profile_complete' => true,
    ]);
    StudentProfile::create([
        'user_id' => $student2->id,
        'session_id' => $session->id,
        'course_id' => $course->id,
        'semester_id' => $semester->id,
        'roll_no' => 'ROLL-002',
        'university_registration_number' => 'URN-002',
        'is_complete' => true,
    ]);

    // Search by name
    $response = $this->actingAs($admin)->get(route('admin.users.students', ['search' => 'Alice']));
    $response->assertStatus(200);
    $response->assertSee('Alice Smith');
    $response->assertDontSee('Bob Jones');

    // Search by email
    $response = $this->actingAs($admin)->get(route('admin.users.students', ['search' => 'bob@example.com']));
    $response->assertStatus(200);
    $response->assertSee('Bob Jones');
    $response->assertDontSee('Alice Smith');

    // Search by roll_no
    $response = $this->actingAs($admin)->get(route('admin.users.students', ['search' => 'ROLL-001']));
    $response->assertStatus(200);
    $response->assertSee('Alice Smith');
    $response->assertDontSee('Bob Jones');

    // Search by university_registration_number
    $response = $this->actingAs($admin)->get(route('admin.users.students', ['search' => 'URN-002']));
    $response->assertStatus(200);
    $response->assertSee('Bob Jones');
    $response->assertDontSee('Alice Smith');
});

test('admin can filter students by session, course, and semester', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    
    $sessionA = AcademicSession::create(['name' => '2024-25', 'session_year' => 2024, 'is_active' => true]);
    $sessionB = AcademicSession::create(['name' => '2025-26', 'session_year' => 2025, 'is_active' => false]);
    
    $courseA = Course::create(['name' => 'LLB', 'code' => 'LLB']);
    $courseB = Course::create(['name' => 'BA LLB', 'code' => 'BALLB']);
    
    $semesterA = Semester::create(['name' => 'Sem 1', 'semester_number' => 1, 'course_id' => $courseA->id, 'part' => 'Part I']);
    $semesterB = Semester::create(['name' => 'Sem 2', 'semester_number' => 2, 'course_id' => $courseB->id, 'part' => 'Part I']);
    
    $student1 = User::create([
        'name' => 'Student One',
        'email' => 'one@example.com',
        'password' => bcrypt('password'),
        'role' => 'student',
        'is_profile_complete' => true,
    ]);
    StudentProfile::create([
        'user_id' => $student1->id,
        'session_id' => $sessionA->id,
        'course_id' => $courseA->id,
        'semester_id' => $semesterA->id,
        'is_complete' => true,
    ]);

    $student2 = User::create([
        'name' => 'Student Two',
        'email' => 'two@example.com',
        'password' => bcrypt('password'),
        'role' => 'student',
        'is_profile_complete' => true,
    ]);
    StudentProfile::create([
        'user_id' => $student2->id,
        'session_id' => $sessionB->id,
        'course_id' => $courseB->id,
        'semester_id' => $semesterB->id,
        'is_complete' => true,
    ]);

    // Filter by sessionA
    $response = $this->actingAs($admin)->get(route('admin.users.students', ['session_id' => $sessionA->id]));
    $response->assertStatus(200);
    $response->assertSee('Student One');
    $response->assertDontSee('Student Two');

    // Filter by courseB
    $response = $this->actingAs($admin)->get(route('admin.users.students', ['course_id' => $courseB->id]));
    $response->assertStatus(200);
    $response->assertSee('Student Two');
    $response->assertDontSee('Student One');

    // Filter by semesterA
    $response = $this->actingAs($admin)->get(route('admin.users.students', ['semester_id' => $semesterA->id]));
    $response->assertStatus(200);
    $response->assertSee('Student One');
    $response->assertDontSee('Student Two');
});

test('admin can export filtered students as csv', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $session = AcademicSession::create(['name' => '2024-25', 'session_year' => 2024, 'is_active' => true]);
    $course = Course::create(['name' => 'LLB', 'code' => 'LLB']);
    $semester = Semester::create(['name' => 'Sem 1', 'semester_number' => 1, 'course_id' => $course->id, 'part' => 'Part I']);

    $student1 = User::create([
        'name' => 'Export Alice',
        'email' => 'export.alice@example.com',
        'password' => bcrypt('password'),
        'role' => 'student',
        'is_profile_complete' => true,
    ]);
    StudentProfile::create([
        'user_id' => $student1->id,
        'session_id' => $session->id,
        'course_id' => $course->id,
        'semester_id' => $semester->id,
        'roll_no' => 'ROLL-EXP-1',
        'university_registration_number' => 'URN-EXP-1',
        'is_complete' => true,
    ]);

    $student2 = User::create([
        'name' => 'Export Bob',
        'email' => 'export.bob@example.com',
        'password' => bcrypt('password'),
        'role' => 'student',
        'is_profile_complete' => true,
    ]);
    StudentProfile::create([
        'user_id' => $student2->id,
        'session_id' => $session->id,
        'course_id' => $course->id,
        'semester_id' => $semester->id,
        'roll_no' => 'ROLL-EXP-2',
        'university_registration_number' => 'URN-EXP-2',
        'is_complete' => true,
    ]);

    $response = $this->actingAs($admin)->get(route('admin.users.students.export', ['search' => 'Export Alice']));

    $response->assertStatus(200);
    $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    $content = $response->streamedContent();
    expect($content)->toContain('Export Alice');
    expect($content)->toContain('ROLL-EXP-1');
    expect($content)->not->toContain('Export Bob');
});

test('admin can access student import pages and routes resolve correctly', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    
    // Test import show page
    $response = $this->actingAs($admin)->get(route('admin.users.students.import.show'));
    $response->assertStatus(200);
    $response->assertViewIs('admin.academy.import_students');
    
    // Test download sample route
    $response = $this->actingAs($admin)->get(route('admin.users.students.import.sample'));
    $response->assertStatus(200);
    $response->assertHeader('Content-Disposition', 'attachment; filename=student_import_sample.csv');
});

test('admin can access staff-faculty list page', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    
    $response = $this->actingAs($admin)->get(route('admin.users.staff_faculty'));
    
    $response->assertStatus(200);
    $response->assertViewIs('admin.users.staff_faculty');
});

test('admin can search and filter staff-faculty members', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    
    $faculty = User::create([
        'name' => 'Faculty Member One',
        'email' => 'fac1@example.com',
        'password' => bcrypt('password'),
        'role' => 'faculty',
        'is_profile_complete' => true,
    ]);
    \App\Models\FacultyProfile::create([
        'user_id' => $faculty->id,
        'employee_id' => 'EMP-FAC-001',
        'designation' => 'Professor',
        'department' => 'Science',
        'phone_no' => '9876543210',
        'is_complete' => true,
    ]);

    $staff = User::create([
        'name' => 'Staff Member Two',
        'email' => 'staff2@example.com',
        'password' => bcrypt('password'),
        'role' => 'staff',
        'is_profile_complete' => true,
    ]);
    \App\Models\StaffProfile::create([
        'user_id' => $staff->id,
        'employee_id' => 'EMP-STAFF-002',
        'designation' => 'Clerk',
        'department' => 'AdminDept',
        'phone_no' => '9876543211',
        'is_complete' => true,
    ]);

    // Search by name
    $response = $this->actingAs($admin)->get(route('admin.users.staff_faculty', ['search' => 'Faculty']));
    $response->assertSee('Faculty Member One');
    $response->assertDontSee('Staff Member Two');

    // Search by email
    $response = $this->actingAs($admin)->get(route('admin.users.staff_faculty', ['search' => 'staff2@example.com']));
    $response->assertSee('Staff Member Two');
    $response->assertDontSee('Faculty Member One');

    // Search by employee_id
    $response = $this->actingAs($admin)->get(route('admin.users.staff_faculty', ['search' => 'EMP-FAC-001']));
    $response->assertSee('Faculty Member One');
    $response->assertDontSee('Staff Member Two');

    // Filter by role
    $response = $this->actingAs($admin)->get(route('admin.users.staff_faculty', ['role' => 'staff']));
    $response->assertSee('Staff Member Two');
    $response->assertDontSee('Faculty Member One');

    // Filter by department
    $response = $this->actingAs($admin)->get(route('admin.users.staff_faculty', ['department' => 'Science']));
    $response->assertSee('Faculty Member One');
    $response->assertDontSee('Staff Member Two');
});

test('admin cannot upload student import file larger than 10MB', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $course = Course::create(['name' => 'LLB', 'code' => 'LLB']);
    $batch = \App\Models\Batch::create([
        'course_id' => $course->id,
        'name' => '2024-27',
        'start_year' => 2024,
        'end_year' => 2027,
        'is_active' => true,
    ]);
    $session = AcademicSession::create([
        'batch_id' => $batch->id,
        'name' => '2024-25',
        'session_year' => 2024,
        'is_active' => true,
    ]);
    $semester = Semester::create(['name' => 'Sem 1', 'semester_number' => 1, 'course_id' => $course->id, 'part' => 'Part I']);
    $section = \App\Models\Section::create(['semester_id' => $semester->id, 'name' => 'A', 'code' => 'A']);

    // Create a fake file larger than 10MB (11MB)
    $file = \Illuminate\Http\UploadedFile::fake()->create('students.csv', 11 * 1024);

    $response = $this->actingAs($admin)->post(route('admin.users.students.import.store'), [
        'batch_id' => $batch->id,
        'session_id' => $session->id,
        'course_id' => $course->id,
        'semester_id' => $semester->id,
        'section_id' => $section->id,
        'file' => $file
    ]);

    $response->assertSessionHasErrors(['file']);
});

test('admin can upload student import file up to 10MB', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $course = Course::create(['name' => 'LLB', 'code' => 'LLB']);
    $batch = \App\Models\Batch::create([
        'course_id' => $course->id,
        'name' => '2024-27',
        'start_year' => 2024,
        'end_year' => 2027,
        'is_active' => true,
    ]);
    $session = AcademicSession::create([
        'batch_id' => $batch->id,
        'name' => '2024-25',
        'session_year' => 2024,
        'is_active' => true,
    ]);
    $semester = Semester::create(['name' => 'Sem 1', 'semester_number' => 1, 'course_id' => $course->id, 'part' => 'Part I']);
    $section = \App\Models\Section::create(['semester_id' => $semester->id, 'name' => 'A', 'code' => 'A']);

    // Create a fake valid CSV content file
    $csvContent = "name,email,roll_no,university_registration_number,phone_no,dob,gender\n";
    $csvContent .= "Test Student,test_student@example.com,105,EN105,9876543219,2005-01-01,Male\n";
    
    $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('students.csv', $csvContent);

    $response = $this->actingAs($admin)->post(route('admin.users.students.import.store'), [
        'batch_id' => $batch->id,
        'session_id' => $session->id,
        'course_id' => $course->id,
        'semester_id' => $semester->id,
        'section_id' => $section->id,
        'file' => $file
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertSessionHas('success');
});
