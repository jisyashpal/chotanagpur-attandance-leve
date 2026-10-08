@extends('layouts.admin')

@section('title', 'Academic Sessions')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <i class="fas fa-chevron-right text-muted mx-2" style="font-size: 0.8rem;"></i>
    <span class="text-primary">Academic Sessions</span>
@endsection

@section('content')
<div class="container-fluid">
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 border-top border-primary border-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-bold">Add New Session</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.academy.sessions.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Batch</label>
                            <select name="batch_id" class="form-select" required>
                                <option value="">Select Batch</option>
                                @foreach ($batches as $batch)
                                    <option value="{{ $batch->id }}">{{ $batch->name }} ({{ $batch->course->name ?? 'N/A' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Session Name</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. 2024-25" required>
                            <small class="text-muted">Example: 2024-25, 2025-26</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Session Year</label>
                            <input type="number" name="session_year" class="form-control" placeholder="e.g. 2024" required>
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive">
                                <label class="form-check-label" for="isActive">Set as Active Session</label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-plus me-2"></i>Create Session
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-bold">Existing Sessions</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">#</th>
                                    <th>Session</th>
                                    <th>Batch</th>
                                    <th>Year</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sessions as $session)
                                <tr>
                                    <td class="ps-4">{{ ($sessions->currentPage()-1) * $sessions->perPage() + $loop->iteration }}</td>
                                    <td class="fw-bold">{{ $session->name }}</td>
                                    <td>{{ $session->batch->name ?? 'N/A' }} <small class="text-muted">({{ $session->batch->course->name ?? '' }})</small></td>
                                    <td>{{ $session->session_year }}</td>
                                    <td>
                                        @if($session->is_active)
                                            <span class="badge bg-success-subtle text-success px-3">Active</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary px-3">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <button type="button" class="btn btn-sm btn-light text-primary me-2 edit-session-btn"
                                            data-id="{{ $session->id }}"
                                            data-batch="{{ $session->batch_id }}"
                                            data-name="{{ $session->name }}"
                                            data-year="{{ $session->session_year }}"
                                            data-active="{{ $session->is_active ? 1 : 0 }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="{{ route('admin.academy.sessions.destroy', $session->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this session?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-light text-danger"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No sessions found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($sessions->hasPages())
                <div class="card-footer bg-white border-0 py-3">{{ $sessions->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editSessionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold">Edit Academic Session</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editSessionForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Batch</label>
                        <select name="batch_id" id="edit_session_batch" class="form-select" required>
                            @foreach ($batches as $batch)
                                <option value="{{ $batch->id }}">{{ $batch->name }} ({{ $batch->course->name ?? 'N/A' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Session Name</label>
                        <input type="text" name="name" id="edit_session_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Session Year</label>
                        <input type="number" name="session_year" id="edit_session_year" class="form-control" required>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="edit_session_active">
                        <label class="form-check-label" for="edit_session_active">Set as Active Session</label>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Update Session</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const editModal = new bootstrap.Modal(document.getElementById('editSessionModal'));
    const editForm = document.getElementById('editSessionForm');
    document.querySelectorAll('.edit-session-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            editForm.action = "{{ route('admin.academy.sessions.update', ':id') }}".replace(':id', this.dataset.id);
            document.getElementById('edit_session_batch').value = this.dataset.batch;
            document.getElementById('edit_session_name').value = this.dataset.name;
            document.getElementById('edit_session_year').value = this.dataset.year;
            document.getElementById('edit_session_active').checked = this.dataset.active == 1;
            editModal.show();
        });
    });
});
</script>
@endsection
