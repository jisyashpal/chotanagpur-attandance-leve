@extends('layouts.admin')

@section('title', 'Notices')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <i class="fas fa-chevron-right text-muted mx-2" style="font-size: 0.8rem;"></i>
    <span class="text-primary">Notices</span>
@endsection

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
            <h5 class="card-title mb-0 fw-bold"><i class="fas fa-bullhorn text-primary me-2"></i> Notices Management</h5>
            <a href="{{ route('admin.notices.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i> Add New Notice
            </a>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Title</th>
                            <th>Target Role</th>
                            <th>File</th>
                            <th>Link</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($notices as $notice)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold">{{ $notice->title }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-soft-{{ $notice->target_role == 'all' ? 'primary' : ($notice->target_role == 'student' ? 'success' : 'info') }} text-{{ $notice->target_role == 'all' ? 'primary' : ($notice->target_role == 'student' ? 'success' : 'info') }}">
                                        {{ ucfirst($notice->target_role) }}
                                    </span>
                                </td>
                                <td>
                                    @if($notice->file_path)
                                        <a href="{{ asset('public/' . $notice->file_path) }}" target="_blank" class="btn btn-sm btn-outline-info">
                                            <i class="fas fa-file-download"></i>
                                        </a>
                                    @else
                                        <span class="text-muted small">No file</span>
                                    @endif
                                </td>
                                <td>
                                    @if($notice->link)
                                        <a href="{{ $notice->link }}" target="_blank" class="text-primary small text-truncate d-inline-block" style="max-width: 150px;">
                                            {{ $notice->link }}
                                        </a>
                                    @else
                                        <span class="text-muted small">No link</span>
                                    @endif
                                </td>
                                <td>
                                    @if($notice->is_active)
                                        <span class="badge bg-success small">Active</span>
                                    @else
                                        <span class="badge bg-secondary small">Inactive</span>
                                    @endif
                                </td>
                                <td class="small text-muted">
                                    {{ $notice->created_at->format('M d, Y') }}
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group">
                                        <a href="{{ route('admin.notices.edit', $notice) }}" class="btn btn-sm btn-outline-primary border-0">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.notices.destroy', $notice) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this notice?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger border-0">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <img src="{{ asset('assets/img/illustrations/empty.svg') }}" alt="No data" style="width: 150px;" class="mb-3 d-none">
                                    <p class="text-muted mb-0">No notices found.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($notices->hasPages())
            <div class="card-footer bg-white border-top">
                {{ $notices->links() }}
            </div>
        @endif
    </div>
</div>

<style>
    .bg-soft-primary { background-color: rgba(13, 110, 253, 0.1); }
    .bg-soft-success { background-color: rgba(25, 135, 84, 0.1); }
    .bg-soft-info { background-color: rgba(13, 202, 240, 0.1); }
    .table-hover tbody tr:hover { background-color: #f8f9fa; }
</style>
@endsection
