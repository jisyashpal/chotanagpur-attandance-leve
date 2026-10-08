<?php

use App\Models\User;
use App\Models\AcademicSession;
use App\Models\Course;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\FacultySubjectAllocation;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin can CRUD academic sessions', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    
    // Create
    $response = $this->actingAs($admin)->post(route('admin.academy.sessions.store'), [
        'name' => '2024-25',
        'session_year' => 2024,
        'is_active' => 1
    ]);
    $response->assertSessionHasNoErrors();
    $this->assertDatabaseHas('academic_sessions', ['name' => '2024-25', 'session_year' => 2024, 'is_active' => true]);
    
    $session = AcademicSession::first();

    // Update
    $response = $this->actingAs($admin)->put(route('admin.academy.sessions.update', $session->id), [
        'name' => '2024-25-Updated',
        'session_year' => 2025,
        'is_active' => 0
    ]);
    $response->assertSessionHasNoErrors();
    $this->assertDatabaseHas('academic_sessions', ['id' => $session->id, 'name' => '2024-25-Updated', 'is_active' => false]);

    // Delete
    $response = $this->actingAs($admin)->delete(route('admin.academy.sessions.destroy', $session->id));
    $response->assertSessionHasNoErrors();
    $this->assertDatabaseMissing('academic_sessions', ['id' => $session->id]);
});

test('admin can CRUD courses', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    
    // Create
    $response = $this->actingAs($admin)->post(route('admin.academy.courses.store'), [
        'name' => 'LLB 3 Years',
        'code' => 'LLB3'
    ]);
    $response->assertSessionHasNoErrors();
    $this->assertDatabaseHas('courses', ['name' => 'LLB 3 Years', 'code' => 'LLB3']);
    
    $course = Course::first();

    // Update
    $response = $this->actingAs($admin)->put(route('admin.academy.courses.update', $course->id), [
        'name' => 'LLB 3 Years - Updated',
        'code' => 'LLB3U'
    ]);
    $response->assertSessionHasNoErrors();
    $this->assertDatabaseHas('courses', ['id' => $course->id, 'name' => 'LLB 3 Years - Updated', 'code' => 'LLB3U']);

    // Delete
    $response = $this->actingAs($admin)->delete(route('admin.academy.courses.destroy', $course->id));
    $response->assertSessionHasNoErrors();
    $this->assertDatabaseMissing('courses', ['id' => $course->id]);
});

test('admin can CRUD semesters', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $course = Course::create(['name' => 'LLB', 'code' => 'LLB']);
    
    // Create
    $response = $this->actingAs($admin)->post(route('admin.academy.semesters.store'), [
        'course_id' => $course->id,
        'semester_number' => 1,
        'name' => 'Semester 1',
        'part' => 'Part I'
    ]);
    $response->assertSessionHasNoErrors();
    $this->assertDatabaseHas('semesters', ['course_id' => $course->id, 'semester_number' => 1, 'name' => 'Semester 1']);
    
    $semester = Semester::first();

    // Update
    $response = $this->actingAs($admin)->put(route('admin.academy.semesters.update', $semester->id), [
        'course_id' => $course->id,
        'semester_number' => 2,
        'name' => 'Semester 2',
        'part' => 'Part I'
    ]);
    $response->assertSessionHasNoErrors();
    $this->assertDatabaseHas('semesters', ['id' => $semester->id, 'semester_number' => 2, 'name' => 'Semester 2']);

    // Delete
    $response = $this->actingAs($admin)->delete(route('admin.academy.semesters.destroy', $semester->id));
    $response->assertSessionHasNoErrors();
    $this->assertDatabaseMissing('semesters', ['id' => $semester->id]);
});

test('admin can CRUD subjects', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $course = Course::create(['name' => 'LLB', 'code' => 'LLB']);
    $semester = Semester::create(['course_id' => $course->id, 'semester_number' => 1, 'name' => 'Semester 1', 'part' => 'Part I']);
    
    // Create
    $response = $this->actingAs($admin)->post(route('admin.academy.subjects.store'), [
        'semester_id' => $semester->id,
        'name' => 'Jurisprudence',
        'code' => 'LAW101'
    ]);
    $response->assertSessionHasNoErrors();
    $this->assertDatabaseHas('subjects', ['semester_id' => $semester->id, 'name' => 'Jurisprudence', 'code' => 'LAW101']);
    
    $subject = Subject::first();

    // Update
    $response = $this->actingAs($admin)->put(route('admin.academy.subjects.update', $subject->id), [
        'semester_id' => $semester->id,
        'name' => 'Jurisprudence - Updated',
        'code' => 'LAW101U'
    ]);
    $response->assertSessionHasNoErrors();
    $this->assertDatabaseHas('subjects', ['id' => $subject->id, 'name' => 'Jurisprudence - Updated', 'code' => 'LAW101U']);

    // Delete
    $response = $this->actingAs($admin)->delete(route('admin.academy.subjects.destroy', $subject->id));
    $response->assertSessionHasNoErrors();
    $this->assertDatabaseMissing('subjects', ['id' => $subject->id]);
});

test('admin can CRUD allocations', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $faculty = User::factory()->create(['role' => 'faculty']);
    $course = Course::create(['name' => 'LLB', 'code' => 'LLB']);
    $semester = Semester::create(['course_id' => $course->id, 'semester_number' => 1, 'name' => 'Semester 1', 'part' => 'Part I']);
    $subject = Subject::create(['semester_id' => $semester->id, 'name' => 'Jurisprudence', 'code' => 'LAW101']);

    // Create Allocation
    $response = $this->actingAs($admin)->post(route('admin.academy.allocations.store'), [
        'faculty_id' => $faculty->id,
        'subject_ids' => [$subject->id]
    ]);
    $response->assertSessionHasNoErrors();
    $this->assertDatabaseHas('faculty_subject_allocations', [
        'faculty_id' => $faculty->id,
        'subject_id' => $subject->id
    ]);
    
    $allocation = FacultySubjectAllocation::first();

    // Delete Allocation
    $this->actingAs($admin)->delete(route('admin.academy.allocations.destroy', $allocation->id))->assertSessionHasNoErrors();
    $this->assertDatabaseMissing('faculty_subject_allocations', ['id' => $allocation->id]);
});

test('admin can search and filter allocations', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $faculty1 = User::factory()->create(['name' => 'Professor Green', 'role' => 'faculty']);
    $faculty2 = User::factory()->create(['name' => 'Professor Blue', 'role' => 'faculty']);
    
    $course1 = Course::create(['name' => 'Law Course', 'code' => 'LAW']);
    $course2 = Course::create(['name' => 'Arts Course', 'code' => 'ARTS']);
    
    $semester1 = Semester::create(['course_id' => $course1->id, 'semester_number' => 1, 'name' => 'Semester 1', 'part' => 'Part I']);
    $semester2 = Semester::create(['course_id' => $course2->id, 'semester_number' => 1, 'name' => 'Semester 1', 'part' => 'Part I']);
    
    $subject1 = Subject::create(['semester_id' => $semester1->id, 'name' => 'Jurisprudence', 'code' => 'LAW101']);
    $subject2 = Subject::create(['semester_id' => $semester2->id, 'name' => 'History', 'code' => 'ART101']);

    // Create Allocations
    $alloc1 = FacultySubjectAllocation::create([
        'faculty_id' => $faculty1->id,
        'subject_id' => $subject1->id,
        'semester_id' => $semester1->id
    ]);
    
    $alloc2 = FacultySubjectAllocation::create([
        'faculty_id' => $faculty2->id,
        'subject_id' => $subject2->id,
        'semester_id' => $semester2->id
    ]);

    // Test Search by Faculty Name
    $response = $this->actingAs($admin)->get(route('admin.academy.allocations', ['search' => 'Green']));
    $response->assertStatus(200);
    $allocations = $response->viewData('allocations');
    $this->assertTrue($allocations->contains('id', $alloc1->id));
    $this->assertFalse($allocations->contains('id', $alloc2->id));

    // Test Search by Subject Code
    $response = $this->actingAs($admin)->get(route('admin.academy.allocations', ['search' => 'ART101']));
    $response->assertStatus(200);
    $allocations = $response->viewData('allocations');
    $this->assertTrue($allocations->contains('id', $alloc2->id));
    $this->assertFalse($allocations->contains('id', $alloc1->id));

    // Test Filter by Course
    $response = $this->actingAs($admin)->get(route('admin.academy.allocations', ['course_id' => $course1->id]));
    $response->assertStatus(200);
    $allocations = $response->viewData('allocations');
    $this->assertTrue($allocations->contains('id', $alloc1->id));
    $this->assertFalse($allocations->contains('id', $alloc2->id));

    // Test Filter by Semester
    $response = $this->actingAs($admin)->get(route('admin.academy.allocations', ['semester_id' => $semester2->id]));
    $response->assertStatus(200);
    $allocations = $response->viewData('allocations');
    $this->assertTrue($allocations->contains('id', $alloc2->id));
    $this->assertFalse($allocations->contains('id', $alloc1->id));
});
