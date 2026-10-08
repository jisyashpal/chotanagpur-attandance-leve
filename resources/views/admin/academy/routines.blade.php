@extends('layouts.admin')

@section('title', 'Class Routine')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <i class="fas fa-chevron-right text-muted mx-2" style="font-size: 0.8rem;"></i>
    <span class="text-primary">Routine</span>
@endsection

@section('content')
<div class="container-fluid">
    @if (session('error'))
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.academy.routines') }}" method="GET" class="mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label small text-muted fw-bold">Course</label>
                        <select name="course_id" id="filter_course_id" class="form-select form-select-sm">
                            <option value="">All Courses</option>
                            @foreach ($courses as $course)
                                <option value="{{ $course->id }}" @selected(request('course_id') == $course->id)>{{ $course->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted fw-bold">Batch</label>
                        <select name="batch_id" id="filter_batch_id" class="form-select form-select-sm">
                            <option value="">All Batches</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted fw-bold">Session</label>
                        <select name="session_id" id="filter_session_id" class="form-select form-select-sm">
                            <option value="">All Sessions</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted fw-bold">Semester</label>
                        <select name="semester_id" id="filter_semester_id" class="form-select form-select-sm">
                            <option value="">All Semesters</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label small text-muted fw-bold">Section</label>
                        <select name="section_id" id="filter_section_id" class="form-select form-select-sm">
                            <option value="">All</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label small text-muted fw-bold">Day</label>
                        <select name="day_of_week" class="form-select form-select-sm">
                            <option value="">All</option>
                            @foreach ($days as $num => $name)
                                <option value="{{ $num }}" @selected(request('day_of_week') == $num)>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-primary w-100">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <a href="{{ route('admin.academy.routines') }}" class="btn btn-sm btn-light border w-100">Reset</a>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div class="row">
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 border-top border-primary border-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-bold">Add Routine Slot</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.academy.routines.store') }}" method="POST" id="routineForm">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Course</label>
                            <select name="course_id" id="course_id" class="form-select" required>
                                <option value="">Choose Course</option>
                                @foreach ($courses as $course)
                                    <option value="{{ $course->id }}">{{ $course->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Batch</label>
                                <select name="batch_id" id="batch_id" class="form-select" required>
                                    <option value="">Select Course First</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Session</label>
                                <select name="session_id" id="session_id" class="form-select" required>
                                    <option value="">Select Batch First</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Semester</label>
                                <select name="semester_id" id="semester_id" class="form-select" required>
                                    <option value="">Select Course First</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Section</label>
                                <select name="section_id" id="section_id" class="form-select" required>
                                    <option value="">Select Semester First</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Subject</label>
                            <select name="subject_id" id="subject_id" class="form-select" required>
                                <option value="">Select Semester First</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Faculty <span class="text-muted small">(optional)</span></label>
                            <select name="faculty_id" id="faculty_id" class="form-select">
                                <option value="">Not assigned</option>
                                @foreach ($faculties as $faculty)
                                    <option value="{{ $faculty->id }}">{{ $faculty->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Day</label>
                                <select name="day_of_week" class="form-select" required>
                                    <option value="">Choose Day</option>
                                    @foreach ($days as $num => $name)
                                        <option value="{{ $num }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Period</label>
                                <select name="period_number" class="form-select" required>
                                    <option value="">Choose Period</option>
                                    @for ($i = 1; $i <= 8; $i++)
                                        <option value="{{ $i }}">Period {{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Start Time</label>
                                <input type="time" name="start_time" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">End Time</label>
                                <input type="time" name="end_time" class="form-control">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Room</label>
                            <input type="text" name="room" class="form-control" placeholder="e.g. Room 101">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-plus me-2"></i>Add to Routine
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-bold">Class Routine</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-3">#</th>
                                    <th>Day</th>
                                    <th>Period</th>
                                    <th>Subject</th>
                                    <th>Section</th>
                                    <th>Session</th>
                                    <th>Faculty</th>
                                    <th>Time / Room</th>
                                    <th class="text-end pe-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($routines as $routine)
                                    <tr>
                                        <td class="ps-3">{{ ($routines->currentPage() - 1) * $routines->perPage() + $loop->iteration }}</td>
                                        <td class="fw-semibold">{{ $routine->day_name }}</td>
                                        <td><span class="badge bg-primary-subtle text-primary border">P{{ $routine->period_number }}</span></td>
                                        <td>
                                            <div class="fw-bold">{{ $routine->subject->name ?? 'N/A' }}</div>
                                            <small class="text-muted">{{ $routine->course->name ?? '' }} · {{ $routine->semester->name ?? '' }}</small>
                                        </td>
                                        <td>{{ $routine->section->name ?? 'N/A' }}</td>
                                        <td class="small">{{ $routine->session->name ?? 'N/A' }}</td>
                                        <td>{{ $routine->faculty->name ?? '—' }}</td>
                                        <td class="small">
                                            @if ($routine->start_time || $routine->end_time)
                                                {{ $routine->start_time ? \Illuminate\Support\Str::of($routine->start_time)->substr(0, 5) : '—' }}
                                                –
                                                {{ $routine->end_time ? \Illuminate\Support\Str::of($routine->end_time)->substr(0, 5) : '—' }}
                                            @else
                                                —
                                            @endif
                                            @if ($routine->room)
                                                <div class="text-muted">{{ $routine->room }}</div>
                                            @endif
                                        </td>
                                        <td class="text-end pe-3">
                                            <button type="button" class="btn btn-sm btn-light text-primary me-1 edit-routine-btn"
                                                data-id="{{ $routine->id }}"
                                                data-course-id="{{ $routine->course_id }}"
                                                data-batch-id="{{ $routine->batch_id }}"
                                                data-session-id="{{ $routine->session_id }}"
                                                data-semester-id="{{ $routine->semester_id }}"
                                                data-section-id="{{ $routine->section_id }}"
                                                data-subject-id="{{ $routine->subject_id }}"
                                                data-faculty-id="{{ $routine->faculty_id }}"
                                                data-day="{{ $routine->day_of_week }}"
                                                data-period="{{ $routine->period_number }}"
                                                data-start="{{ $routine->start_time ? \Illuminate\Support\Str::of($routine->start_time)->substr(0, 5) : '' }}"
                                                data-end="{{ $routine->end_time ? \Illuminate\Support\Str::of($routine->end_time)->substr(0, 5) : '' }}"
                                                data-room="{{ $routine->room }}"
                                                data-active="{{ $routine->is_active ? 1 : 0 }}"
                                                title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form action="{{ route('admin.academy.routines.destroy', $routine->id) }}" method="POST" class="d-inline"
                                                onsubmit="return confirm('Delete this routine slot?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-light text-danger" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4 text-muted">No routine slots found. Add one using the form.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if ($routines->hasPages())
                    <div class="card-footer bg-white border-0 py-3">
                        {{ $routines->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editRoutineModal" tabindex="-1" aria-labelledby="editRoutineModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="editRoutineForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editRoutineModalLabel">Edit Routine Slot</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Course</label>
                            <select name="course_id" id="edit_course_id" class="form-select" required>
                                <option value="">Choose Course</option>
                                @foreach ($courses as $course)
                                    <option value="{{ $course->id }}">{{ $course->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Batch</label>
                            <select name="batch_id" id="edit_batch_id" class="form-select" required></select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Session</label>
                            <select name="session_id" id="edit_session_id" class="form-select" required></select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Semester</label>
                            <select name="semester_id" id="edit_semester_id" class="form-select" required></select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Section</label>
                            <select name="section_id" id="edit_section_id" class="form-select" required></select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Subject</label>
                            <select name="subject_id" id="edit_subject_id" class="form-select" required></select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Faculty</label>
                            <select name="faculty_id" id="edit_faculty_id" class="form-select">
                                <option value="">Not assigned</option>
                                @foreach ($faculties as $faculty)
                                    <option value="{{ $faculty->id }}">{{ $faculty->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Day</label>
                            <select name="day_of_week" id="edit_day_of_week" class="form-select" required>
                                @foreach ($days as $num => $name)
                                    <option value="{{ $num }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Period</label>
                            <select name="period_number" id="edit_period_number" class="form-select" required>
                                @for ($i = 1; $i <= 8; $i++)
                                    <option value="{{ $i }}">Period {{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Start Time</label>
                            <input type="time" name="start_time" id="edit_start_time" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">End Time</label>
                            <input type="time" name="end_time" id="edit_end_time" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Room</label>
                            <input type="text" name="room" id="edit_room" class="form-control">
                        </div>
                        <div class="col-md-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" id="edit_is_active" value="1">
                                <label class="form-check-label" for="edit_is_active">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const routes = {
        batches: "{{ route('admin.academy.courses.batches_json', ['course_id' => '__ID__']) }}",
        sessions: "{{ route('admin.academy.batches.sessions_json', ['batch_id' => '__ID__']) }}",
        semesters: "{{ route('admin.academy.courses.semesters_json', ['course_id' => '__ID__']) }}",
        sections: "{{ route('admin.academy.semesters.sections_json', ['semester_id' => '__ID__']) }}",
        subjects: "{{ route('admin.academy.semesters.subjects_json', ['semester_id' => '__ID__']) }}",
        update: "{{ route('admin.academy.routines.update', ['id' => '__ID__']) }}",
    };

    function url(template, id) {
        return template.replace('__ID__', id);
    }

    function fillSelect(select, items, valueKey, labelFn, placeholder, selectedId = null) {
        select.innerHTML = `<option value="">${placeholder}</option>`;
        (items || []).forEach(item => {
            const selected = String(selectedId) === String(item[valueKey]) ? 'selected' : '';
            select.innerHTML += `<option value="${item[valueKey]}" ${selected}>${labelFn(item)}</option>`;
        });
    }

    async function fetchJson(endpoint) {
        const res = await fetch(endpoint);
        return res.json();
    }

    async function loadBatches(courseId, batchSelect, sessionSelect, selectedBatchId = null, selectedSessionId = null) {
        batchSelect.innerHTML = '<option value="">Loading...</option>';
        sessionSelect.innerHTML = '<option value="">Select Batch First</option>';
        if (!courseId) {
            batchSelect.innerHTML = '<option value="">Select Course First</option>';
            return;
        }
        const data = await fetchJson(url(routes.batches, courseId));
        fillSelect(batchSelect, data.batches, 'id', b => b.name, 'Select Batch', selectedBatchId);
        if (selectedBatchId) {
            await loadSessions(selectedBatchId, sessionSelect, selectedSessionId);
        }
    }

    async function loadSessions(batchId, sessionSelect, selectedSessionId = null) {
        sessionSelect.innerHTML = '<option value="">Loading...</option>';
        if (!batchId) {
            sessionSelect.innerHTML = '<option value="">Select Batch First</option>';
            return;
        }
        const data = await fetchJson(url(routes.sessions, batchId));
        fillSelect(sessionSelect, data.sessions, 'id', s => s.name, 'Select Session', selectedSessionId);
    }

    async function loadSemesters(courseId, semesterSelect, sectionSelect, subjectSelect, selectedSemesterId = null, selectedSectionId = null, selectedSubjectId = null) {
        semesterSelect.innerHTML = '<option value="">Loading...</option>';
        sectionSelect.innerHTML = '<option value="">Select Semester First</option>';
        subjectSelect.innerHTML = '<option value="">Select Semester First</option>';
        if (!courseId) {
            semesterSelect.innerHTML = '<option value="">Select Course First</option>';
            return;
        }
        const data = await fetchJson(url(routes.semesters, courseId));
        fillSelect(semesterSelect, data.semesters, 'id', s => s.name, 'Select Semester', selectedSemesterId);
        if (selectedSemesterId) {
            await Promise.all([
                loadSections(selectedSemesterId, sectionSelect, selectedSectionId),
                loadSubjects(selectedSemesterId, subjectSelect, selectedSubjectId),
            ]);
        }
    }

    async function loadSections(semesterId, sectionSelect, selectedSectionId = null) {
        sectionSelect.innerHTML = '<option value="">Loading...</option>';
        if (!semesterId) {
            sectionSelect.innerHTML = '<option value="">Select Semester First</option>';
            return;
        }
        const data = await fetchJson(url(routes.sections, semesterId));
        fillSelect(sectionSelect, data.sections, 'id', s => `${s.name} (${s.code})`, 'Select Section', selectedSectionId);
    }

    async function loadSubjects(semesterId, subjectSelect, selectedSubjectId = null) {
        subjectSelect.innerHTML = '<option value="">Loading...</option>';
        if (!semesterId) {
            subjectSelect.innerHTML = '<option value="">Select Semester First</option>';
            return;
        }
        const data = await fetchJson(url(routes.subjects, semesterId));
        fillSelect(subjectSelect, data.subjects, 'id', s => `${s.name} (${s.code})`, 'Select Subject', selectedSubjectId);
    }

    // Create form cascading
    const courseSelect = document.getElementById('course_id');
    const batchSelect = document.getElementById('batch_id');
    const sessionSelect = document.getElementById('session_id');
    const semesterSelect = document.getElementById('semester_id');
    const sectionSelect = document.getElementById('section_id');
    const subjectSelect = document.getElementById('subject_id');

    courseSelect.addEventListener('change', function () {
        loadBatches(this.value, batchSelect, sessionSelect);
        loadSemesters(this.value, semesterSelect, sectionSelect, subjectSelect);
    });
    batchSelect.addEventListener('change', function () {
        loadSessions(this.value, sessionSelect);
    });
    semesterSelect.addEventListener('change', function () {
        loadSections(this.value, sectionSelect);
        loadSubjects(this.value, subjectSelect);
    });

    // Filter cascading
    const filterCourse = document.getElementById('filter_course_id');
    const filterBatch = document.getElementById('filter_batch_id');
    const filterSession = document.getElementById('filter_session_id');
    const filterSemester = document.getElementById('filter_semester_id');
    const filterSection = document.getElementById('filter_section_id');

    filterCourse.addEventListener('change', function () {
        loadBatches(this.value, filterBatch, filterSession);
        loadSemesters(this.value, filterSemester, filterSection, document.createElement('select'));
    });
    filterBatch.addEventListener('change', function () {
        loadSessions(this.value, filterSession);
    });
    filterSemester.addEventListener('change', function () {
        loadSections(this.value, filterSection);
    });

    // Prefill filters from query string
    const qs = {
        course: @json(request('course_id')),
        batch: @json(request('batch_id')),
        session: @json(request('session_id')),
        semester: @json(request('semester_id')),
        section: @json(request('section_id')),
    };

    if (qs.course) {
        loadBatches(qs.course, filterBatch, filterSession, qs.batch, qs.session);
        loadSemesters(qs.course, filterSemester, filterSection, document.createElement('select'), qs.semester, qs.section);
    }

    // Edit modal
    const editModal = new bootstrap.Modal(document.getElementById('editRoutineModal'));
    const editForm = document.getElementById('editRoutineForm');
    const editCourse = document.getElementById('edit_course_id');
    const editBatch = document.getElementById('edit_batch_id');
    const editSession = document.getElementById('edit_session_id');
    const editSemester = document.getElementById('edit_semester_id');
    const editSection = document.getElementById('edit_section_id');
    const editSubject = document.getElementById('edit_subject_id');

    editCourse.addEventListener('change', function () {
        loadBatches(this.value, editBatch, editSession);
        loadSemesters(this.value, editSemester, editSection, editSubject);
    });
    editBatch.addEventListener('change', function () {
        loadSessions(this.value, editSession);
    });
    editSemester.addEventListener('change', function () {
        loadSections(this.value, editSection);
        loadSubjects(this.value, editSubject);
    });

    document.querySelectorAll('.edit-routine-btn').forEach(btn => {
        btn.addEventListener('click', async function () {
            const id = this.dataset.id;
            editForm.action = url(routes.update, id);

            document.getElementById('edit_day_of_week').value = this.dataset.day;
            document.getElementById('edit_period_number').value = this.dataset.period;
            document.getElementById('edit_start_time').value = this.dataset.start || '';
            document.getElementById('edit_end_time').value = this.dataset.end || '';
            document.getElementById('edit_room').value = this.dataset.room || '';
            document.getElementById('edit_faculty_id').value = this.dataset.facultyId || '';
            document.getElementById('edit_is_active').checked = this.dataset.active === '1';

            editCourse.value = this.dataset.courseId;
            await loadBatches(this.dataset.courseId, editBatch, editSession, this.dataset.batchId, this.dataset.sessionId);
            await loadSemesters(
                this.dataset.courseId,
                editSemester,
                editSection,
                editSubject,
                this.dataset.semesterId,
                this.dataset.sectionId,
                this.dataset.subjectId
            );

            editModal.show();
        });
    });
})();
</script>
@endpush
