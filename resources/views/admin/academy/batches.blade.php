@extends('layouts.admin')

@section('title', 'Batches')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <i class="fas fa-chevron-right text-muted mx-2" style="font-size: 0.8rem;"></i>
    <span class="text-primary">Batches</span>
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

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 border-top border-primary border-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-bold">Add New Batch</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.academy.batches.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Course</label>
                            <select name="course_id" class="form-select" required>
                                <option value="">Select Course</option>
                                @foreach ($courses as $course)
                                    <option value="{{ $course->id }}">{{ $course->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Batch Name</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. 2024-27" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Start Year</label>
                            <input type="number" name="start_year" class="form-control" placeholder="e.g. 2024" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">End Year</label>
                            <input type="number" name="end_year" class="form-control" placeholder="e.g. 2027" required>
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive" checked>
                                <label class="form-check-label" for="isActive">Active Batch</label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-plus me-2"></i>Create Batch
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-bold">Existing Batches</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">#</th>
                                    <th>Batch</th>
                                    <th>Course</th>
                                    <th>Years</th>
                                    <th>Sessions</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($batches as $batch)
                                <tr>
                                    <td class="ps-4">{{ ($batches->currentPage()-1) * $batches->perPage() + $loop->iteration }}</td>
                                    <td class="fw-bold">{{ $batch->name }}</td>
                                    <td>{{ $batch->course->name ?? 'N/A' }}</td>
                                    <td>{{ $batch->start_year }}–{{ $batch->end_year }}</td>
                                    <td>{{ $batch->sessions_count }}</td>
                                    <td>
                                        @if($batch->is_active)
                                            <span class="badge bg-success-subtle text-success px-3">Active</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary px-3">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <button type="button" class="btn btn-sm btn-light text-primary me-2 edit-batch-btn"
                                            data-id="{{ $batch->id }}"
                                            data-course="{{ $batch->course_id }}"
                                            data-name="{{ $batch->name }}"
                                            data-start="{{ $batch->start_year }}"
                                            data-end="{{ $batch->end_year }}"
                                            data-active="{{ $batch->is_active ? 1 : 0 }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="{{ route('admin.academy.batches.destroy', $batch->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this batch?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-light text-danger"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">No batches found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($batches->hasPages())
                <div class="card-footer bg-white border-0 py-3">{{ $batches->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editBatchModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold">Edit Batch</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editBatchForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Course</label>
                        <select name="course_id" id="edit_batch_course" class="form-select" required>
                            @foreach ($courses as $course)
                                <option value="{{ $course->id }}">{{ $course->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Batch Name</label>
                        <input type="text" name="name" id="edit_batch_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Start Year</label>
                        <input type="number" name="start_year" id="edit_batch_start" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">End Year</label>
                        <input type="number" name="end_year" id="edit_batch_end" class="form-control" required>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="edit_batch_active">
                        <label class="form-check-label" for="edit_batch_active">Active Batch</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Update Batch</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = new bootstrap.Modal(document.getElementById('editBatchModal'));
    const form = document.getElementById('editBatchForm');
    document.querySelectorAll('.edit-batch-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            form.action = "{{ route('admin.academy.batches.update', ':id') }}".replace(':id', this.dataset.id);
            document.getElementById('edit_batch_course').value = this.dataset.course;
            document.getElementById('edit_batch_name').value = this.dataset.name;
            document.getElementById('edit_batch_start').value = this.dataset.start;
            document.getElementById('edit_batch_end').value = this.dataset.end;
            document.getElementById('edit_batch_active').checked = this.dataset.active == 1;
            modal.show();
        });
    });
});
</script>
@endsection
