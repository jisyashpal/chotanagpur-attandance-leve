<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\StudentProfile;
use App\Models\AcademicSession;
use App\Models\Batch;
use App\Models\Course;
use App\Models\Semester;
use App\Models\Section;
use App\Models\Location;
use Illuminate\Support\Facades\Hash;

class AttendanceDemoSeeder extends Seeder
{
    public function run(): void
    {
        Location::updateOrCreate(
            ['name' => 'Main Office / Campus'],
            [
                'latitude' => 23.3441,
                'longitude' => 85.3091,
                'radius' => 500,
                'is_active' => true,
            ]
        );

        $course = Course::where('code', 'LLB3')->first() ?? Course::first();
        if (! $course) {
            return;
        }

        $batch = Batch::where('course_id', $course->id)->where('name', '2024-2027')->first()
            ?? Batch::where('course_id', $course->id)->where('is_active', true)->first()
            ?? Batch::where('course_id', $course->id)->first();
        $session = $batch
            ? (AcademicSession::where('batch_id', $batch->id)->where('name', '2026-27')->first()
                ?? AcademicSession::where('batch_id', $batch->id)->where('is_active', true)->first()
                ?? AcademicSession::where('batch_id', $batch->id)->first())
            : null;
        $semester = Semester::where('course_id', $course->id)->orderBy('semester_number')->first();
        $section = $semester
            ? (Section::where('semester_id', $semester->id)->where('code', 'A')->first()
                ?? Section::where('semester_id', $semester->id)->first())
            : null;

        if ($session && $batch && $semester && $section) {
            for ($i = 1; $i <= 5; $i++) {
                $email = "student{$i}@demo.com";

                $user = User::updateOrCreate(
                    ['email' => $email],
                    [
                        'name' => "Demo Student {$i}",
                        'password' => Hash::make('password'),
                        'role' => 'student',
                        'is_profile_complete' => true,
                    ]
                );

                StudentProfile::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'batch_id' => $batch->id,
                        'session_id' => $session->id,
                        'course_id' => $course->id,
                        'semester_id' => $semester->id,
                        'section_id' => $section->id,
                        'roll_no' => "ROLL000{$i}",
                        'university_registration_number' => "URN100{$i}",
                        'phone_no' => "987654321{$i}",
                        'dob' => '2005-01-01',
                        'gender' => 'Male',
                        'is_complete' => true,
                    ]
                );
            }
        }
    }
}
