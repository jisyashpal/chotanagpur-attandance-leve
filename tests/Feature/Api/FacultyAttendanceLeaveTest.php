<?php

use App\Models\AcademicSession;
use App\Models\Course;
use App\Models\LeaveRequest;
use App\Models\Section;
use App\Models\Semester;
use App\Models\StudentAttendance;
use App\Models\Subject;
use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('faculty class list and summary treat approved leave as leave', function () {
    $session = AcademicSession::create(['name' => '2026-27', 'session_year' => 2026, 'is_active' => true]);
    $course = Course::create(['name' => 'LLB', 'code' => 'LLB']);
    $semester = Semester::create([
        'name' => 'Semester 1',
        'course_id' => $course->id,
        'semester_number' => 1,
        'part' => 'Part 1',
    ]);
    $section = Section::create(['semester_id' => $semester->id, 'name' => 'A', 'code' => 'A']);
    $subject = Subject::create(['name' => 'Constitutional Law', 'code' => 'CL01', 'semester_id' => $semester->id]);

    $faculty = User::factory()->create(['role' => 'faculty']);
    $presentStudent = User::factory()->create(['name' => 'Present Student', 'role' => 'student']);
    $leaveStudent = User::factory()->create(['name' => 'Leave Student', 'role' => 'student']);
    $absentStudent = User::factory()->create(['name' => 'Absent Student', 'role' => 'student']);
    $unmarkedStudent = User::factory()->create(['name' => 'Unmarked Student', 'role' => 'student']);

    foreach ([$presentStudent, $leaveStudent, $absentStudent, $unmarkedStudent] as $index => $student) {
        $student->studentProfile()->create([
            'session_id' => $session->id,
            'course_id' => $course->id,
            'semester_id' => $semester->id,
            'section_id' => $section->id,
            'roll_no' => 'ROLL-'.$index,
            'is_complete' => true,
        ]);
    }

    UserDevice::create([
        'user_id' => $faculty->id,
        'device_id' => 'device-faculty-leave',
        'fcm_token' => 'fcm-faculty-leave',
        'platform' => 'android',
    ]);

    $base = [
        'session_id' => $session->id,
        'course_id' => $course->id,
        'semester_id' => $semester->id,
        'section_id' => $section->id,
        'subject_id' => $subject->id,
        'date' => '2026-09-04',
        'marked_by' => $faculty->id,
    ];

    StudentAttendance::create($base + ['student_id' => $presentStudent->id, 'status' => 'present']);
    StudentAttendance::create($base + ['student_id' => $leaveStudent->id, 'status' => 'absent']);
    StudentAttendance::create($base + ['student_id' => $absentStudent->id, 'status' => 'absent']);

    LeaveRequest::create([
        'user_id' => $leaveStudent->id,
        'from_date' => '2026-09-04',
        'to_date' => '2026-09-04',
        'reason' => 'Approved medical leave',
        'status' => 'approved',
        'approved_by' => $faculty->id,
        'approved_at' => now(),
    ]);

    LeaveRequest::create([
        'user_id' => $unmarkedStudent->id,
        'from_date' => '2026-09-04',
        'to_date' => '2026-09-04',
        'reason' => 'Approved unmarked leave',
        'status' => 'approved',
        'approved_by' => $faculty->id,
        'approved_at' => now(),
    ]);

    LeaveRequest::create([
        'user_id' => $absentStudent->id,
        'from_date' => '2026-09-04',
        'to_date' => '2026-09-04',
        'reason' => 'Still pending',
        'status' => 'pending',
    ]);

    Sanctum::actingAs($faculty);

    $headers = [
        'X-APP-KEY' => 'default-secret-key',
        'X-DEVICE-ID' => 'device-faculty-leave',
    ];

    $this->postJson('/api/attendance/faculty-attendance-summary/students', [
        'session_id' => $session->id,
        'course_id' => $course->id,
        'semester_id' => $semester->id,
        'section_id' => $section->id,
        'subject_id' => $subject->id,
        'date' => '2026-09-04',
    ], $headers)
        ->assertOk()
        ->assertJsonFragment(['name' => 'Present Student', 'status' => 'present'])
        ->assertJsonFragment(['name' => 'Leave Student', 'status' => 'leave'])
        ->assertJsonFragment(['name' => 'Absent Student', 'status' => 'absent'])
        ->assertJsonFragment(['name' => 'Unmarked Student', 'status' => 'leave']);

    $this->getJson('/api/attendance/faculty-attendance-summary?date=2026-09-04', $headers)
        ->assertOk()
        ->assertJsonPath('data.0.present_count', 1)
        ->assertJsonPath('data.0.absent_count', 1)
        ->assertJsonPath('data.0.leave_count', 2);
});
