@extends('layouts.admin')

@section('title', 'Staff & Faculty Attendance Report')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <i class="fas fa-chevron-right text-muted mx-2" style="font-size: 0.8rem;"></i>
    <span class="text-primary">Staff & Faculty Attendance</span>
@endsection

@section('content')
<div class="container-fluid">
    <form action="{{ route('admin.attendance.staff_faculty') }}" method="GET" class="mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0 fw-bold">Filter Attendance</h5>
            </div>
            <div class="card-body">
                <div class="filter-card-grid">
                    <div class="search-col">
                        <label class="form-label small text-muted fw-bold">Search Member</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0 text-muted">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" name="search" class="form-control border-start-0"
                                placeholder="Search by name, email..."
                                value="{{ request('search') }}">
                        </div>
                    </div>

                    <div>
                        <label class="form-label small text-muted fw-bold">Select Member</label>
                        <select name="user_id" class="form-select form-select-sm">
                            <option value="">All Members</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ ucfirst($user->role) }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="form-label small text-muted fw-bold">Date</label>
                        <input type="date" name="date" class="form-control form-control-sm" value="{{ request('date') }}">
                    </div>

                    <div class="actions-col">
                        <button type="submit" class="btn btn-sm btn-primary w-100 shadow-sm">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <a href="{{ route('admin.attendance.staff_faculty') }}" class="btn btn-sm btn-light border w-100">
                            Reset
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <form action="{{ route('admin.attendance.monthly_export') }}" method="GET" class="mb-4">
        <input type="hidden" name="scope" value="staff_faculty">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0 fw-bold">Staff & Faculty Attendance Export</h5>
            </div>
            <div class="card-body">
                <div class="filter-card-grid">
                    <div>
                        <label class="form-label small text-muted fw-bold">Month</label>
                        <input type="number" name="month" min="1" max="12" class="form-control form-control-sm" value="{{ request('month', now()->month) }}" required>
                    </div>
                    <div>
                        <label class="form-label small text-muted fw-bold">Year</label>
                        <input type="number" name="year" min="2000" max="2100" class="form-control form-control-sm" value="{{ request('year', now()->year) }}" required>
                    </div>
                    <div>
                        <label class="form-label small text-muted fw-bold">Role</label>
                        <select name="role" class="form-select form-select-sm">
                            <option value="">All Staff & Faculty</option>
                            <option value="faculty" {{ request('role') === 'faculty' ? 'selected' : '' }}>Faculty</option>
                            <option value="staff" {{ request('role') === 'staff' ? 'selected' : '' }}>Staff</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label small text-muted fw-bold">Select Member</label>
                        <select name="user_id" class="form-select form-select-sm">
                            <option value="">All Members</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ (string) request('user_id') === (string) $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ ucfirst($user->role) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label small text-muted fw-bold">Format</label>
                        <select name="format" class="form-select form-select-sm">
                            <option value="csv">CSV (Download)</option>
                            <option value="json">JSON</option>
                        </select>
                    </div>
                    <div class="actions-col">
                        <button type="submit" class="btn btn-sm btn-success w-100 shadow-sm">
                            <i class="fas fa-file-export"></i> Export
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    @if($selectedUser && $stats)
    <div class="card shadow-sm border-0 mb-4 overflow-hidden">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <!-- User Profile Info -->
                <div class="col-lg-4 border-end border-light pe-lg-4 mb-3 mb-lg-0">
                    <div class="d-flex align-items-center">
                        <div class="avatar-lg me-3">
                            <img src="{{ $selectedUser->getProfilePhotoUrl() }}" alt="{{ $selectedUser->name }}"
                                class="rounded-circle border border-2 border-primary shadow-sm"
                                style="width: 70px; height: 70px; object-fit: cover;">
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1">{{ $selectedUser->name }}</h5>
                            <p class="text-muted small mb-1">
                                <i class="fas fa-envelope me-1 text-secondary"></i> {{ $selectedUser->email }}
                            </p>
                            <span class="badge {{ $selectedUser->role === 'faculty' ? 'bg-info text-white' : 'bg-warning text-dark' }} px-3 py-1 text-uppercase fw-bold" style="font-size: 0.75rem;">
                                {{ ucfirst($selectedUser->role) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="col-lg-8 ps-lg-4">
                    <div class="row g-3">
                        <div class="col-3">
                            <div class="p-3 bg-light rounded-3 text-center h-100 shadow-sm border border-light">
                                <span class="text-muted small d-block mb-1 text-uppercase fw-bold" style="font-size: 0.65rem; letter-spacing: 0.5px;">Total Days</span>
                                <h4 class="fw-bold mb-0 text-dark">{{ $stats['total'] }}</h4>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="p-3 bg-success-subtle rounded-3 text-center h-100 shadow-sm border border-success-subtle">
                                <span class="text-success small d-block mb-1 text-uppercase fw-bold" style="font-size: 0.65rem; letter-spacing: 0.5px;">Present</span>
                                <h4 class="fw-bold mb-0 text-success text-opacity-75">{{ $stats['present'] }}</h4>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="p-3 bg-info-subtle rounded-3 text-center h-100 shadow-sm border border-info-subtle">
                                <span class="text-info small d-block mb-1 text-uppercase fw-bold" style="font-size: 0.65rem; letter-spacing: 0.5px;">Leave</span>
                                <h4 class="fw-bold mb-0 text-info">{{ $stats['leave'] }}</h4>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="p-3 bg-danger-subtle rounded-3 text-center h-100 shadow-sm border border-danger-subtle">
                                <span class="text-danger small d-block mb-1 text-uppercase fw-bold" style="font-size: 0.65rem; letter-spacing: 0.5px;">Absent</span>
                                <h4 class="fw-bold mb-0 text-danger text-opacity-75">{{ $stats['absent'] }}</h4>
                            </div>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="mt-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small text-muted fw-semibold">Attendance Rate (Present / Present + Absent)</span>
                            <span class="badge {{ $stats['percentage'] >= 75 ? 'bg-success' : ($stats['percentage'] >= 50 ? 'bg-warning text-dark' : 'bg-danger') }} fw-bold px-2 py-1">
                                {{ $stats['percentage'] }}%
                            </span>
                        </div>
                        <div class="progress" style="height: 10px; border-radius: 5px;">
                            <div class="progress-bar {{ $stats['percentage'] >= 75 ? 'bg-success' : ($stats['percentage'] >= 50 ? 'bg-warning' : 'bg-danger') }}" 
                                 role="progressbar" 
                                 style="width: {{ $stats['percentage'] }}%" 
                                 aria-valuenow="{{ $stats['percentage'] }}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 fw-bold">
                @if($selectedUser)
                    Attendance History: {{ $selectedUser->name }}
                @else
                    Attendance Records
                @endif
            </h5>
            @if($selectedUser)
                <a href="{{ route('admin.attendance.staff_faculty') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-times-circle me-1"></i> Clear Member Filter
                </a>
            @endif
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">#</th>
                            <th>Member</th>
                            <th>Role</th>
                            <th>Date</th>
                            <th>Check-in</th>
                            <th>Check-out</th>
                            <th>Location</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $attendance)
                        <tr>
                            <td class="ps-4">{{ ($attendances->currentPage()-1) * $attendances->perPage() + $loop->iteration }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm me-2">
                                        <img src="{{ $attendance->user->getProfilePhotoUrl() }}" class="rounded-circle" style="width: 32px; height: 32px; object-fit: cover;">
                                    </div>
                                    <span class="fw-bold">{{ $attendance->user->name }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge {{ $attendance->user->role === 'faculty' ? 'bg-info-subtle text-info' : 'bg-warning-subtle text-warning' }} px-3">
                                    {{ ucfirst($attendance->user->role) }}
                                </span>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($attendance->date)->format('d M, Y') }}</td>
                            <td>{{ $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('h:i A') : 'N/A' }}</td>
                            <td>{{ $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('h:i A') : 'N/A' }}</td>
                            <td>
                                @if($attendance->location)
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold text-dark">{{ $attendance->location->name }}</span>
                                        <small class="text-muted" style="font-size: 0.75rem;">
                                            <i class="fas fa-map-marker-alt text-danger me-1"></i>
                                            {{ $attendance->latitude }}, {{ $attendance->longitude }}
                                        </small>
                                    </div>
                                @else
                                    <small class="text-muted">
                                        <i class="fas fa-map-marker-alt text-danger me-1"></i>
                                        {{ $attendance->latitude }}, {{ $attendance->longitude }}
                                    </small>
                                @endif
                            </td>
                            <td>
                                @if(($attendance->display_status ?? $attendance->status) === 'present')
                                    <span class="badge bg-success text-white px-3">Present</span>
                                @elseif(($attendance->display_status ?? $attendance->status) === 'leave')
                                    <span class="badge bg-info text-white px-3">Leave</span>
                                @else
                                    <span class="badge bg-secondary text-white px-3">Absent</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">No attendance records found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($attendances->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $attendances->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
