@extends('layouts.admin')

@section('title', 'Academic Sections')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <i class="fas fa-chevron-right text-muted mx-2" style="font-size: 0.8rem;"></i>
    <span class="text-primary">Sections</span>
@endsection

@section('content')
<div class="container-fluid">
    <form action="{{ route('admin.academy.sections') }}" method="GET" class="mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="filter-card-grid">
                    <div class="search-col">
                        <label class="form-label small text-muted fw-bold">Search Sections</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0 text-muted">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" name="search" class="form-control border-start-0"
                                placeholder="Search by name, code or course..."
                                value="{{ request('search') }}">
                        </div>
                    </div>

                    <div>
                        <label class="form-label small text-muted fw-bold">Course</label>
                        <select name="course_id" id="filter_course_id" class="form-select form-select-sm">
                            <option value="">All Courses</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                                    {{ $course->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="form-label small text-muted fw-bold">Semester</label>
                        <select name="semester_id" id="filter_semester_id" class="form-select form-select-sm">
                            <option value="">All Semesters</option>
                            @foreach($semesters as $semester)
                                <option value="{{ $semester->id }}"
                                    data-course-id="{{ $semester->course_id }}"
                                    {{ request('semester_id') == $semester->id ? 'selected' : '' }}>
                                    {{ $semester->course->name ?? 'N/A' }} - {{ $semester->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="actions-col d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-primary w-100 shadow-sm">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <a href="{{ route('admin.academy.sections') }}" class="btn btn-sm btn-light border w-100">
                            Reset
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 border-top border-primary border-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-bold">Add New Section</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.academy.sections.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Course</label>
                            <select id="course_id" class="form-select" required>
                                <option value="">Choose Course</option>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}">{{ $course->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Semester</label>
                            <select name="semester_id" id="semester_id" class="form-select" required>
                                <option value="">Select Course First</option>
                                @foreach($semesters as $semester)
                                    <option value="{{ $semester->id }}" data-course-id="{{ $semester->course_id }}">{{ $semester->course->name }} - {{ $semester->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Section Name</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Section A" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Section Code</label>
                            <input type="text" name="code" class="form-control" placeholder="e.g. A" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-plus me-2"></i>Create Section
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-bold">Existing Sections</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">#</th>
                                    <th>Course</th>
                                    <th>Semester</th>
                                    <th>Section</th>
                                    <th>Code</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sections as $section)
                                <tr>
                                    <td class="ps-4">{{ ($sections->currentPage()-1) * $sections->perPage() + $loop->iteration }}</td>
                                    <td>{{ $section->semester->course->name ?? 'N/A' }}</td>
                                    <td>{{ $section->semester->name ?? 'N/A' }}</td>
                                    <td class="fw-bold">{{ $section->name }}</td>
                                    <td><span class="badge bg-secondary-subtle text-dark border">{{ $section->code }}</span></td>
                                    <td class="text-end pe-4">
                                        <button type="button" class="btn btn-sm btn-light text-primary me-2 edit-section-btn"
                                            data-id="{{ $section->id }}"
                                            data-semester-id="{{ $section->semester_id }}"
                                            data-name="{{ $section->name }}"
                                            data-code="{{ $section->code }}"
                                            title="Edit Section">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="{{ route('admin.academy.sections.destroy', $section->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this section?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-light text-danger" title="Delete Section">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No sections found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($sections->hasPages())
                <div class="card-footer bg-white border-0 py-3">
                    {{ $sections->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editSectionModal" tabindex="-1" aria-labelledby="editSectionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark bg-gradient text-white">
                <h5 class="modal-title fw-bold" id="editSectionModalLabel">Edit Section</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editSectionForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Semester</label>
                        <select name="semester_id" id="edit_semester_id" class="form-select" required>
                            @foreach($semesters as $semester)
                                <option value="{{ $semester->id }}">{{ $semester->course->name }} - {{ $semester->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Section Name</label>
                        <input type="text" name="name" id="edit_section_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Section Code</label>
                        <input type="text" name="code" id="edit_section_code" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Update Section</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function cascadeSemesters(courseSelect, semesterSelect, emptyLabel, allowAllWhenEmpty) {
        const allOptions = Array.from(semesterSelect.querySelectorAll('option[data-course-id]'));

        function apply(courseId, preserveSemesterId) {
            const placeholder = (!courseId && allowAllWhenEmpty) ? emptyLabel : (courseId ? 'Select Semester' : emptyLabel);
            semesterSelect.innerHTML = `<option value="">${placeholder}</option>`;

            allOptions.forEach(option => {
                if ((allowAllWhenEmpty && !courseId) || (courseId && option.dataset.courseId === String(courseId))) {
                    const clone = option.cloneNode(true);
                    if (preserveSemesterId && clone.value === String(preserveSemesterId)) {
                        clone.selected = true;
                    }
                    semesterSelect.appendChild(clone);
                }
            });
        }

        courseSelect.addEventListener('change', function() {
            apply(this.value, null);
        });

        apply(courseSelect.value || null, null);
        return apply;
    }

    const filterApply = cascadeSemesters(
        document.getElementById('filter_course_id'),
        document.getElementById('filter_semester_id'),
        'All Semesters',
        true
    );
    if (document.getElementById('filter_course_id').value) {
        filterApply(document.getElementById('filter_course_id').value, "{{ request('semester_id') }}");
    }

    const allSemesters = Array.from(document.querySelectorAll('#semester_id option[data-course-id]'));
    const courseSelect = document.getElementById('course_id');
    const semesterSelect = document.getElementById('semester_id');

    function filterSemestersByCourse(courseId) {
        semesterSelect.innerHTML = '<option value="">Select Semester</option>';
        if (!courseId) {
            semesterSelect.innerHTML = '<option value="">Select Course First</option>';
            return;
        }

        allSemesters.forEach(option => {
            if (option.dataset.courseId === String(courseId)) {
                semesterSelect.appendChild(option.cloneNode(true));
            }
        });
    }

    courseSelect.addEventListener('change', function() {
        filterSemestersByCourse(this.value);
    });

    const editModal = new bootstrap.Modal(document.getElementById('editSectionModal'));
    const editForm = document.getElementById('editSectionForm');
    const editSemesterInput = document.getElementById('edit_semester_id');
    const editNameInput = document.getElementById('edit_section_name');
    const editCodeInput = document.getElementById('edit_section_code');

    document.querySelectorAll('.edit-section-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const semesterId = this.dataset.semesterId;
            const name = this.dataset.name;
            const code = this.dataset.code;

            editForm.action = "{{ route('admin.academy.sections.update', ':id') }}".replace(':id', id);
            editSemesterInput.value = semesterId;
            editNameInput.value = name;
            editCodeInput.value = code;

            editModal.show();
        });
    });
});
</script>
@endsection
