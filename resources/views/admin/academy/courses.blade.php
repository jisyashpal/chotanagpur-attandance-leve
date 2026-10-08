@extends('layouts.admin')

@section('title', 'Academic Courses')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <i class="fas fa-chevron-right text-muted mx-2" style="font-size: 0.8rem;"></i>
    <span class="text-primary">Courses</span>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 border-top border-primary border-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-bold">Add New Course</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.academy.courses.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Course Name</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. LL.B (3 Years)" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Course Code</label>
                            <input type="text" name="code" class="form-control" placeholder="e.g. LLB3" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-plus me-2"></i>Create Course
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-bold">Available Courses</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">#</th>
                                    <th>Course Name</th>
                                    <th>Code</th>
                                    <th>Semesters</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($courses as $course)
                                <tr>
                                    <td class="ps-4">{{ ($courses->currentPage()-1) * $courses->perPage() + $loop->iteration }}</td>
                                    <td class="fw-bold">{{ $course->name }}</td>
                                    <td><span class="badge bg-primary-subtle text-primary">{{ $course->code }}</span></td>
                                    <td>{{ $course->semesters_count ?? 0 }}</td>
                                     <td class="text-end pe-4">
                                         <button type="button" class="btn btn-sm btn-light text-primary me-2 edit-course-btn" 
                                                 data-id="{{ $course->id }}" 
                                                 data-name="{{ $course->name }}" 
                                                 data-code="{{ $course->code }}" 
                                                 title="Edit Course">
                                             <i class="fas fa-edit"></i>
                                         </button>
                                         <form action="{{ route('admin.academy.courses.destroy', $course->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this course? This will delete all related semesters, subjects, and student profiles.')">
                                             @csrf
                                             @method('DELETE')
                                             <button type="submit" class="btn btn-sm btn-light text-danger" title="Delete Course">
                                                 <i class="fas fa-trash"></i>
                                             </button>
                                         </form>
                                     </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No courses found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($courses->hasPages())
                <div class="card-footer bg-white border-0 py-3">
                    {{ $courses->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Edit Course Modal -->
<div class="modal fade" id="editCourseModal" tabindex="-1" aria-labelledby="editCourseModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark bg-gradient text-white">
                <h5 class="modal-title fw-bold" id="editCourseModalLabel">Edit Academic Course</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editCourseForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Course Name</label>
                        <input type="text" name="name" id="edit_course_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Course Code</label>
                        <input type="text" name="code" id="edit_course_code" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Update Course</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editModal = new bootstrap.Modal(document.getElementById('editCourseModal'));
    const editForm = document.getElementById('editCourseForm');
    const editNameInput = document.getElementById('edit_course_name');
    const editCodeInput = document.getElementById('edit_course_code');

    document.querySelectorAll('.edit-course-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const name = this.dataset.name;
            const code = this.dataset.code;

            editForm.action = "{{ route('admin.academy.courses.update', ':id') }}".replace(':id', id);
            editNameInput.value = name;
            editCodeInput.value = code;

            editModal.show();
        });
    });
});
</script>
@endsection
