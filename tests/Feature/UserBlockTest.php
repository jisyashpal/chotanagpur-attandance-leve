<?php

use App\Models\AcademicSession;
use App\Models\Course;
use App\Models\Semester;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function blockedApiHeaders(): array
{
    return [
        'X-APP-KEY' => 'default-secret-key',
        'X-DEVICE-ID' => 'device-block-1',
        'X-DEVICE-NAME' => 'Test Phone',
        'X-PLATFORM' => 'android',
        'X-FCM-TOKEN' => 'fcm-block-1',
    ];
}

test('admin can block and unblock a student and existing tokens are revoked', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $student = User::factory()->create(['role' => 'student']);
    $student->createToken('device-block-1');

    $this->actingAs($admin)
        ->patch(route('admin.users.toggle_active', $student->id))
        ->assertRedirect()
        ->assertSessionHas('success');

    $student->refresh();
    expect($student->is_active)->toBeFalse();
    expect($student->tokens()->count())->toBe(0);

    $this->actingAs($admin)
        ->patch(route('admin.users.toggle_active', $student->id))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($student->refresh()->is_active)->toBeTrue();
});

test('admin cannot block another admin', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $other = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->patch(route('admin.users.toggle_active', $other->id))
        ->assertRedirect()
        ->assertSessionHas('error');

    expect($other->refresh()->is_active)->toBeTrue();
});

test('blocked faculty cannot log in on the web or api', function () {
    $faculty = User::factory()->create([
        'role' => 'faculty',
        'is_active' => false,
    ]);

    $this->post('/login', [
        'email' => $faculty->email,
        'password' => 'password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();

    $this->postJson('/api/login', [
        'email' => $faculty->email,
        'password' => 'password',
    ], blockedApiHeaders())
        ->assertStatus(403)
        ->assertJsonPath('message', 'Your account has been blocked.');
});

test('blocked student cannot log in with roll number', function () {
    $session = AcademicSession::create(['name' => '2026-27', 'session_year' => 2026, 'is_active' => true]);
    $course = Course::create(['name' => 'LLB', 'code' => 'LLB']);
    $semester = Semester::create([
        'name' => 'Sem 1',
        'semester_number' => 1,
        'course_id' => $course->id,
        'part' => 'Part I',
    ]);

    $student = User::factory()->create([
        'role' => 'student',
        'is_active' => false,
        'is_profile_complete' => true,
    ]);

    StudentProfile::create([
        'user_id' => $student->id,
        'session_id' => $session->id,
        'course_id' => $course->id,
        'semester_id' => $semester->id,
        'roll_no' => 'ROLL-BLOCK',
        'phone_no' => '9876543210',
        'dob' => '2005-01-01',
        'gender' => 'Male',
        'is_complete' => true,
    ]);

    $this->postJson('/api/student-login', [
        'roll_no' => 'ROLL-BLOCK',
        'phone_no' => '9876543210',
        'dob' => '2005-01-01',
    ], blockedApiHeaders())
        ->assertStatus(403)
        ->assertJsonPath('message', 'Your account has been blocked.');
});

test('an existing api token is rejected after the account is blocked', function () {
    $student = User::factory()->create(['role' => 'student']);
    $token = $student->createToken('device-block-1')->plainTextToken;

    $student->update(['is_active' => false]);

    $this->getJson('/api/user', array_merge(blockedApiHeaders(), [
        'Authorization' => 'Bearer '.$token,
    ]))->assertStatus(401)
        ->assertJsonPath('message', 'Your account has been blocked.');

    expect($student->tokens()->count())->toBe(0);
});

test('a blocked student with an open web session is logged out', function () {
    $student = User::factory()->create([
        'role' => 'student',
        'is_active' => false,
        'is_profile_complete' => true,
    ]);

    $this->actingAs($student)
        ->get(route('student.dashboard'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});
