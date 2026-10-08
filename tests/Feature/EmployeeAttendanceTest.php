<?php

use App\Models\User;
use App\Models\FacultyProfile;
use App\Models\StaffProfile;
use App\Models\Attendance;
use App\Models\Location;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('faculty can access dashboard and view attendance history', function () {
    $user = User::create([
        'name' => 'Dr. John Doe',
        'email' => 'john.doe@faculty.com',
        'password' => Hash::make('password'),
        'role' => 'faculty',
        'is_profile_complete' => true
    ]);

    $profile = FacultyProfile::create([
        'user_id' => $user->id,
        'employee_id' => 'FAC101',
        'designation' => 'Professor',
        'department' => 'CS',
        'phone_no' => '9876543210',
        'dob' => '1980-01-01',
        'gender' => 'Male',
        'is_complete' => true
    ]);

    $location = Location::create([
        'name' => 'Main Campus',
        'latitude' => 23.344,
        'longitude' => 85.309,
        'radius' => 100
    ]);

    Attendance::create([
        'user_id' => $user->id,
        'location_id' => $location->id,
        'date' => '2026-06-25',
        'check_in' => '09:00:00',
        'check_out' => '17:00:00',
        'status' => 'present',
        'latitude' => 23.344,
        'longitude' => 85.309
    ]);

    $response = $this->actingAs($user)->get(route('faculty.dashboard'));
    $response->assertStatus(200);

    $response = $this->actingAs($user)->get(route('faculty.attendance'));
    $response->assertStatus(200);
    $response->assertViewIs('faculty.attendance');
    $response->assertViewHasAll([
        'attendances',
        'totalDays',
        'presentDays',
        'lateDays',
        'absentDays',
        'attendancePercentage'
    ]);
});

test('staff can access dashboard and view attendance history', function () {
    $user = User::create([
        'name' => 'Jane Smith',
        'email' => 'jane.smith@staff.com',
        'password' => Hash::make('password'),
        'role' => 'staff',
        'is_profile_complete' => true
    ]);

    $profile = StaffProfile::create([
        'user_id' => $user->id,
        'employee_id' => 'STAFF202',
        'designation' => 'Registrar',
        'department' => 'Administration',
        'phone_no' => '9876543220',
        'dob' => '1990-05-15',
        'gender' => 'Female',
        'is_complete' => true
    ]);

    $location = Location::create([
        'name' => 'Main Campus',
        'latitude' => 23.344,
        'longitude' => 85.309,
        'radius' => 100
    ]);

    Attendance::create([
        'user_id' => $user->id,
        'location_id' => $location->id,
        'date' => '2026-06-25',
        'check_in' => '09:15:00',
        'check_out' => '17:00:00',
        'status' => 'late',
        'latitude' => 23.344,
        'longitude' => 85.309
    ]);

    $response = $this->actingAs($user)->get(route('staff.dashboard'));
    $response->assertStatus(200);

    $response = $this->actingAs($user)->get(route('staff.attendance'));
    $response->assertStatus(200);
    $response->assertViewIs('staff.attendance');
    $response->assertViewHasAll([
        'attendances',
        'totalDays',
        'presentDays',
        'lateDays',
        'absentDays',
        'attendancePercentage'
    ]);
});
