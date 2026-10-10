<?php

use App\Models\AcademicSession;
use App\Models\Course;
use App\Models\LeaveRequest;
use App\Models\Semester;
use App\Models\StudentAttendance;
use App\Models\StudentProfile;
use App\Models\Subject;
use App\Models\User;
use App\Models\UserDevice;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('student overall attendance percentage matches the monthly report formula', function () {
    Carbon::setTestNow('2026-09-15 10:00:00');

    $session = AcademicSession::create(['name' => '2026-27', 'session_year' => 2026, 'is_active' => true]);
    $course = Course::create(['name' => 'LLB', 'code' => 'LLB']);
    $semester = Semester::create([
        'name' => 'Semester 1',
        'course_id' => $course->id,
        'semester_number' => 1,
        'part' => 'Part 1',
    ]);
    $subjectA = Subject::create(['name' => 'Constitutional Law', 'code' => 'CL01', 'semester_id' => $semester->id]);
    $subjectB = Subject::create(['name' => 'Contract Law', 'code' => 'CT01', 'semester_id' => $semester->id]);

    $faculty = User::factory()->create(['role' => 'faculty']);
    $student = User::factory()->create(['role' => 'student']);
    $otherStudent = User::factory()->create(['role' => 'student']);

    UserDevice::create([
        'user_id' => $student->id,
        'device_id' => 'device-overall-1',
        'fcm_token' => 'fcm-overall-1',
        'platform' => 'android',
    ]);

    $base = [
        'session_id' => $session->id,
        'course_id' => $course->id,
        'semester_id' => $semester->id,
        'marked_by' => $faculty->id,
    ];

    StudentAttendance::create($base + ['student_id' => $student->id, 'subject_id' => $subjectA->id, 'date' => '2026-09-01', 'status' => 'present']);
    StudentAttendance::create($base + ['student_id' => $student->id, 'subject_id' => $subjectA->id, 'date' => '2026-09-02', 'status' => 'present']);
    StudentAttendance::create($base + ['student_id' => $student->id, 'subject_id' => $subjectB->id, 'date' => '2026-09-03', 'status' => 'present']);
    StudentAttendance::create($base + ['student_id' => $student->id, 'subject_id' => $subjectB->id, 'date' => '2026-09-04', 'status' => 'absent']);
    StudentAttendance::create($base + ['student_id' => $student->id, 'subject_id' => $subjectA->id, 'date' => '2026-08-20', 'status' => 'present']);
    StudentAttendance::create($base + ['student_id' => $otherStudent->id, 'subject_id' => $subjectA->id, 'date' => '2026-09-01', 'status' => 'present']);

    Sanctum::actingAs($student);

    $headers = [
        'X-APP-KEY' => 'default-secret-key',
        'X-DEVICE-ID' => 'device-overall-1',
    ];

    $this->getJson('/api/attendance/student/overall', $headers)
        ->assertOk()
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('data.month', '2026-09')
        ->assertJsonPath('data.total_classes', 4)
        ->assertJsonPath('data.attended', 3)
        ->assertJsonPath('data.absent', 1)
        ->assertJsonPath('data.percentage', 75);

    $this->getJson('/api/attendance/student/overall?month=2026-08', $headers)
        ->assertOk()
        ->assertJsonPath('data.month', '2026-08')
        ->assertJsonPath('data.total_classes', 1)
        ->assertJsonPath('data.attended', 1)
        ->assertJsonPath('data.percentage', 100);

    $this->getJson('/api/attendance/student', $headers)
        ->assertOk()
        ->assertJsonPath('status', 'success')
        ->assertJsonFragment(['subject_name' => 'Constitutional Law', 'percentage' => 100])
        ->assertJsonFragment(['subject_name' => 'Contract Law', 'percentage' => 50])
        ->assertJsonMissingPath('data.percentage');
});

test('student attendance can be filtered by month or semester', function () {
    $session = AcademicSession::create(['name' => '2026-27', 'session_year' => 2026, 'is_active' => true]);
    $course = Course::create(['name' => 'LLB', 'code' => 'LLB']);
    $semesterOne = Semester::create([
        'name' => 'Semester 1',
        'course_id' => $course->id,
        'semester_number' => 1,
        'part' => 'Part 1',
    ]);
    $semesterTwo = Semester::create([
        'name' => 'Semester 2',
        'course_id' => $course->id,
        'semester_number' => 2,
        'part' => 'Part 1',
    ]);
    $subject = Subject::create(['name' => 'Constitutional Law', 'code' => 'CL01', 'semester_id' => $semesterOne->id]);
    $subjectTwo = Subject::create(['name' => 'Family Law', 'code' => 'FL01', 'semester_id' => $semesterTwo->id]);

    $faculty = User::factory()->create(['role' => 'faculty']);
    $student = User::factory()->create(['role' => 'student']);

    StudentProfile::create([
        'user_id' => $student->id,
        'session_id' => $session->id,
        'course_id' => $course->id,
        'semester_id' => $semesterOne->id,
        'roll_no' => 'ROLL-010',
        'is_complete' => true,
    ]);

    UserDevice::create([
        'user_id' => $student->id,
        'device_id' => 'device-filter-1',
        'fcm_token' => 'fcm-filter-1',
        'platform' => 'android',
    ]);

    $base = [
        'student_id' => $student->id,
        'session_id' => $session->id,
        'course_id' => $course->id,
        'marked_by' => $faculty->id,
    ];

    StudentAttendance::create($base + ['subject_id' => $subject->id, 'semester_id' => $semesterOne->id, 'date' => '2026-09-01', 'status' => 'present']);
    StudentAttendance::create($base + ['subject_id' => $subject->id, 'semester_id' => $semesterOne->id, 'date' => '2026-08-01', 'status' => 'absent']);
    StudentAttendance::create($base + ['subject_id' => $subjectTwo->id, 'semester_id' => $semesterTwo->id, 'date' => '2026-09-10', 'status' => 'present']);

    Sanctum::actingAs($student);

    $headers = [
        'X-APP-KEY' => 'default-secret-key',
        'X-DEVICE-ID' => 'device-filter-1',
    ];

    $this->getJson('/api/attendance/student/overall?filter=semester', $headers)
        ->assertOk()
        ->assertJsonPath('data.filter', 'semester')
        ->assertJsonPath('data.semester_id', $semesterOne->id)
        ->assertJsonPath('data.semester_name', 'Semester 1')
        ->assertJsonPath('data.month', null)
        ->assertJsonPath('data.total_classes', 2)
        ->assertJsonPath('data.attended', 1)
        ->assertJsonPath('data.absent', 1)
        ->assertJsonPath('data.percentage', 50);

    $this->getJson('/api/attendance/student/overall?filter=semester&semester_id='.$semesterTwo->id, $headers)
        ->assertOk()
        ->assertJsonPath('data.semester_name', 'Semester 2')
        ->assertJsonPath('data.total_classes', 1)
        ->assertJsonPath('data.attended', 1)
        ->assertJsonPath('data.percentage', 100);

    $this->getJson('/api/attendance/student?filter=month&month=2026-09', $headers)
        ->assertOk()
        ->assertJsonPath('filter', 'month')
        ->assertJsonPath('month', '2026-09')
        ->assertJsonFragment(['subject_name' => 'Constitutional Law', 'total_classes' => 1, 'attended_classes' => 1])
        ->assertJsonFragment(['subject_name' => 'Family Law', 'total_classes' => 1, 'attended_classes' => 1]);

    $this->getJson('/api/attendance/student?filter=semester&semester_id='.$semesterOne->id, $headers)
        ->assertOk()
        ->assertJsonPath('filter', 'semester')
        ->assertJsonPath('semester_name', 'Semester 1')
        ->assertJsonFragment(['subject_name' => 'Constitutional Law', 'total_classes' => 2])
        ->assertJsonMissing(['subject_name' => 'Family Law']);
});

test('approved leave removes an absent class from the attendance percentage', function () {
    Carbon::setTestNow('2026-09-15 10:00:00');

    $session = AcademicSession::create(['name' => '2026-27', 'session_year' => 2026, 'is_active' => true]);
    $course = Course::create(['name' => 'LLB', 'code' => 'LLB']);
    $semester = Semester::create([
        'name' => 'Semester 1',
        'course_id' => $course->id,
        'semester_number' => 1,
        'part' => 'Part 1',
    ]);
    $subject = Subject::create(['name' => 'Constitutional Law', 'code' => 'CL01', 'semester_id' => $semester->id]);
    $faculty = User::factory()->create(['role' => 'faculty']);
    $student = User::factory()->create(['role' => 'student']);

    UserDevice::create([
        'user_id' => $student->id,
        'device_id' => 'device-leave-1',
        'fcm_token' => 'fcm-leave-1',
        'platform' => 'android',
    ]);

    $base = [
        'session_id' => $session->id,
        'course_id' => $course->id,
        'semester_id' => $semester->id,
        'subject_id' => $subject->id,
        'student_id' => $student->id,
        'marked_by' => $faculty->id,
    ];

    StudentAttendance::create($base + ['date' => '2026-09-01', 'status' => 'present']);
    StudentAttendance::create($base + ['date' => '2026-09-04', 'status' => 'absent']);
    StudentAttendance::create($base + ['date' => '2026-09-05', 'status' => 'absent']);

    LeaveRequest::create([
        'user_id' => $student->id,
        'from_date' => '2026-09-04',
        'to_date' => '2026-09-04',
        'reason' => 'Medical',
        'status' => 'approved',
        'approved_by' => $faculty->id,
        'approved_at' => now(),
    ]);

    LeaveRequest::create([
        'user_id' => $student->id,
        'from_date' => '2026-09-05',
        'to_date' => '2026-09-05',
        'reason' => 'Still waiting',
        'status' => 'pending',
    ]);

    Sanctum::actingAs($student);

    $headers = [
        'X-APP-KEY' => 'default-secret-key',
        'X-DEVICE-ID' => 'device-leave-1',
    ];

    $this->getJson('/api/attendance/student/overall', $headers)
        ->assertOk()
        ->assertJsonPath('data.total_classes', 2)
        ->assertJsonPath('data.attended', 1)
        ->assertJsonPath('data.absent', 1)
        ->assertJsonPath('data.leave', 1)
        ->assertJsonPath('data.percentage', 50);

    $this->getJson('/api/attendance/student', $headers)
        ->assertOk()
        ->assertJsonFragment([
            'subject_name' => 'Constitutional Law',
            'total_classes' => 2,
            'attended_classes' => 1,
            'leave_classes' => 1,
            'percentage' => 50,
        ])
        ->assertJsonFragment(['date' => '2026-09-04', 'status' => 'leave'])
        ->assertJsonFragment(['date' => '2026-09-05', 'status' => 'absent']);
});
