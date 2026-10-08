@extends('layouts.admin')

@section('title', 'Promote Students')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <i class="fas fa-chevron-right text-muted mx-2" style="font-size: 0.8rem;"></i>
    <span class="text-primary">Promote Students</span>
@endsection

@section('content')
<div class="container-fluid">
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="card shadow-sm border-0 border-top border-primary border-4 mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold">Filter Students</h5>
            <small class="text-muted">Course → Batch → Session → Semester → Section</small>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.users.students.promote') }}" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Course</label>
                    <select name="course_id" id="course_id" class="form-select" required>
                        <option value="">Select Course</option>
                        @foreach ($courses as $course)
                            <option value="{{ $course->id }}" @selected(request('course_id') == $course->id)>{{ $course->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Batch</label>
                    <select name="batch_id" id="batch_id" class="form-select" required>
                        <option value="">Select Course First</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Session</label>
                    <select name="session_id" id="session_id" class="form-select" required>
                        <option value="">Select Batch First</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Semester</label>
                    <select name="semester_id" id="semester_id" class="form-select" required>
                        <option value="">Select Course First</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Section (optional)</label>
                    <select name="section_id" id="section_id" class="form-select">
                        <option value="">All</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-primary w-100" type="submit">Load Students</button>
                </div>
            </form>
        </div>
    </div>

    @if ($students->isNotEmpty())
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold">Promote ({{ $students->count() }} found)</h5>
            <small class="text-muted">Odd semester → Mid-year (same session). Even semester → Year-end (pick next session).</small>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.users.students.promote.store') }}">
                @csrf
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Promotion Type</label>
                        <select name="promotion_type" id="promotion_type" class="form-select" required>
                            <option value="mid_year">Mid-Year (same session)</option>
                            <option value="year_end">Year-End (to next session)</option>
                        </select>
                    </div>
                    <div class="col-md-4" id="target_session_wrap" style="display:none;">
                        <label class="form-label">Target Session</label>
                        <select name="target_session_id" id="target_session_id" class="form-select">
                            <option value="">Select</option>
                            @foreach ($targetSessions as $session)
                                <option value="{{ $session->id }}">
                                    {{ $session->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Target Section (optional)</label>
                        <select name="target_section_id" class="form-select">
                            <option value="">Auto-match by name/code</option>
                            @foreach ($targetSections as $section)
                                <option value="{{ $section->id }}">{{ $section->name }} ({{ $section->semester->name ?? 'Sem '.$section->semester_id }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th><input type="checkbox" id="check_all" checked></th>
                                <th>Name</th>
                                <th>Roll</th>
                                <th>Batch</th>
                                <th>Session</th>
                                <th>Semester</th>
                                <th>Section</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($students as $profile)
                            <tr>
                                <td><input type="checkbox" name="student_ids[]" value="{{ $profile->user_id }}" class="student-check" checked></td>
                                <td>{{ $profile->user->name ?? 'N/A' }}</td>
                                <td>{{ $profile->roll_no }}</td>
                                <td>{{ $profile->batch->name ?? 'N/A' }}</td>
                                <td>{{ $profile->session->name ?? 'N/A' }}</td>
                                <td>{{ $profile->semester->name ?? 'N/A' }}</td>
                                <td>{{ $profile->section->name ?? 'N/A' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-warning" onclick="return confirm('Promote selected students?')">
                        <i class="fas fa-level-up-alt me-2"></i>Promote Selected
                    </button>
                </div>
            </form>
        </div>
    </div>
    @elseif (request()->filled('course_id'))
        <div class="alert alert-info">No students matched the selected filters.</div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const typeSelect = document.getElementById('promotion_type');
    const wrap = document.getElementById('target_session_wrap');
    if (typeSelect) {
        const toggle = () => {
            wrap.style.display = typeSelect.value === 'year_end' ? 'block' : 'none';
        };
        typeSelect.addEventListener('change', toggle);
        toggle();
    }

    const checkAll = document.getElementById('check_all');
    if (checkAll) {
        checkAll.addEventListener('change', function () {
            document.querySelectorAll('.student-check').forEach(cb => cb.checked = this.checked);
        });
    }

    function loadBatches(courseId, selectedBatchId = null) {
        const batchSelect = document.getElementById('batch_id');
        const sessionSelect = document.getElementById('session_id');
        batchSelect.innerHTML = '<option value="">Loading...</option>';
        sessionSelect.innerHTML = '<option value="">Select Batch First</option>';

        if (!courseId) {
            batchSelect.innerHTML = '<option value="">Select Course First</option>';
            return Promise.resolve();
        }

        const url = "{{ route('admin.academy.courses.batches_json', ['course_id' => '__ID__']) }}".replace('__ID__', courseId);
        return fetch(url)
            .then(r => r.json())
            .then(data => {
                batchSelect.innerHTML = '<option value="">Select Batch</option>';
                (data.batches || []).forEach(batch => {
                    const selected = String(selectedBatchId) === String(batch.id) ? 'selected' : '';
                    batchSelect.innerHTML += `<option value="${batch.id}" ${selected}>${batch.name}</option>`;
                });
            })
            .catch(() => {
                batchSelect.innerHTML = '<option value="">Error loading batches</option>';
            });
    }

    function loadSessions(batchId, selectedSessionId = null) {
        const sessionSelect = document.getElementById('session_id');
        sessionSelect.innerHTML = '<option value="">Loading...</option>';

        if (!batchId) {
            sessionSelect.innerHTML = '<option value="">Select Batch First</option>';
            return Promise.resolve();
        }

        const url = "{{ route('admin.academy.batches.sessions_json', ['batch_id' => '__ID__']) }}".replace('__ID__', batchId);
        return fetch(url)
            .then(r => r.json())
            .then(data => {
                sessionSelect.innerHTML = '<option value="">Select Session</option>';
                (data.sessions || []).forEach(session => {
                    const selected = String(selectedSessionId) === String(session.id) ? 'selected' : '';
                    sessionSelect.innerHTML += `<option value="${session.id}" ${selected}>${session.name}</option>`;
                });
            })
            .catch(() => {
                sessionSelect.innerHTML = '<option value="">Error loading sessions</option>';
            });
    }

    function loadSemesters(courseId, selectedSemesterId = null) {
        const semesterSelect = document.getElementById('semester_id');
        const sectionSelect = document.getElementById('section_id');
        semesterSelect.innerHTML = '<option value="">Loading...</option>';
        sectionSelect.innerHTML = '<option value="">All</option>';

        if (!courseId) {
            semesterSelect.innerHTML = '<option value="">Select Course First</option>';
            return Promise.resolve();
        }

        const url = "{{ route('admin.academy.courses.semesters_json', ['course_id' => '__COURSE_ID__']) }}".replace('__COURSE_ID__', courseId);
        return fetch(url)
            .then(r => r.json())
            .then(data => {
                semesterSelect.innerHTML = '<option value="">Select Semester</option>';
                (data.semesters || []).forEach(semester => {
                    const selected = String(selectedSemesterId) === String(semester.id) ? 'selected' : '';
                    semesterSelect.innerHTML += `<option value="${semester.id}" ${selected}>${semester.name}</option>`;
                });
            })
            .catch(() => {
                semesterSelect.innerHTML = '<option value="">Error loading semesters</option>';
            });
    }

    function loadSections(semesterId, selectedSectionId = null) {
        const sectionSelect = document.getElementById('section_id');
        sectionSelect.innerHTML = '<option value="">All</option>';

        if (!semesterId) {
            return Promise.resolve();
        }

        sectionSelect.innerHTML = '<option value="">Loading...</option>';
        const url = "{{ route('admin.academy.semesters.sections_json', ['semester_id' => '__SEMESTER_ID__']) }}".replace('__SEMESTER_ID__', semesterId);
        return fetch(url)
            .then(r => r.json())
            .then(data => {
                sectionSelect.innerHTML = '<option value="">All</option>';
                (data.sections || []).forEach(section => {
                    const selected = String(selectedSectionId) === String(section.id) ? 'selected' : '';
                    sectionSelect.innerHTML += `<option value="${section.id}" ${selected}>${section.name}</option>`;
                });
            })
            .catch(() => {
                sectionSelect.innerHTML = '<option value="">Error loading sections</option>';
            });
    }

    document.getElementById('course_id').addEventListener('change', function () {
        loadBatches(this.value);
        loadSemesters(this.value);
        document.getElementById('session_id').innerHTML = '<option value="">Select Batch First</option>';
        document.getElementById('section_id').innerHTML = '<option value="">All</option>';
    });

    document.getElementById('batch_id').addEventListener('change', function () {
        loadSessions(this.value);
    });

    document.getElementById('semester_id').addEventListener('change', function () {
        loadSections(this.value);
    });

    const selectedCourse = @json(request('course_id'));
    const selectedBatch = @json(request('batch_id'));
    const selectedSession = @json(request('session_id'));
    const selectedSemester = @json(request('semester_id'));
    const selectedSection = @json(request('section_id'));

    if (selectedCourse) {
        Promise.all([
            loadBatches(selectedCourse, selectedBatch),
            loadSemesters(selectedCourse, selectedSemester),
        ]).then(() => {
            const tasks = [];
            if (selectedBatch) {
                tasks.push(loadSessions(selectedBatch, selectedSession));
            }
            if (selectedSemester) {
                tasks.push(loadSections(selectedSemester, selectedSection));
            }
            return Promise.all(tasks);
        });
    }
});
</script>
@endsection
