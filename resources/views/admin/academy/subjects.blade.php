@extends('layouts.admin')

@section('title', 'Academic Subjects')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <i class="fas fa-chevron-right text-muted mx-2" style="font-size: 0.8rem;"></i>
    <span class="text-primary">Subjects</span>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Add New Subject -->
        <div class="col-md-12">
            <div class="card shadow-sm border-0 border-top border-primary border-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-bold">Add New Subject</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.academy.subjects.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Course</label>
                                <select id="add_course_id" class="form-select" required>
                                    <option value="">Choose Course</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}">{{ $course->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Semester</label>
                                <select name="semester_id" id="add_semester_id" class="form-select" required>
                                    <option value="">Select Course First</option>
                                    @foreach($semesters as $semester)
                                        <option value="{{ $semester->id }}" data-course-id="{{ $semester->course_id }}">
                                            {{ $semester->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Subject Name</label>
                                <input type="text" name="name" class="form-control" placeholder="e.g. Constitutional Law" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Subject Code</label>
                                <input type="text" name="code" class="form-control" placeholder="e.g. LAW101" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Create Subject
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Filter and Search Form -->
        <div class="col-md-12">
            <form action="{{ route('admin.academy.subjects') }}" method="GET">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="filter-card-grid">
                            <!-- Search Input -->
                            <div class="search-col">
                                <label class="form-label small text-muted fw-bold">Search Subjects</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white border-end-0 text-muted">
                                        <i class="fas fa-search"></i>
                                    </span>
                                    <input type="text" name="search" class="form-control border-start-0"
                                        placeholder="Search by name, code or course..."
                                        value="{{ request('search') }}">
                                </div>
                            </div>

                            <!-- Course Filter -->
                            <div>
                                <label class="form-label small text-muted fw-bold">Course</label>
                                <select name="course_id" id="filter_course_id" class="form-select form-select-sm">
                                    <option value="">All Courses</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                                            {{ $course->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Semester Filter -->
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

                            <!-- Action Buttons -->
                            <div class="actions-col d-flex gap-2">
                                <button type="submit" class="btn btn-sm btn-primary w-100 shadow-sm">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                                <a href="{{ route('admin.academy.subjects') }}" class="btn btn-sm btn-light border w-100">
                                    Reset
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Existing Subjects -->
        <div class="col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-bold">Existing Subjects</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">#</th>
                                    <th>Subject</th>
                                    <th>Code</th>
                                    <th>Course & Sem</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($subjects as $subject)
                                <tr>
                                    <td class="ps-4">{{ ($subjects->currentPage()-1) * $subjects->perPage() + $loop->iteration }}</td>
                                    <td class="fw-bold">{{ $subject->name }}</td>
                                    <td><span class="badge bg-secondary-subtle text-dark border">{{ $subject->code }}</span></td>
                                    <td class="small">{{ $subject->semester->course->name }} - {{ $subject->semester->name }}</td>
                                     <td class="text-end pe-4">
                                         <button type="button" class="btn btn-sm btn-light text-primary me-2 edit-subject-btn" 
                                                 data-id="{{ $subject->id }}" 
                                                 data-course-id="{{ $subject->semester->course_id }}"
                                                 data-semester-id="{{ $subject->semester_id }}" 
                                                 data-name="{{ $subject->name }}" 
                                                 data-code="{{ $subject->code }}"
                                                 title="Edit Subject">
                                             <i class="fas fa-edit"></i>
                                         </button>
                                         <form action="{{ route('admin.academy.subjects.destroy', $subject->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this subject? This will delete all related attendance logs and allocations.')">
                                             @csrf
                                             @method('DELETE')
                                             <button type="submit" class="btn btn-sm btn-light text-danger" title="Delete Subject">
                                                 <i class="fas fa-trash"></i>
                                             </button>
                                         </form>
                                     </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No subjects found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($subjects->hasPages())
                <div class="card-footer bg-white border-0 py-3">
                    {{ $subjects->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Edit Subject Modal -->
<div class="modal fade" id="editSubjectModal" tabindex="-1" aria-labelledby="editSubjectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark bg-gradient text-white">
                <h5 class="modal-title fw-bold" id="editSubjectModalLabel">Edit Academic Subject</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editSubjectForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Course</label>
                        <select id="edit_subject_course_id" class="form-select" required>
                            <option value="">Choose Course</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}">{{ $course->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Semester</label>
                        <select name="semester_id" id="edit_subject_semester_id" class="form-select" required>
                            <option value="">Select Course First</option>
                            @foreach($semesters as $semester)
                                <option value="{{ $semester->id }}" data-course-id="{{ $semester->course_id }}">
                                    {{ $semester->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subject Name</label>
                        <input type="text" name="name" id="edit_subject_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subject Code</label>
                        <input type="text" name="code" id="edit_subject_code" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Update Subject</button>
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

    // Filter bar
    const filterApply = cascadeSemesters(
        document.getElementById('filter_course_id'),
        document.getElementById('filter_semester_id'),
        'All Semesters',
        true
    );
    if (document.getElementById('filter_course_id').value) {
        filterApply(document.getElementById('filter_course_id').value, "{{ request('semester_id') }}");
    }

    // Add New Subject form
    cascadeSemesters(
        document.getElementById('add_course_id'),
        document.getElementById('add_semester_id'),
        'Select Course First',
        false
    );

    // Edit Subject modal
    const editCourse = document.getElementById('edit_subject_course_id');
    const editSemester = document.getElementById('edit_subject_semester_id');
    const editApply = cascadeSemesters(editCourse, editSemester, 'Select Course First', false);

    const editModal = new bootstrap.Modal(document.getElementById('editSubjectModal'));
    const editForm = document.getElementById('editSubjectForm');
    const editNameInput = document.getElementById('edit_subject_name');
    const editCodeInput = document.getElementById('edit_subject_code');

    document.querySelectorAll('.edit-subject-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const courseId = this.dataset.courseId;
            const semesterId = this.dataset.semesterId;

            editForm.action = "{{ route('admin.academy.subjects.update', ':id') }}".replace(':id', id);
            editCourse.value = courseId;
            editApply(courseId, semesterId);
            editNameInput.value = this.dataset.name;
            editCodeInput.value = this.dataset.code;

            editModal.show();
        });
    });
});
</script>
@endsection
