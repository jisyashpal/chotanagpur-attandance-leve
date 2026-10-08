@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('breadcrumb')
<span class="text-primary">Dashboard</span>
@endsection

@section('content')
@php
$maxTrend = max(1, collect($attendanceTrend)->flatMap(fn ($d) => [$d['students'], $d['staff']])->max());
@endphp

<div class="container-fluid admin-dashboard">
    {{-- Hero --}}
    <div class="dash-hero mb-4">
        <div class="dash-hero-content d-flex flex-column flex-lg-row justify-content-between gap-3">
            <div>
                <div class="text-white-50 small fw-semibold mb-1 text-uppercase" style="letter-spacing: 0.06em;">
                    CNLC Attendance Portal
                </div>
                <h2>Welcome back, {{ auth()->user()->name }}</h2>
                <p>Monitor attendance, manage users, and keep academic operations running smoothly from one place.</p>
                <div class="dash-hero-meta">
                    <span class="dash-chip">
                        <i class="fas fa-user-shield"></i>
                        Administrator
                    </span>
                    <span class="dash-chip">
                        <i class="fas fa-signal"></i>
                        {{ $stats['active_logins'] }} active logins
                    </span>
                </div>
            </div>
            <div class="d-flex align-items-end gap-2 flex-wrap">
                <a href="{{ route('admin.users.students') }}" class="btn btn-light btn-sm fw-semibold">
                    <i class="fas fa-users me-1"></i> Students
                </a>
                <a href="{{ route('admin.attendance.monthly') }}" class="btn btn-outline-light btn-sm fw-semibold">
                    <i class="fas fa-chart-bar me-1"></i> Reports
                </a>
            </div>
        </div>
    </div>

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <a href="{{ route('admin.users.students') }}" class="text-decoration-none">
                <div class="stat-card">
                    <div class="card-body">
                        <div class="stat-icon students"><i class="fas fa-user-graduate"></i></div>
                        <div>
                            <div class="stat-label">Students</div>
                            <p class="stat-value">{{ number_format($stats['students']) }}</p>
                            <div class="stat-sub">{{ $stats['pending_profiles'] }} pending profiles</div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-xl-3">
            <a href="{{ route('admin.users.staff_faculty') }}" class="text-decoration-none">
                <div class="stat-card">
                    <div class="card-body">
                        <div class="stat-icon faculty"><i class="fas fa-chalkboard-teacher"></i></div>
                        <div>
                            <div class="stat-label">Faculty</div>
                            <p class="stat-value">{{ number_format($stats['faculty']) }}</p>
                            <div class="stat-sub">{{ $stats['courses'] }} courses</div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-xl-3">
            <a href="{{ route('admin.users.staff_faculty', ['role' => 'staff']) }}" class="text-decoration-none">
                <div class="stat-card">
                    <div class="card-body">
                        <div class="stat-icon staff"><i class="fas fa-user-tie"></i></div>
                        <div>
                            <div class="stat-label">Staff</div>
                            <p class="stat-value">{{ number_format($stats['staff']) }}</p>
                            <div class="stat-sub">{{ $stats['active_sessions'] }} active session(s)</div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-6 col-xl-3">
            <a href="{{ route('admin.leaves.index') }}" class="text-decoration-none">
                <div class="stat-card">
                    <div class="card-body">
                        <div class="stat-icon leaves"><i class="fas fa-file-alt"></i></div>
                        <div>
                            <div class="stat-label">Pending Leaves</div>
                            <p class="stat-value">{{ number_format($stats['pending_leaves']) }}</p>
                            <div class="stat-sub">Awaiting review</div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="card-body">
                    <div class="stat-icon present"><i class="fas fa-check-circle"></i></div>
                    <div>
                        <div class="stat-label">Students Today</div>
                        <p class="stat-value">{{ number_format($stats['today_student_present']) }}</p>
                        <div class="stat-sub">Marked present</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="stat-card">
                <div class="card-body">
                    <div class="stat-icon sessions"><i class="fas fa-fingerprint"></i></div>
                    <div>
                        <div class="stat-label">Staff / Faculty Today</div>
                        <p class="stat-value">{{ number_format($stats['today_staff_present']) }}</p>
                        <div class="stat-sub">Checked in</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12 col-xl-6">
            <div class="panel-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="quick-grid">
                        <a href="{{ route('admin.users.students.import.show') }}" class="quick-link">
                            <i class="fas fa-file-import"></i>
                            <span>Import Students</span>
                        </a>
                        <a href="{{ route('admin.attendance.students') }}" class="quick-link">
                            <i class="fas fa-calendar-check"></i>
                            <span>Student Attendance</span>
                        </a>
                        <a href="{{ route('admin.academy.subjects') }}" class="quick-link">
                            <i class="fas fa-layer-group"></i>
                            <span>Subject Allocation</span>
                        </a>
                        <a href="{{ route('admin.notifications.create') }}" class="quick-link">
                            <i class="fas fa-bell"></i>
                            <span>Send Notification</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        {{-- Attendance trend --}}
        <div class="col-xl-5">
            <div class="panel-card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="card-title">7-Day Attendance</h5>
                    <div class="d-flex gap-3 small text-muted">
                        <span><span class="legend-dot" style="background:#c02236"></span> Students</span>
                        <span><span class="legend-dot" style="background:#3b82f6"></span> Staff/Faculty</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="trend-bars">
                        @foreach($attendanceTrend as $day)
                        <div class="trend-col" title="{{ $day['date'] }} — Students: {{ $day['students'] }}, Staff: {{ $day['staff'] }}">
                            <div class="trend-stack">
                                <div class="trend-bar students" style="height: {{ max(4, ($day['students'] / $maxTrend) * 100) }}%"></div>
                                <div class="trend-bar staff" style="height: {{ max(4, ($day['staff'] / $maxTrend) * 100) }}%"></div>
                            </div>
                            <div class="trend-label">{{ $day['label'] }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Pending leaves --}}
        <div class="col-xl-4">
            <div class="panel-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title">Pending Leave Requests</h5>
                    <a href="{{ route('admin.leaves.index') }}" class="small text-decoration-none fw-semibold" style="color:#c02236;">View all</a>
                </div>
                <div class="card-body">
                    @forelse($pendingLeaves as $leave)
                    <div class="list-item-soft">
                        <div class="d-flex align-items-center gap-2 min-w-0">
                            <div class="avatar-initial">{{ strtoupper(substr($leave->user->name ?? 'U', 0, 1)) }}</div>
                            <div class="min-w-0">
                                <div class="fw-semibold text-truncate">{{ $leave->user->name ?? 'Unknown' }}</div>
                                <small class="text-muted">
                                    {{ optional($leave->from_date)->format('d M') }}
                                    –
                                    {{ optional($leave->to_date)->format('d M Y') }}
                                </small>
                            </div>
                        </div>
                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25">Pending</span>
                    </div>
                    @empty
                    <div class="empty-state">
                        <div><i class="fas fa-inbox"></i></div>
                        <div>No pending leave requests</div>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Holidays --}}
        <div class="col-xl-3">
            <div class="panel-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title">Upcoming Holidays</h5>
                    <a href="{{ route('admin.holidays.index') }}" class="small text-decoration-none fw-semibold" style="color:#c02236;">Manage</a>
                </div>
                <div class="card-body">
                    @forelse($upcomingHolidays as $holiday)
                    <div class="list-item-soft">
                        <div>
                            <div class="fw-semibold">{{ $holiday->name }}</div>
                            <small class="text-muted">{{ optional($holiday->date)->format('D, d M Y') }}</small>
                        </div>
                    </div>
                    @empty
                    <div class="empty-state py-3">
                        <div class="small">No upcoming holidays</div>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection