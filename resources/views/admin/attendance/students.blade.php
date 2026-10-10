@extends('layouts.admin')

@section('title', 'Student Attendance Report')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <i class="fas fa-chevron-right text-muted mx-2" style="font-size: 0.8rem;"></i>
    <span class="text-primary">Student Attendance</span>
@endsection

@section('content')
<div class="container-fluid">
    <form action="{{ route('admin.attendance.students') }}" method="GET" class="mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0 fw-bold">Filter Attendance</h5>
            </div>
            <div class="card-body">
                <div class="filter-card-grid">
                    <div class="search-col">
                        <label class="form-label small text-muted fw-bold">Student Search</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0 text-muted">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" name="student_search" class="form-control border-start-0"
                                placeholder="Search by name or roll no..."
                                value="{{ request('student_search') }}">
                        </div>
                        @if(request('student_id'))
                            <input type="hidden" name="student_id" value="{{ request('student_id') }}">
                        @endif
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

                    <div>
                        <label class="form-label small text-muted fw-bold">Date</label>
                        <input type="date" name="date" class="form-control form-control-sm" value="{{ request('date') }}">
                    </div>

                    <div class="actions-col">
                        <button type="submit" class="btn btn-sm btn-primary w-100 shadow-sm">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <a href="{{ route('admin.attendance.students') }}" class="btn btn-sm btn-light border w-100">
                            Reset
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <form action="{{ route('admin.attendance.monthly_export') }}" method="GET" class="mb-4">
        <input type="hidden" name="scope" value="students">
        @if(request('student_id'))
            <input type="hidden" name="student_id" value="{{ request('student_id') }}">
        @endif
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0 fw-bold">Student Attendance Export</h5>
            </div>
            <div class="card-body">
                <div class="filter-card-grid">
                    <div>
                        <label class="form-label small text-muted fw-bold">Month</label>
                        <select name="month" class="form-select form-select-sm" required>
                            @foreach(['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'] as $index => $monthName)
                                <option value="{{ $index + 1 }}" {{ (int) request('month', now()->month) === ($index + 1) ? 'selected' : '' }}>
                                    {{ $monthName }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label small text-muted fw-bold">Year</label>
                        <input type="number" name="year" min="2000" max="2100" class="form-control form-control-sm" value="{{ request('year', now()->year) }}" required>
                    </div>
                    <div class="search-col">
                        <label class="form-label small text-muted fw-bold">Student Search</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0 text-muted">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" name="student_search" class="form-control border-start-0"
                                placeholder="Search by name or roll no..."
                                value="{{ request('student_search') }}">
                        </div>
                    </div>
                    <div>
                        <label class="form-label small text-muted fw-bold">Course</label>
                        <select name="course_id" id="export_course_id" class="form-select form-select-sm">
                            <option value="">All Courses</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}" {{ (string) request('course_id') === (string) $course->id ? 'selected' : '' }}>
                                    {{ $course->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label small text-muted fw-bold">Batch</label>
                        <select name="batch_id" id="export_batch_id" class="form-select form-select-sm" {{ request('course_id') ? '' : 'disabled' }}>
                            <option value="">{{ request('course_id') ? 'All Batches' : 'Select Course First' }}</option>
                            @foreach($batches as $batch)
                                <option value="{{ $batch->id }}" {{ (string) request('batch_id') === (string) $batch->id ? 'selected' : '' }}>
                                    {{ $batch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label small text-muted fw-bold">Session</label>
                        <select name="session_id" id="export_session_id" class="form-select form-select-sm" {{ request('batch_id') ? '' : 'disabled' }}>
                            <option value="">{{ request('batch_id') ? 'All Sessions' : 'Select Batch First' }}</option>
                            @foreach($sessions as $session)
                                <option value="{{ $session->id }}" {{ (string) request('session_id') === (string) $session->id ? 'selected' : '' }}>
                                    {{ $session->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label small text-muted fw-bold">Semester</label>
                        <select name="semester_id" id="export_semester_id" class="form-select form-select-sm" {{ request('course_id') ? '' : 'disabled' }}>
                            <option value="">{{ request('course_id') ? 'All Semesters' : 'Select Course First' }}</option>
                            @foreach($semesters as $semester)
                                <option value="{{ $semester->id }}" {{ (string) request('semester_id') === (string) $semester->id ? 'selected' : '' }}>
                                    {{ $semester->name }}{{ $semester->part ? ' ('.$semester->part.')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label small text-muted fw-bold">Section</label>
                        <select name="section_id" id="export_section_id" class="form-select form-select-sm" {{ request('semester_id') ? '' : 'disabled' }}>
                            <option value="">{{ request('semester_id') ? 'All Sections' : 'Select Semester First' }}</option>
                            @foreach($sections as $section)
                                <option value="{{ $section->id }}" {{ (string) request('section_id') === (string) $section->id ? 'selected' : '' }}>
                                    {{ $section->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label small text-muted fw-bold">Subject</label>
                        <select name="subject_id" id="export_subject_id" class="form-select form-select-sm" {{ request('semester_id') ? '' : 'disabled' }}>
                            <option value="">{{ request('semester_id') ? 'All Subjects' : 'Select Semester First' }}</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ (string) request('subject_id') === (string) $subject->id ? 'selected' : '' }}>
                                    {{ $subject->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label small text-muted fw-bold">Format</label>
                        <select name="format" class="form-select form-select-sm">
                            <option value="csv">CSV (Download)</option>
                            <option value="json">JSON</option>
                        </select>
                    </div>
                    <div class="actions-col">
                        <button type="submit" class="btn btn-sm btn-success w-100 shadow-sm">
                            <i class="fas fa-file-export"></i> Export
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    @if($selectedStudent && $stats)
    <div class="card shadow-sm border-0 mb-4 overflow-hidden">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-lg-4 border-end border-light pe-lg-4 mb-3 mb-lg-0">
                    <div class="d-flex align-items-center">
                        <div class="avatar-lg me-3">
                            <img src="{{ $selectedStudent->getProfilePhotoUrl() }}" alt="{{ $selectedStudent->name }}"
                                class="rounded-circle border border-2 border-primary shadow-sm"
                                style="width: 70px; height: 70px; object-fit: cover;">
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1">{{ $selectedStudent->name }}</h5>
                            <p class="text-muted small mb-1">
                                <i class="fas fa-id-card me-1 text-secondary"></i> Roll No: <strong>{{ $selectedStudent->studentProfile->roll_no ?? 'N/A' }}</strong>
                            </p>
                            <p class="text-muted small mb-0">
                                <i class="fas fa-graduation-cap me-1 text-secondary"></i> {{ $selectedStudent->studentProfile->course->name ?? 'N/A' }}
                                <span class="badge bg-light text-dark border ms-1">{{ $selectedStudent->studentProfile->semester->name ?? 'N/A' }}</span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8 ps-lg-4">
                    <div class="row g-3">
                        <div class="col-6 col-xl-3">
                            <div class="p-3 bg-light rounded-3 text-center h-100 shadow-sm border border-light">
                                <span class="text-muted small d-block mb-1 text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Total Classes</span>
                                <h4 class="fw-bold mb-0 text-dark">{{ $stats['total'] }}</h4>
                            </div>
                        </div>
                        <div class="col-6 col-xl-3">
                            <div class="p-3 bg-success-subtle rounded-3 text-center h-100 shadow-sm border border-success-subtle">
                                <span class="text-success small d-block mb-1 text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Attended</span>
                                <h4 class="fw-bold mb-0 text-success text-opacity-75">{{ $stats['present'] }}</h4>
                            </div>
                        </div>
                        <div class="col-6 col-xl-3">
                            <div class="p-3 bg-danger-subtle rounded-3 text-center h-100 shadow-sm border border-danger-subtle">
                                <span class="text-danger small d-block mb-1 text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Absent</span>
                                <h4 class="fw-bold mb-0 text-danger text-opacity-75">{{ $stats['absent'] }}</h4>
                            </div>
                        </div>
                        <div class="col-6 col-xl-3">
                            <div class="p-3 bg-info-subtle rounded-3 text-center h-100 shadow-sm border border-info-subtle">
                                <span class="text-info small d-block mb-1 text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">Leave</span>
                                <h4 class="fw-bold mb-0 text-info">{{ $stats['leave'] ?? 0 }}</h4>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small text-muted fw-semibold">Attendance Percentage</span>
                            <span class="badge {{ $stats['percentage'] >= 75 ? 'bg-success' : ($stats['percentage'] >= 50 ? 'bg-warning text-dark' : 'bg-danger') }} fw-bold px-2 py-1">
                                {{ $stats['percentage'] }}%
                            </span>
                        </div>
                        <div class="progress" style="height: 10px; border-radius: 5px;">
                            <div class="progress-bar {{ $stats['percentage'] >= 75 ? 'bg-success' : ($stats['percentage'] >= 50 ? 'bg-warning' : 'bg-danger') }}"
                                 role="progressbar"
                                 style="width: {{ $stats['percentage'] }}%"
                                 aria-valuenow="{{ $stats['percentage'] }}"
                                 aria-valuemin="0"
                                 aria-valuemax="100">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 fw-bold">
                @if($selectedStudent)
                    Attendance History: {{ $selectedStudent->name }}
                @else
                    Attendance Records
                @endif
            </h5>
            @if($selectedStudent)
                <a href="{{ route('admin.attendance.students') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-times-circle me-1"></i> Clear Student Filter
                </a>
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
                            <th>Course & Sem</th>
                            <th>Subject</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Marked by</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $attendance)
                        <tr>
                            <td class="ps-4">{{ ($attendances->currentPage()-1) * $attendances->perPage() + $loop->iteration }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm me-2">
                                        <img src="{{ $attendance->student->getProfilePhotoUrl() }}" class="rounded-circle" style="width: 32px; height: 32px; object-fit: cover;">
                                    </div>
                                    <span class="fw-bold">{{ $attendance->student->name }}</span>
                                </div>
                            </td>
                            <td>{{ $attendance->student->studentProfile->roll_no ?? 'N/A' }}</td>
                            <td>
                                <small>{{ $attendance->course->name }}</small><br>
                                <span class="badge bg-light text-dark border">{{ $attendance->semester->name }}</span>
                                <span class="badge bg-secondary ms-1">{{ $attendance->section->name ?? 'N/A' }}</span>
                            </td>
                            <td>{{ $attendance->subject->name }}</td>
                            <td>{{ \Carbon\Carbon::parse($attendance->date)->format('d M, Y') }}</td>
                            <td>
                                @if($attendance->status === 'present')
                                    <span class="badge bg-success-subtle text-success px-3">Present</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger px-3">Absent</span>
                                @endif
                            </td>
                            <td>{{ $attendance->marker->name ?? 'N/A' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">No attendance records found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($attendances->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $attendances->links() }}
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
    'allSemesterLabel' => 'All Semesters',
    'allSectionLabel' => 'All Sections',
    'allSubjectLabel' => 'All Subjects',
])
@include('admin.partials.academic_filter_cascade', [
    'courseSelectId' => 'export_course_id',
    'batchSelectId' => 'export_batch_id',
    'sessionSelectId' => 'export_session_id',
    'semesterSelectId' => 'export_semester_id',
    'sectionSelectId' => 'export_section_id',
    'subjectSelectId' => 'export_subject_id',
    'allSemesterLabel' => 'All Semesters',
    'allSectionLabel' => 'All Sections',
    'allSubjectLabel' => 'All Subjects',
])
@endsection
