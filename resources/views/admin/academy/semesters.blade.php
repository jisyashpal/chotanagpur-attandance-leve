@extends('layouts.admin')

@section('title', 'Academic Semesters')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <i class="fas fa-chevron-right text-muted mx-2" style="font-size: 0.8rem;"></i>
    <span class="text-primary">Semesters</span>
@endsection

@section('content')
<div class="container-fluid">
    <form action="{{ route('admin.academy.semesters') }}" method="GET" class="mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="filter-card-grid">
                    <div class="search-col">
                        <label class="form-label small text-muted fw-bold">Search Semesters</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0 text-muted">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" name="search" class="form-control border-start-0"
                                placeholder="Search by name, part or course..."
                                value="{{ request('search') }}">
                        </div>
                    </div>

                    <div>
                        <label class="form-label small text-muted fw-bold">Course</label>
                        <select name="course_id" class="form-select form-select-sm">
                            <option value="">All Courses</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                                    {{ $course->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="actions-col d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-primary w-100 shadow-sm">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <a href="{{ route('admin.academy.semesters') }}" class="btn btn-sm btn-light border w-100">
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
                    <h5 class="card-title mb-0 fw-bold">Add New Semester</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.academy.semesters.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Select Course</label>
                            <select name="course_id" class="form-select" required>
                                <option value="">Choose Course</option>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}">{{ $course->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Semester Number</label>
                            <input type="number" name="semester_number" class="form-control" placeholder="e.g. 1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Semester Name</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Semester 1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Part</label>
                            <input type="text" name="part" class="form-control" placeholder="e.g. Part I" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-plus me-2"></i>Create Semester
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-bold">Existing Semesters</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">#</th>
                                    <th>Course</th>
                                    <th>Semester</th>
                                    <th>Part</th>
                                    <th>Created At</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($semesters as $semester)
                                <tr>
                                    <td class="ps-4">{{ ($semesters->currentPage()-1) * $semesters->perPage() + $loop->iteration }}</td>
                                    <td><span class="fw-bold">{{ $semester->course->name }}</span></td>
                                    <td>{{ $semester->name }}</td>
                                    <td><span class="badge bg-secondary">{{ $semester->part }}</span></td>
                                    <td>{{ $semester->created_at->format('d M, Y') }}</td>
                                     <td class="text-end pe-4">
                                         <button type="button" class="btn btn-sm btn-light text-primary me-2 edit-semester-btn" 
                                                 data-id="{{ $semester->id }}" 
                                                 data-course-id="{{ $semester->course_id }}" 
                                                 data-number="{{ $semester->semester_number }}" 
                                                 data-name="{{ $semester->name }}" 
                                                 data-part="{{ $semester->part }}"
                                                 title="Edit Semester">
                                             <i class="fas fa-edit"></i>
                                         </button>
                                         <form action="{{ route('admin.academy.semesters.destroy', $semester->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this semester? This will delete all related subjects, student records, and allocations.')">
                                             @csrf
                                             @method('DELETE')
                                             <button type="submit" class="btn btn-sm btn-light text-danger" title="Delete Semester">
                                                 <i class="fas fa-trash"></i>
                                             </button>
                                         </form>
                                     </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No semesters found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($semesters->hasPages())
                <div class="card-footer bg-white border-0 py-3">
                    {{ $semesters->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Edit Semester Modal -->
<div class="modal fade" id="editSemesterModal" tabindex="-1" aria-labelledby="editSemesterModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark bg-gradient text-white">
                <h5 class="modal-title fw-bold" id="editSemesterModalLabel">Edit Academic Semester</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editSemesterForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Select Course</label>
                        <select name="course_id" id="edit_semester_course_id" class="form-select" required>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}">{{ $course->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Semester Number</label>
                        <input type="number" name="semester_number" id="edit_semester_number" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Semester Name</label>
                        <input type="text" name="name" id="edit_semester_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Part</label>
                        <input type="text" name="part" id="edit_semester_part" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Update Semester</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editModal = new bootstrap.Modal(document.getElementById('editSemesterModal'));
    const editForm = document.getElementById('editSemesterForm');
    const editCourseInput = document.getElementById('edit_semester_course_id');
    const editNumberInput = document.getElementById('edit_semester_number');
    const editNameInput = document.getElementById('edit_semester_name');
    const editPartInput = document.getElementById('edit_semester_part');

    document.querySelectorAll('.edit-semester-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const courseId = this.dataset.courseId;
            const number = this.dataset.number;
            const name = this.dataset.name;
            const part = this.dataset.part;

            editForm.action = "{{ route('admin.academy.semesters.update', ':id') }}".replace(':id', id);
            editCourseInput.value = courseId;
            editNumberInput.value = number;
            editNameInput.value = name;
            editPartInput.value = part;

            editModal.show();
        });
    });
});
</script>
@endsection
