@extends('layouts.admin')

@section('title', 'Edit Notice')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <i class="fas fa-chevron-right text-muted mx-2" style="font-size: 0.8rem;"></i>
    <a href="{{ route('admin.notices.index') }}">Notices</a>
    <i class="fas fa-chevron-right text-muted mx-2" style="font-size: 0.8rem;"></i>
    <span class="text-primary">Edit Notice</span>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="card-title mb-0 fw-bold"><i class="fas fa-edit text-primary me-2"></i> Edit Notice</h5>
                </div>
                
                <div class="card-body p-4">
                    <form action="{{ route('admin.notices.update', $notice) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-4">
                            <label for="title" class="form-label small fw-bold">Notice Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" required placeholder="Enter notice title" value="{{ old('title', $notice->title) }}">
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="target_role" class="form-label small fw-bold">Target Audience <span class="text-danger">*</span></label>
                                <select class="form-select @error('target_role') is-invalid @enderror" id="target_role" name="target_role" required>
                                    <option value="all" {{ old('target_role', $notice->target_role) == 'all' ? 'selected' : '' }}>All Roles</option>
                                    <option value="student" {{ old('target_role', $notice->target_role) == 'student' ? 'selected' : '' }}>Students</option>
                                    <option value="faculty" {{ old('target_role', $notice->target_role) == 'faculty' ? 'selected' : '' }}>Faculty/Teachers</option>
                                    <option value="staff" {{ old('target_role', $notice->target_role) == 'staff' ? 'selected' : '' }}>Staff</option>
                                </select>
                                @error('target_role')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ $notice->is_active ? 'checked' : '' }}>
                                    <label class="form-check-label small fw-bold" for="is_active">Mark as Active</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="file" class="form-label small fw-bold">Attachment (Optional)</label>
                            @if($notice->file_path)
                                <div class="d-flex align-items-center mb-2 p-2 bg-light rounded">
                                    <i class="fas fa-file-alt text-primary me-2"></i>
                                    <span class="small text-truncate flex-grow-1">{{ basename($notice->file_path) }}</span>
                                    <a href="{{ asset('public/' . $notice->file_path) }}" target="_blank" class="btn btn-sm btn-link py-0 text-decoration-none">View</a>
                                </div>
                            @endif
                            <input type="file" class="form-control @error('file') is-invalid @enderror" id="file" name="file">
                            <div class="form-text small text-muted">Leave empty to keep current file. Max size: 5MB.</div>
                            @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="link" class="form-label small fw-bold">External Link (Optional)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-link"></i></span>
                                <input type="url" class="form-control @error('link') is-invalid @enderror" id="link" name="link" placeholder="https://example.com" value="{{ old('link', $notice->link) }}">
                            </div>
                            @error('link')
                                <div class="invalid-feedback text-danger d-block small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mt-5 border-top pt-4">
                            <button type="submit" class="btn btn-primary px-5">
                                <i class="fas fa-save me-2"></i> Update Notice
                            </button>
                            <a href="{{ route('admin.notices.index') }}" class="btn btn-light px-4 ms-2 text-muted">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
