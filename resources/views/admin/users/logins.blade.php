@extends('layouts.admin')

@section('title', 'Active Login Sessions')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <i class="fas fa-chevron-right text-muted mx-2" style="font-size: 0.8rem;"></i>
    <span class="text-primary">Active Logins</span>
@endsection

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 fw-bold text-dark">
                <i class="fas fa-shield-alt me-2 text-primary"></i>Active User Sessions
            </h5>
            <span class="badge bg-primary-subtle text-primary border px-3">
                Total Sessions: {{ $sessions->total() }}
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted text-uppercase small fw-bold">
                        <tr>
                            <th class="ps-4">User</th>
                            <th>Device Info</th>
                            <th>Platform</th>
                            <th>Last Active</th>
                            <th>Created At</th>
                            <th class="text-end pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sessions as $session)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle me-2 bg-light d-flex align-items-center justify-content-center" style="width: 35px; height: 35px; border-radius: 50%;">
                                        <i class="fas fa-user text-primary" style="font-size: 0.9rem;"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $session->user_name }}</div>
                                        <div class="small text-muted">{{ $session->user_email }}</div>
                                        <span class="badge bg-secondary-subtle text-secondary x-small px-2" style="font-size: 0.7rem;">
                                            {{ ucfirst($session->user_role) }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($session->device_name)
                                    <div class="fw-medium text-dark">{{ $session->device_name }}</div>
                                    <div class="small text-muted font-monospace">{{ $session->name }}</div>
                                @else
                                    <span class="text-muted italic">Unknown Device</span>
                                    <div class="small text-muted font-monospace">{{ $session->name }}</div>
                                @endif
                            </td>
                            <td>
                                @php
                                    $platform = strtolower($session->platform ?? 'unknown');
                                    $icon = 'laptop';
                                    $color = 'secondary';
                                    if(str_contains($platform, 'android')) { $icon = 'mobile-alt'; $color = 'success'; }
                                    elseif(str_contains($platform, 'ios')) { $icon = 'apple-alt'; $color = 'dark'; }
                                    elseif(str_contains($platform, 'web')) { $icon = 'globe'; $color = 'info'; }
                                @endphp
                                <span class="badge bg-{{ $color }}-subtle text-{{ $color }} border px-2">
                                    <i class="fas fa-{{ $icon }} me-1"></i> {{ ucfirst($platform) }}
                                </span>
                            </td>
                            <td>
                                <div class="text-dark">{{ $session->last_used_at ? \Carbon\Carbon::parse($session->last_used_at)->diffForHumans() : 'Never' }}</div>
                                <div class="small text-muted">{{ $session->last_used_at ? \Carbon\Carbon::parse($session->last_used_at)->format('d M, h:i A') : '-' }}</div>
                            </td>
                            <td>
                                <div class="text-muted small">{{ \Carbon\Carbon::parse($session->created_at)->format('d M Y') }}</div>
                                <div class="text-muted x-small">{{ \Carbon\Carbon::parse($session->created_at)->format('h:i A') }}</div>
                            </td>
                            <td class="text-end pe-4">
                                <form action="{{ route('admin.users.active_logins.revoke', $session->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to revoke this session? The user will be logged out from this device.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger border-0">
                                        <i class="fas fa-sign-out-alt me-1"></i> Revoke
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-history fa-3x mb-3 opacity-25"></i>
                                    <p class="mb-0">No active login sessions found.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($sessions->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $sessions->links() }}
        </div>
        @endif
    </div>
</div>

<style>
    .x-small { font-size: 0.75rem; }
    .table thead th { border-top: none; }
    .avatar-circle { flex-shrink: 0; }
    .btn-outline-danger:hover { background-color: #fff5f5; color: #dc3545; }
</style>
@endsection
