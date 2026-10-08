@extends('layouts.admin')

@section('title', 'Class Markings')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <i class="fas fa-chevron-right text-muted mx-2" style="font-size: 0.8rem;"></i>
    <span class="text-primary">Class Markings</span>
@endsection

@section('content')
<div class="container-fluid">
    <form action="{{ route('admin.attendance.class_markings') }}" method="GET" class="mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0 fw-bold">Filter Class Markings</h5>
            </div>
            <div class="card-body">
                <div class="filter-card-grid">
                    <div>
                        <label class="form-label small text-muted fw-bold">Teacher</label>
                        <select name="marked_by" class="form-select form-select-sm">
                            <option value="">All Teachers</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}" {{ request('marked_by') == $teacher->id ? 'selected' : '' }}>
                                    {{ $teacher->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="form-label small text-muted fw-bold">Date</label>
                        <input type="date" name="date" class="form-control form-control-sm" value="{{ request('date') }}">
                    </div>

                    <div>
                        <label class="form-label small text-muted fw-bold">Course</label>
                        <select name="course_id" id="course_id" class="form-select form-select-sm">
                            <option value="">All Courses</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>{{ $course->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="form-label small text-muted fw-bold">Batch</label>
                        <select name="batch_id" id="batch_id" class="form-select form-select-sm" {{ request('course_id') ? '' : 'disabled' }}>
                            <option value="">{{ request('course_id') ? 'All Batches' : 'Select Course First' }}</option>
                            @foreach($batches as $batch)
                                <option value="{{ $batch->id }}" {{ request('batch_id') == $batch->id ? 'selected' : '' }}>{{ $batch->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="form-label small text-muted fw-bold">Session</label>
                        <select name="session_id" id="session_id" class="form-select form-select-sm" {{ request('batch_id') ? '' : 'disabled' }}>
                            <option value="">{{ request('batch_id') ? 'All Sessions' : 'Select Batch First' }}</option>
                            @foreach($sessions as $session)
                                <option value="{{ $session->id }}" {{ request('session_id') == $session->id ? 'selected' : '' }}>{{ $session->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="form-label small text-muted fw-bold">Semester</label>
                        <select name="semester_id" id="semester_id" class="form-select form-select-sm" {{ request('course_id') ? '' : 'disabled' }}>
                            <option value="">{{ request('course_id') ? 'All Semesters' : 'Select Course First' }}</option>
                            @foreach($semesters as $semester)
                                <option value="{{ $semester->id }}" {{ request('semester_id') == $semester->id ? 'selected' : '' }}>
                                    {{ $semester->name }}{{ $semester->part ? ' ('.$semester->part.')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="form-label small text-muted fw-bold">Section</label>
                        <select name="section_id" id="section_id" class="form-select form-select-sm" {{ request('semester_id') ? '' : 'disabled' }}>
                            <option value="">{{ request('semester_id') ? 'All Sections' : 'Select Semester First' }}</option>
                            @foreach($sections as $section)
                                <option value="{{ $section->id }}" {{ request('section_id') == $section->id ? 'selected' : '' }}>{{ $section->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="form-label small text-muted fw-bold">Subject</label>
                        <select name="subject_id" id="subject_id" class="form-select form-select-sm" {{ request('semester_id') ? '' : 'disabled' }}>
                            <option value="">{{ request('semester_id') ? 'All Subjects' : 'Select Semester First' }}</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="actions-col">
                        <button type="submit" class="btn btn-sm btn-primary w-100 shadow-sm">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <a href="{{ route('admin.attendance.class_markings') }}" class="btn btn-sm btn-light border w-100">
                            Reset
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h5 class="card-title mb-0 fw-bold">Marked Classes</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">#</th>
                            <th>Date</th>
                            <th>Teacher</th>
                            <th>Subject</th>
                            <th>Class</th>
                            <th>Present</th>
                            <th>Absent</th>
                            <th>Total</th>
                            <th class="pe-4 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($markings as $marking)
                        <tr>
                            <td class="ps-4">{{ ($markings->currentPage()-1) * $markings->perPage() + $loop->iteration }}</td>
                            <td>{{ \Carbon\Carbon::parse($marking->date)->format('d M, Y') }}</td>
                            <td class="fw-semibold">{{ $marking->marker->name ?? 'Unknown' }}</td>
                            <td>
                                {{ $marking->subject->name ?? 'N/A' }}
                                @if(!empty($marking->subject->code))
                                    <br><small class="text-muted">{{ $marking->subject->code }}</small>
                                @endif
                            </td>
                            <td>
                                <small class="d-block">{{ $marking->course->name ?? 'N/A' }}</small>
                                <span class="badge bg-light text-dark border">{{ $marking->semester->name ?? 'N/A' }}</span>
                                <span class="badge bg-secondary">{{ $marking->section->name ?? 'N/A' }}</span>
                                <br><small class="text-muted">Session: {{ $marking->session->name ?? 'N/A' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-success-subtle text-success px-3">{{ (int) $marking->present_count }}</span>
                            </td>
                            <td>
                                <span class="badge bg-danger-subtle text-danger px-3">{{ (int) $marking->absent_count }}</span>
                            </td>
                            <td class="fw-semibold">{{ (int) $marking->total_students }}</td>
                            <td class="pe-4 text-end">
                                <a href="{{ route('admin.attendance.class_markings.students', [
                                    'marked_by' => $marking->marked_by,
                                    'session_id' => $marking->session_id,
                                    'course_id' => $marking->course_id,
                                    'semester_id' => $marking->semester_id,
                                    'section_id' => $marking->section_id,
                                    'subject_id' => $marking->subject_id,
                                    'date' => $marking->date,
                                ]) }}" class="btn btn-sm btn-outline-primary me-1">
                                    <i class="fas fa-eye me-1"></i> View
                                </a>
                                <a href="{{ route('admin.attendance.class_markings.students', [
                                    'marked_by' => $marking->marked_by,
                                    'session_id' => $marking->session_id,
                                    'course_id' => $marking->course_id,
                                    'semester_id' => $marking->semester_id,
                                    'section_id' => $marking->section_id,
                                    'subject_id' => $marking->subject_id,
                                    'date' => $marking->date,
                                    'edit' => 1,
                                ]) }}" class="btn btn-sm btn-outline-warning">
                                    <i class="fas fa-edit me-1"></i> Edit
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">No class markings found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($markings->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $markings->links() }}
        </div>
        @endif
    </div>
</div>
@include('admin.partials.academic_filter_cascade', [
    'courseSelectId' => 'course_id',
    'batchSelectId' => 'batch_id',
    'sessionSelectId' => 'session_id',
    'semesterSelectId' => 'semester_id',
    'sectionSelectId' => 'section_id',
    'subjectSelectId' => 'subject_id',
])
@endsection
