<?php

use App\Models\AcademicSession;
use App\Models\ActivityLog;
use App\Models\Batch;
use App\Models\Course;
use App\Models\Section;
use App\Models\Semester;
use App\Models\StudentProfile;
use App\Models\User;
use App\Services\SemesterPromotionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeAcademicTree(): array
{
    $course = Course::create(['name' => 'LLB', 'code' => 'LLB3']);
    $batch = Batch::create([
        'course_id' => $course->id,
        'name' => '2024-27',
        'start_year' => 2024,
        'end_year' => 2027,
        'is_active' => true,
    ]);
    $session1 = AcademicSession::create([
        'batch_id' => $batch->id,
        'name' => '2024-25',
        'session_year' => 2024,
        'is_active' => true,
    ]);
    $session2 = AcademicSession::create([
        'batch_id' => $batch->id,
        'name' => '2025-26',
        'session_year' => 2025,
        'is_active' => false,
    ]);
    $sem1 = Semester::create(['course_id' => $course->id, 'semester_number' => 1, 'name' => 'Sem 1', 'part' => 'Part I']);
    $sem2 = Semester::create(['course_id' => $course->id, 'semester_number' => 2, 'name' => 'Sem 2', 'part' => 'Part I']);
    $sem3 = Semester::create(['course_id' => $course->id, 'semester_number' => 3, 'name' => 'Sem 3', 'part' => 'Part II']);
    $sec1 = Section::create(['semester_id' => $sem1->id, 'name' => 'A', 'code' => 'A']);
    $sec2 = Section::create(['semester_id' => $sem2->id, 'name' => 'A', 'code' => 'A']);
    $sec3 = Section::create(['semester_id' => $sem3->id, 'name' => 'A', 'code' => 'A']);

    return compact('course', 'batch', 'session1', 'session2', 'sem1', 'sem2', 'sem3', 'sec1', 'sec2', 'sec3');
}

test('api returns batches and nested sessions', function () {
    $tree = makeAcademicTree();
    $headers = ['X-APP-KEY' => 'default-secret-key', 'Accept' => 'application/json'];

    $this->withHeaders($headers)->getJson('/api/batches?course_id='.$tree['course']->id)
        ->assertOk()
        ->assertJsonPath('batches.0.id', $tree['batch']->id);

    $this->withHeaders($headers)->getJson('/api/courses/'.$tree['course']->id.'/batches')
        ->assertOk()
        ->assertJsonPath('batches.0.name', '2024-27');

    $this->withHeaders($headers)->getJson('/api/batches/'.$tree['batch']->id.'/sessions')
        ->assertOk()
        ->assertJsonCount(2, 'sessions');

    $this->withHeaders($headers)->getJson('/api/sessions?batch_id='.$tree['batch']->id)
        ->assertOk()
        ->assertJsonPath('sessions.0.batch_id', $tree['batch']->id);
});

test('mid year promote moves odd semester to next within same session', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $tree = makeAcademicTree();
    $student = User::factory()->create(['role' => 'student', 'is_profile_complete' => true]);

    $profile = StudentProfile::create([
        'user_id' => $student->id,
        'batch_id' => $tree['batch']->id,
        'session_id' => $tree['session1']->id,
        'course_id' => $tree['course']->id,
        'semester_id' => $tree['sem1']->id,
        'section_id' => $tree['sec1']->id,
        'roll_no' => 'R1',
        'is_complete' => true,
    ]);

    $this->actingAs($admin);
    $summary = app(SemesterPromotionService::class)->promoteMidYear([$student->id]);

    expect($summary['promoted'])->toBe(1);
    $profile->refresh();
    expect($profile->semester_id)->toBe($tree['sem2']->id)
        ->and($profile->session_id)->toBe($tree['session1']->id)
        ->and($profile->section_id)->toBe($tree['sec2']->id);

    expect(ActivityLog::where('action', 'promoted')->count())->toBeGreaterThan(0);
});

test('year end promote moves even semester to next session', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $tree = makeAcademicTree();
    $student = User::factory()->create(['role' => 'student', 'is_profile_complete' => true]);

    StudentProfile::create([
        'user_id' => $student->id,
        'batch_id' => $tree['batch']->id,
        'session_id' => $tree['session1']->id,
        'course_id' => $tree['course']->id,
        'semester_id' => $tree['sem2']->id,
        'section_id' => $tree['sec2']->id,
        'roll_no' => 'R2',
        'is_complete' => true,
    ]);

    $this->actingAs($admin);
    $summary = app(SemesterPromotionService::class)->promoteYearEnd([$student->id], $tree['session2']->id);

    expect($summary['promoted'])->toBe(1);
    $profile = StudentProfile::where('user_id', $student->id)->first();
    expect($profile->semester_id)->toBe($tree['sem3']->id)
        ->and($profile->session_id)->toBe($tree['session2']->id);
});

test('admin can open promote and activity log pages', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->get(route('admin.users.students.promote'))->assertOk();
    $this->actingAs($admin)->get(route('admin.activity_logs.index'))->assertOk();
    $this->actingAs($admin)->get(route('admin.academy.batches'))->assertOk();
});
