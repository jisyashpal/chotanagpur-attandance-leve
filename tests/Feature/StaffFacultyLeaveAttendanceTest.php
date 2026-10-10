<?php

use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('faculty and staff monthly attendance counts leave like a student and keeps check-in without checkout present', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $faculty = User::factory()->create(['name' => 'Leave Faculty', 'role' => 'faculty']);

    Attendance::create([
        'user_id' => $faculty->id,
        'date' => '2026-08-03',
        'check_in' => '09:10:00',
        'status' => 'present',
    ]);
    Attendance::create([
        'user_id' => $faculty->id,
        'date' => '2026-08-05',
        'check_in' => '09:00:00',
        'status' => 'present',
    ]);
    Attendance::create([
        'user_id' => $faculty->id,
        'date' => '2026-08-07',
        'check_in' => '09:40:00',
        'status' => 'late',
    ]);

    LeaveRequest::create([
        'user_id' => $faculty->id,
        'from_date' => '2026-08-02',
        'to_date' => '2026-08-04',
        'reason' => 'Approved leave',
        'status' => 'approved',
        'approved_by' => $admin->id,
        'approved_at' => now(),
    ]);
    LeaveRequest::create([
        'user_id' => $faculty->id,
        'from_date' => '2026-08-06',
        'to_date' => '2026-08-06',
        'reason' => 'Still pending',
        'status' => 'pending',
    ]);

    $response = $this->actingAs($admin)->get(route('admin.attendance.monthly', [
        'tab' => 'staff',
        'month' => '2026-08',
        'user_id' => $faculty->id,
    ]));

    $response->assertOk();
    $matrix = $response->viewData('staffAttendanceMatrix')[$faculty->id];
    $summary = $response->viewData('staffAttendanceSummary')[$faculty->id];

    expect($matrix[2]['status'])->toBe('sunday')
        ->and($matrix[3]['status'])->toBe('present')
        ->and($matrix[4]['status'])->toBe('leave')
        ->and($matrix[5]['status'])->toBe('present')
        ->and($matrix[6]['status'])->toBe('absent')
        ->and($matrix[7]['status'])->toBe('present')
        ->and($summary['present'])->toBe(3)
        ->and($summary['leave'])->toBe(1)
        ->and($summary)->not->toHaveKey('late');

    UserDevice::create([
        'user_id' => $faculty->id,
        'device_id' => 'device-faculty-self-leave',
        'fcm_token' => 'fcm-faculty-self-leave',
        'platform' => 'android',
    ]);

    Sanctum::actingAs($faculty);

    $this->getJson('/api/attendance/staff-attendance', [
        'X-APP-KEY' => 'default-secret-key',
        'X-DEVICE-ID' => 'device-faculty-self-leave',
    ])->assertOk()
        ->assertJsonPath('summary.present', 3)
        ->assertJsonPath('summary.absent', 0)
        ->assertJsonPath('summary.leave', 1)
        ->assertJsonPath('summary.percentage', 100)
        ->assertJsonFragment(['date' => '2026-08-07', 'status' => 'present', 'check_out' => null])
        ->assertJsonFragment(['date' => '2026-08-03', 'status' => 'present']);
});
