@extends('layouts.admin')

@section('title', 'Staff & Faculty List')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <i class="fas fa-chevron-right text-muted mx-2" style="font-size: 0.8rem;"></i>
    <span class="text-primary">Staff & Faculty</span>
@endsection

@section('content')
    <div class="container-fluid">
        <!-- Filter and Search Form -->
        <form action="{{ route('admin.users.staff_faculty') }}" method="GET" class="mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="filter-card-grid">
                        <!-- Search Input -->
                        <div class="search-col">
                            <label class="form-label small text-muted fw-bold">Search Member</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white border-end-0 text-muted">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input type="text" name="search" class="form-control border-start-0"
                                    placeholder="Search by name, email, employee ID..."
                                    value="{{ request('search') }}">
                            </div>
                        </div>

                        <!-- Role Filter -->
                        <div>
                            <label class="form-label small text-muted fw-bold">Role</label>
                            <select name="role" class="form-select form-select-sm">
                                <option value="">All Roles</option>
                                <option value="faculty" {{ request('role') == 'faculty' ? 'selected' : '' }}>Faculty</option>
                                <option value="staff" {{ request('role') == 'staff' ? 'selected' : '' }}>Staff</option>
                            </select>
                        </div>

                        <!-- Department Filter -->
                        <div>
                            <label class="form-label small text-muted fw-bold">Department</label>
                            <select name="department" class="form-select form-select-sm">
                                <option value="">All Departments</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Action Buttons -->
                        <div class="actions-col d-flex gap-2">
                            <button type="submit" class="btn btn-sm btn-primary w-100 shadow-sm">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <a href="{{ route('admin.users.staff_faculty') }}" class="btn btn-sm btn-light border w-100">
                                Reset
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 fw-bold">Staff & Faculty Members</h5>
                <button class="btn btn-primary btn-sm">
                    <i class="fas fa-user-plus me-2"></i>Add Member
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">#</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td class="ps-4">{{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm me-2">
                                                <img src="{{ $user->getProfilePhotoUrl() }}" alt="{{ $user->name }}"
                                                    class="rounded-circle"
                                                    style="width: 32px; height: 32px; object-fit: cover; border: 1px solid #eee;">
                                            </div>
                                            <span class="fw-bold">{{ $user->name }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        <span
                                            class="badge {{ $user->role === 'faculty' ? 'bg-info-subtle text-info' : 'bg-warning-subtle text-warning' }} border px-3">
                                            {{ ucfirst($user->role) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($user->is_profile_complete)
                                            <span class="badge bg-success-subtle text-success px-2">Complete</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary px-2">Incomplete</span>
                                        @endif
                                        @if(! $user->is_active)
                                            <span class="badge bg-danger-subtle text-danger px-2">Blocked</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group">
                                            <a href="{{ route('admin.attendance.monthly', ['user_id' => $user->id]) }}"
                                                class="btn btn-sm btn-light text-warning me-2" title="View Attendance Report">
                                                <i class="fas fa-calendar-check"></i>
                                            </a>
                                            <a href="{{ route('admin.users.staff_faculty.show', $user->id) }}"
                                                class="btn btn-sm btn-light text-info me-2" title="View Profile">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.users.staff_faculty.edit', $user->id) }}"
                                                class="btn btn-sm btn-light text-primary me-2" title="Edit Profile">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.users.toggle_active', $user->id) }}"
                                                method="POST" class="d-inline"
                                                onsubmit="return confirm('{{ $user->is_active ? 'Block this user? They will be logged out immediately.' : 'Unblock this user?' }}')">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                    class="btn btn-sm btn-light {{ $user->is_active ? 'text-danger' : 'text-success' }} me-2"
                                                    title="{{ $user->is_active ? 'Block' : 'Unblock' }}">
                                                    <i class="fas {{ $user->is_active ? 'fa-ban' : 'fa-unlock' }}"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.users.staff_faculty.destroy', $user->id) }}"
                                                method="POST" class="d-inline"
                                                onsubmit="return confirm('Are you sure you want to delete this user?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-light text-danger"
                                                    title="Delete User">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No staff or faculty members found.</td>
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