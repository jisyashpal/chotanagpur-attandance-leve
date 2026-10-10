<?php

use App\Models\User;
use App\Models\Holiday;
use App\Models\AcademicSession;
use App\Models\Attendance;
use App\Models\Course;
use App\Models\Semester;
use App\Models\Subject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

uses(RefreshDatabase::class);

test('admin can CRUD holidays via calendar forms', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    
    // 1. Read Calendar
    $response = $this->actingAs($admin)->get(route('admin.holidays.index'));
    $response->assertStatus(200);
    $response->assertSee('Holiday Management');

    // 2. Store Holiday
    $response = $this->actingAs($admin)->post(route('admin.holidays.store'), [
        'name' => 'Independence Day Holiday',
        'date' => '2026-08-15',
        'description' => 'National Holiday marking independence.'
    ]);
    $response->assertRedirect();
    $this->assertNotNull(Holiday::where('name', 'Independence Day Holiday')->whereDate('date', '2026-08-15')->first());

    $holiday = Holiday::first();

    // 3. Update Holiday
    $response = $this->actingAs($admin)->put(route('admin.holidays.update', $holiday->id), [
        'name' => 'Independence Day Holiday Updated',
        'date' => '2026-08-15',
        'description' => 'Updated description.'
    ]);
    $response->assertRedirect();
    $this->assertNotNull(Holiday::where('id', $holiday->id)
        ->where('name', 'Independence Day Holiday Updated')
        ->where('description', 'Updated description.')
        ->whereDate('date', '2026-08-15')
        ->first());

    // 4. Delete Holiday
    $response = $this->actingAs($admin)->delete(route('admin.holidays.destroy', $holiday->id));
    $response->assertRedirect();
    $this->assertDatabaseMissing('holidays', [
        'id' => $holiday->id
    ]);
});

test('admin can access monthly attendance report for students and faculty/staff', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    
    // Create academic records
    $session = AcademicSession::create(['name' => '2026-27', 'session_year' => 2026, 'is_active' => true]);
    $course = Course::create(['name' => 'Law Course', 'code' => 'LC01']);
    $semester = Semester::create([
        'name' => 'Semester 1',
        'course_id' => $course->id,
        'semester_number' => 1,
        'part' => 'Part 1'
    ]);
    $subject = Subject::create(['name' => 'Constitutional Law', 'code' => 'CL01', 'semester_id' => $semester->id]);

    // Create student
    $student = User::factory()->create(['role' => 'student']);
    $student->studentProfile()->create([
        'session_id' => $session->id,
        'course_id' => $course->id,
        'semester_id' => $semester->id,
        'roll_no' => 'ROLL-001',
        'university_registration_number' => 'URN-001',
        'is_complete' => true
    ]);

    // Create a staff user
    $staff = User::factory()->create(['role' => 'staff']);

    // 1. Visit monthly page with no filters
    $response = $this->actingAs($admin)->get(route('admin.attendance.monthly'));
    $response->assertStatus(200);
    $response->assertSee('Please select a Course and Semester');

    // 2. Filter students
    $response = $this->actingAs($admin)->get(route('admin.attendance.monthly', [
        'tab' => 'students',
        'course_id' => $course->id,
        'semester_id' => $semester->id,
        'session_id' => $session->id
    ]));
    $response->assertStatus(200);
    $response->assertSee($student->name);
    $response->assertSee('Overall Monthly Attendance Summary');

    // 3. Filter students by subject to load matrix
    $response = $this->actingAs($admin)->get(route('admin.attendance.monthly', [
        'tab' => 'students',
        'course_id' => $course->id,
        'semester_id' => $semester->id,
        'session_id' => $session->id,
        'subject_id' => $subject->id
    ]));
    $response->assertStatus(200);
    $response->assertSee('Student Attendance Matrix');
    $response->assertSee($student->name);

    // 4. View faculty & staff tab
    $response = $this->actingAs($admin)->get(route('admin.attendance.monthly', [
        'tab' => 'staff'
    ]));
    $response->assertStatus(200);
    $response->assertSee('Faculty & Staff Daily Attendance Matrix', false);
    $response->assertSee($staff->name);
});

test('staff faculty attendance export contains staff and faculty but not students', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $faculty = User::factory()->create([
        'name' => 'Export Faculty User',
        'email' => 'export.faculty@example.com',
        'role' => 'faculty',
    ]);
    $faculty->facultyProfile()->create([
        'employee_id' => 'FAC-EXP-001',
        'designation' => 'Professor',
        'department' => 'Law',
        'phone_no' => '9876543210',
        'is_complete' => true,
    ]);

    $staff = User::factory()->create([
        'name' => 'Export Staff User',
        'email' => 'export.staff@example.com',
        'role' => 'staff',
    ]);
    $staff->staffProfile()->create([
        'employee_id' => 'STF-EXP-001',
        'designation' => 'Registrar',
        'department' => 'Admin',
        'phone_no' => '9876543211',
        'is_complete' => true,
    ]);

    $student = User::factory()->create([
        'name' => 'Export Student User',
        'email' => 'export.student@example.com',
        'role' => 'student',
    ]);

    Attendance::create([
        'user_id' => $faculty->id,
        'date' => '2026-08-03',
        'check_in' => '09:00:00',
        'status' => 'present',
    ]);
    Attendance::create([
        'user_id' => $staff->id,
        'date' => '2026-08-03',
        'check_in' => '09:20:00',
        'status' => 'late',
    ]);

    $response = $this->actingAs($admin)->get(route('admin.attendance.monthly_export', [
        'month' => 8,
        'year' => 2026,
        'format' => 'csv',
        'scope' => 'staff_faculty',
    ]));

    $response->assertOk();
    $csv = $response->streamedContent();

    expect($csv)->toContain('Export Faculty User')
        ->toContain('Export Staff User')
        ->toContain('faculty')
        ->toContain('staff')
        ->toContain('FAC-EXP-001')
        ->toContain('STF-EXP-001')
        ->not->toContain('Export Student User')
        ->not->toContain('export.student@example.com')
        ->not->toContain('late_days')
        ->toContain('approved_leave_days');
});

test('student attendance export contains students but not staff or faculty', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $session = AcademicSession::create(['name' => '2026-27', 'session_year' => 2026, 'is_active' => true]);
    $course = Course::create(['name' => 'LLB', 'code' => 'LLB']);
    $semester = Semester::create([
        'name' => 'Semester 1',
        'course_id' => $course->id,
        'semester_number' => 1,
        'part' => 'Part 1',
    ]);

    $student = User::factory()->create([
        'name' => 'Export Student User',
        'email' => 'export.student@example.com',
        'role' => 'student',
    ]);
    $student->studentProfile()->create([
        'session_id' => $session->id,
        'course_id' => $course->id,
        'semester_id' => $semester->id,
        'roll_no' => 'ROLL-EXP-001',
        'university_registration_number' => 'URN-EXP-001',
        'is_complete' => true,
    ]);

    $faculty = User::factory()->create([
        'name' => 'Export Faculty User',
        'email' => 'export.faculty@example.com',
        'role' => 'faculty',
    ]);

    $response = $this->actingAs($admin)->get(route('admin.attendance.monthly_export', [
        'month' => 8,
        'year' => 2026,
        'format' => 'csv',
        'scope' => 'students',
        'course_id' => $course->id,
        'semester_id' => $semester->id,
    ]));

    $response->assertOk();
    $csv = $response->streamedContent();

    expect($csv)->toContain('Export Student User')
        ->toContain('ROLL-EXP-001')
        ->toContain('URN-EXP-001')
        ->toContain('LLB')
        ->not->toContain('Export Faculty User')
        ->not->toContain('export.faculty@example.com');
});


