@extends('layouts.admin')

@section('title', $editMode ? 'Edit Class Marking' : 'Class Marking Details')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <i class="fas fa-chevron-right text-muted mx-2" style="font-size: 0.8rem;"></i>
    <a href="{{ route('admin.attendance.class_markings') }}">Class Markings</a>
    <i class="fas fa-chevron-right text-muted mx-2" style="font-size: 0.8rem;"></i>
    <span class="text-primary">{{ $editMode ? 'Edit Attendance' : 'Students' }}</span>
@endsection

@section('content')
<div class="container-fluid">
    <div class="mb-3 d-flex flex-wrap gap-2 justify-content-between align-items-center">
        <a href="{{ route('admin.attendance.class_markings') }}" class="btn btn-sm btn-light border">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
        <div class="d-flex gap-2">
            @if($editMode)
                <a href="{{ route('admin.attendance.class_markings.students', [
                    'marked_by' => $teacher->id,
                    'session_id' => $session->id,
                    'course_id' => $course->id,
                    'semester_id' => $semester->id,
                    'section_id' => $section->id,
                    'subject_id' => $subject->id,
                    'date' => $date,
                ]) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-eye me-1"></i> View Only
                </a>
            @else
                <a href="{{ route('admin.attendance.class_markings.students', [
                    'marked_by' => $teacher->id,
                    'session_id' => $session->id,
                    'course_id' => $course->id,
                    'semester_id' => $semester->id,
                    'section_id' => $section->id,
                    'subject_id' => $subject->id,
                    'date' => $date,
                    'edit' => 1,
                ]) }}" class="btn btn-sm btn-warning">
                    <i class="fas fa-edit me-1"></i> Edit Attendance
                </a>
            @endif
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="card shadow-sm border-0 mb-4 overflow-hidden">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-lg-6 border-end border-light pe-lg-4 mb-3 mb-lg-0">
                    <h5 class="fw-bold mb-2">{{ $subject->name }}</h5>
                    <p class="text-muted small mb-1">
                        <i class="fas fa-chalkboard-teacher me-1 text-secondary"></i>
                        Marked by: <strong>{{ $teacher->name }}</strong>
                    </p>
                    <p class="text-muted small mb-1">
                        <i class="fas fa-calendar-day me-1 text-secondary"></i>
                        Date: <strong>{{ \Carbon\Carbon::parse($date)->format('d M, Y') }}</strong>
                    </p>
                    <p class="text-muted small mb-0">
                        <i class="fas fa-graduation-cap me-1 text-secondary"></i>
                        {{ $course->name }}
                        <span class="badge bg-light text-dark border ms-1">{{ $semester->name }}</span>
                        <span class="badge bg-secondary ms-1">{{ $section->name }}</span>
                        <span class="badge bg-info-subtle text-info border ms-1">{{ $session->name }}</span>
                    </p>
                </div>
                <div class="col-lg-6 ps-lg-4">
                    <div class="row g-3">
                        <div class="col-4">
                            <div class="p-3 bg-light rounded-3 text-center h-100 shadow-sm border border-light">
                                <span class="text-muted small d-block mb-1 text-uppercase fw-bold" style="font-size: 0.7rem;">Total</span>
                                <h4 class="fw-bold mb-0 text-dark" id="statTotal">{{ $students->count() }}</h4>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-3 bg-success-subtle rounded-3 text-center h-100 shadow-sm border border-success-subtle">
                                <span class="text-success small d-block mb-1 text-uppercase fw-bold" style="font-size: 0.7rem;">Present</span>
                                <h4 class="fw-bold mb-0 text-success text-opacity-75" id="statPresent">{{ $presentCount }}</h4>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-3 bg-danger-subtle rounded-3 text-center h-100 shadow-sm border border-danger-subtle">
                                <span class="text-danger small d-block mb-1 text-uppercase fw-bold" style="font-size: 0.7rem;">Absent</span>
                                <h4 class="fw-bold mb-0 text-danger text-opacity-75" id="statAbsent">{{ $absentCount }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($editMode)
    <form action="{{ route('admin.attendance.class_markings.students.update') }}" method="POST" id="attendanceEditForm">
        @csrf
        <input type="hidden" name="marked_by" value="{{ $teacher->id }}">
        <input type="hidden" name="session_id" value="{{ $session->id }}">
        <input type="hidden" name="course_id" value="{{ $course->id }}">
        <input type="hidden" name="semester_id" value="{{ $semester->id }}">
        <input type="hidden" name="section_id" value="{{ $section->id }}">
        <input type="hidden" name="subject_id" value="{{ $subject->id }}">
        <input type="hidden" name="date" value="{{ $date }}">
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <h5 class="card-title mb-0 fw-bold">Student Attendance</h5>
            @if($editMode && $students->isNotEmpty())
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-sm btn-outline-success" id="markAllPresent">
                        <i class="fas fa-check-double me-1"></i> All Present
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger" id="markAllAbsent">
                        <i class="fas fa-times me-1"></i> All Absent
                    </button>
                    <button type="submit" class="btn btn-sm btn-primary" form="attendanceEditForm">
                        <i class="fas fa-save me-1"></i> Save Attendance
                    </button>
                </div>
            @endif
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">#</th>
                            <th>Student</th>
                            <th>Roll No.</th>
                            <th>{{ $editMode ? 'Mark Status' : 'Status' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $student)
                        <tr>
                            <td class="ps-4">{{ $loop->iteration }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm me-2">
                                        <img src="{{ $student['profile_photo'] }}" class="rounded-circle" style="width: 32px; height: 32px; object-fit: cover;" alt="">
                                    </div>
                                    <span class="fw-bold">{{ $student['name'] }}</span>
                                </div>
                            </td>
                            <td>{{ $student['roll_no'] }}</td>
                            <td>
                                @if($editMode)
                                    @php
                                        $currentStatus = old('attendance.'.$student['id'], $student['status']);
                                    @endphp
                                    <div class="btn-group btn-group-sm attendance-toggle" role="group" data-student-id="{{ $student['id'] }}">
                                        <input type="radio"
                                               class="btn-check"
                                               name="attendance[{{ $student['id'] }}]"
                                               id="present_{{ $student['id'] }}"
                                               value="present"
                                               autocomplete="off"
                                               {{ $currentStatus === 'present' ? 'checked' : '' }}>
                                        <label class="btn btn-outline-success" for="present_{{ $student['id'] }}">Present</label>

                                        <input type="radio"
                                               class="btn-check"
                                               name="attendance[{{ $student['id'] }}]"
                                               id="absent_{{ $student['id'] }}"
                                               value="absent"
                                               autocomplete="off"
                                               {{ $currentStatus === 'absent' ? 'checked' : '' }}>
                                        <label class="btn btn-outline-danger" for="absent_{{ $student['id'] }}">Absent</label>
                                    </div>
                                    @if(!$student['was_marked'])
                                        <small class="text-muted ms-2 d-inline-block">(not marked yet)</small>
                                    @endif
                                @else
                                    @if($student['status'] === 'present')
                                        <span class="badge bg-success-subtle text-success px-3">Present</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger px-3">Absent</span>
                                        @if(!$student['was_marked'])
                                            <small class="text-muted ms-1">(not marked)</small>
                                        @endif
                                    @endif
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">No students found for this class marking.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($editMode && $students->isNotEmpty())
        <div class="card-footer bg-white border-0 py-3 text-end">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i> Save Attendance
            </button>
        </div>
        @endif
    </div>

    @if($editMode)
    </form>
    @endif
</div>

@if($editMode)
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const presentStat = document.getElementById('statPresent');
        const absentStat = document.getElementById('statAbsent');

        function updateStats() {
            const presentCount = document.querySelectorAll('input[name^="attendance["][value="present"]:checked').length;
            const absentCount = document.querySelectorAll('input[name^="attendance["][value="absent"]:checked').length;
            if (presentStat) presentStat.textContent = presentCount;
            if (absentStat) absentStat.textContent = absentCount;
        }

        document.querySelectorAll('input[name^="attendance["]').forEach(function (input) {
            input.addEventListener('change', updateStats);
        });

        const markAllPresent = document.getElementById('markAllPresent');
        const markAllAbsent = document.getElementById('markAllAbsent');

        if (markAllPresent) {
            markAllPresent.addEventListener('click', function () {
                document.querySelectorAll('input[name^="attendance["][value="present"]').forEach(function (input) {
                    input.checked = true;
                });
                updateStats();
            });
        }

        if (markAllAbsent) {
            markAllAbsent.addEventListener('click', function () {
                document.querySelectorAll('input[name^="attendance["][value="absent"]').forEach(function (input) {
                    input.checked = true;
                });
                updateStats();
            });
        }

        updateStats();
    });
</script>
@endpush
@endif
@endsection
