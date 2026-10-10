@extends('layouts.admin')

@section('title', 'Student List')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <i class="fas fa-chevron-right text-muted mx-2" style="font-size: 0.8rem;"></i>
    <span class="text-primary">Students</span>
@endsection

@section('content')
    <div class="container-fluid">
        <!-- Filter and Search Form -->
        <form action="{{ route('admin.users.students') }}" method="GET" class="mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="filter-card-grid">
                        <!-- Search Input -->
                        <div class="search-col">
                            <label class="form-label small text-muted fw-bold">Search Student</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white border-end-0 text-muted">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input type="text" name="search" class="form-control border-start-0"
                                    placeholder="Search by name, email, roll, enrollment..."
                                    value="{{ request('search') }}">
                            </div>
                        </div>

                        <!-- Course Filter -->
                        <div>
                            <label class="form-label small text-muted fw-bold">Course</label>
                            <select name="course_id" id="course_id" class="form-select form-select-sm">
                                <option value="">All Courses</option>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                                        {{ $course->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Batch Filter -->
                        <div>
                            <label class="form-label small text-muted fw-bold">Batch</label>
                            <select name="batch_id" id="batch_id" class="form-select form-select-sm" {{ request('course_id') ? '' : 'disabled' }}>
                                <option value="">{{ request('course_id') ? 'All Batches' : 'Select Course First' }}</option>
                                @foreach($batches as $batch)
                                    <option value="{{ $batch->id }}" {{ request('batch_id') == $batch->id ? 'selected' : '' }}>{{ $batch->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Session Filter -->
                        <div>
                            <label class="form-label small text-muted fw-bold">Session</label>
                            <select name="session_id" id="session_id" class="form-select form-select-sm" {{ request('batch_id') ? '' : 'disabled' }}>
                                <option value="">{{ request('batch_id') ? 'All Sessions' : 'Select Batch First' }}</option>
                                @foreach($sessions as $session)
                                    <option value="{{ $session->id }}" {{ request('session_id') == $session->id ? 'selected' : '' }}>{{ $session->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Semester Filter -->
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

                        <!-- Action Buttons -->
                        <div class="actions-col d-flex gap-2">
                            <button type="submit" class="btn btn-sm btn-primary w-100 shadow-sm">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <a href="{{ route('admin.users.students') }}" class="btn btn-sm btn-light border w-100">
                                Reset
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 fw-bold">Enrolled Students</h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.users.students.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-user-plus me-2"></i>Add Student
                    </a>
                    <a href="{{ route('admin.users.students.import.show') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-file-import me-2"></i>Import Students
                    </a>
                    <a href="{{ route('admin.users.students.export', request()->query()) }}" class="btn btn-success btn-sm">
                        <i class="fas fa-file-export me-2"></i>Export Students
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Roll No</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Batch</th>
                                <th>Course & Sem</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td class="ps-4">{{ $user->studentProfile->roll_no ?? 'N/A' }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm me-3">
                                                <img src="{{ $user->getProfilePhotoUrl() }}" alt="{{ $user->name }}"
                                                    class="rounded-circle"
                                                    style="width: 40px; height: 40px; object-fit: cover; border: 2px solid #eee;">
                                            </div>
                                            <div>
                                                <div class="fw-bold">{{ $user->name }}</div>
                                                <small
                                                    class="text-muted">{{ $user->studentProfile->university_registration_number ?? 'No Registration No.' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        @if($user->studentProfile?->batch)
                                            <span class="fw-semibold">{{ $user->studentProfile->batch->name }}</span>
                                        @else
                                            <span class="text-muted small">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($user->studentProfile)
                                            <span class="text-dark">{{ $user->studentProfile->course->name ?? 'N/A' }}</span><br>
                                            <small class="text-muted">{{ $user->studentProfile->semester->name ?? '' }}</small>
                                            <span class="badge bg-secondary ms-1">{{ $user->studentProfile->section->name ?? 'N/A' }}</span>
                                        @else
                                            <span class="text-muted small">Not Linked</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($user->is_profile_complete)
                                            <span
                                                class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1">Active</span>
                                        @else
                                            <span
                                                class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-2 py-1">Pending</span>
                                        @endif
                                        @if(! $user->is_active)
                                            <span
                                                class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1">Blocked</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4 text-nowrap">
                                        <a href="{{ route('admin.attendance.monthly', ['student_id' => $user->id]) }}"
                                            class="btn btn-sm btn-light text-warning me-1" title="View Attendance Report">
                                            <i class="fas fa-calendar-check"></i>
                                        </a>
                                        <a href="{{ route('admin.users.students.show', $user->id) }}"
                                            class="btn btn-sm btn-light text-info me-1" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.users.students.edit', $user->id) }}"
                                            class="btn btn-sm btn-light text-primary me-1" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.users.toggle_active', $user->id) }}" method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('{{ $user->is_active ? 'Block this student? They will be logged out immediately.' : 'Unblock this student?' }}')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                class="btn btn-sm btn-light {{ $user->is_active ? 'text-danger' : 'text-success' }} me-1"
                                                title="{{ $user->is_active ? 'Block' : 'Unblock' }}">
                                                <i class="fas {{ $user->is_active ? 'fa-ban' : 'fa-unlock' }}"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.users.students.destroy', $user->id) }}" method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('Are you sure you want to delete this student?')">
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
                                    <td colspan="7" class="text-center py-4 text-muted">No students found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($users->hasPages())
                <div class="card-footer bg-white border-0 py-3">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const courseSelect = document.getElementById('course_id');
    const batchSelect = document.getElementById('batch_id');
    const sessionSelect = document.getElementById('session_id');
    const semesterSelect = document.getElementById('semester_id');
    const sectionSelect = document.getElementById('section_id');

    function semesterLabel(semester) {
        const name = semester.name || ('Semester ' + (semester.semester_number || ''));
        return semester.part ? `${name} (${semester.part})` : name;
    }

    function resetBatchSelect(placeholder = 'Select Course First') {
        batchSelect.innerHTML = `<option value="">${placeholder}</option>`;
        batchSelect.disabled = placeholder === 'Select Course First';
    }

    function resetSessionSelect(placeholder = 'Select Batch First') {
        sessionSelect.innerHTML = `<option value="">${placeholder}</option>`;
        sessionSelect.disabled = placeholder === 'Select Batch First';
    }

    function resetSemesterSelect(placeholder = 'Select Course First') {
        semesterSelect.innerHTML = `<option value="">${placeholder}</option>`;
        semesterSelect.disabled = placeholder === 'Select Course First';
    }

    function resetSectionSelect(placeholder = 'Select Semester First') {
        sectionSelect.innerHTML = `<option value="">${placeholder}</option>`;
        sectionSelect.disabled = placeholder === 'Select Semester First';
    }

    courseSelect.addEventListener('change', function () {
        const courseId = this.value;
        resetSessionSelect();
        resetSectionSelect();

        if (!courseId) {
            resetBatchSelect();
            resetSemesterSelect();
            return;
        }

        resetBatchSelect('Loading...');
        batchSelect.disabled = true;
        resetSemesterSelect('Loading...');
        semesterSelect.disabled = true;

        fetch("{{ route('admin.academy.courses.batches_json', ['course_id' => '__ID__']) }}".replace('__ID__', courseId))
            .then(response => response.json())
            .then(data => {
                resetBatchSelect('All Batches');
                batchSelect.disabled = false;
                (data.batches || []).forEach(batch => {
                    batchSelect.innerHTML += `<option value="${batch.id}">${batch.name}</option>`;
                });
            })
            .catch(() => resetBatchSelect('Error loading batches'));

        fetch("{{ route('admin.academy.courses.semesters_json', ['course_id' => '__COURSE_ID__']) }}".replace('__COURSE_ID__', courseId))
            .then(response => response.json())
            .then(data => {
                resetSemesterSelect('All Semesters');
                semesterSelect.disabled = false;
                (data.semesters || []).forEach(semester => {
                    semesterSelect.innerHTML += `<option value="${semester.id}">${semesterLabel(semester)}</option>`;
                });
            })
            .catch(() => resetSemesterSelect('Error loading semesters'));
    });

    batchSelect.addEventListener('change', function () {
        const batchId = this.value;
        if (!batchId) {
            resetSessionSelect();
            return;
        }

        resetSessionSelect('Loading...');
        sessionSelect.disabled = true;

        fetch("{{ route('admin.academy.batches.sessions_json', ['batch_id' => '__ID__']) }}".replace('__ID__', batchId))
            .then(response => response.json())
            .then(data => {
                resetSessionSelect('All Sessions');
                sessionSelect.disabled = false;
                (data.sessions || []).forEach(session => {
                    sessionSelect.innerHTML += `<option value="${session.id}">${session.name}</option>`;
                });
            })
            .catch(() => resetSessionSelect('Error loading sessions'));
    });

    semesterSelect.addEventListener('change', function () {
        const semesterId = this.value;
        if (!semesterId) {
            resetSectionSelect();
            return;
        }

        resetSectionSelect('Loading...');
        sectionSelect.disabled = true;

        fetch("{{ route('admin.academy.semesters.sections_json', ['semester_id' => '__SEMESTER_ID__']) }}".replace('__SEMESTER_ID__', semesterId))
            .then(response => response.json())
            .then(data => {
                resetSectionSelect('All Sections');
                sectionSelect.disabled = false;
                (data.sections || []).forEach(section => {
                    sectionSelect.innerHTML += `<option value="${section.id}">${section.name}</option>`;
                });
            })
            .catch(() => resetSectionSelect('Error loading sections'));
    });
</script>
@endpush