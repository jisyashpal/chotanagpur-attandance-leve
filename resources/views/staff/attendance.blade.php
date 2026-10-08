@extends('layouts.admin')

@section('title', 'Staff Attendance Logs')

@section('content')
<div class="container-fluid px-0">
    <!-- Header banner -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card bg-info text-white border-0 shadow-sm">
                <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <h3 class="fw-bold mb-1"><i class="fas fa-calendar-check me-2"></i>Attendance History</h3>
                        <p class="mb-0 text-white opacity-75">View and track your daily check-in and check-out logs</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats row -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted fw-medium small">Attendance Ratio</span>
                        <div class="bg-info bg-opacity-10 p-2 rounded text-info">
                            <i class="fas fa-chart-pie fa-lg"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold mb-2">{{ $attendancePercentage }}%</h2>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-info" role="progressbar" style="width: {{ $attendancePercentage }}%" aria-valuenow="{{ $attendancePercentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted fw-medium small">Total Days</span>
                        <div class="bg-info bg-opacity-10 p-2 rounded text-info">
                            <i class="fas fa-business-time fa-lg"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold mb-2">{{ $totalDays }}</h2>
                    <span class="text-muted small">Total working days</span>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted fw-medium small">Days Present</span>
                        <div class="bg-success bg-opacity-10 p-2 rounded text-success">
                            <i class="fas fa-check-double fa-lg"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold mb-2 text-success">{{ $presentDays }}</h2>
                    <span class="text-muted small">On-time & Late days</span>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted fw-medium small">Late Incomings</span>
                        <div class="bg-danger bg-opacity-10 p-2 rounded text-danger">
                            <i class="fas fa-clock fa-lg"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold mb-2 text-danger">{{ $lateDays }}</h2>
                    <span class="text-muted small">Arrived after hours</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Logs list -->
    <div class="row">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
                    <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-list me-2 text-info"></i>Detailed Attendance Logs</h5>
                    <span class="badge bg-light text-dark">{{ $attendances->total() }} Records</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4" style="width: 20%;">Date</th>
                                    <th style="width: 20%;">Location</th>
                                    <th class="text-center" style="width: 15%;">Check In</th>
                                    <th class="text-center" style="width: 15%;">Check Out</th>
                                    <th class="text-center" style="width: 15%;">Work Duration</th>
                                    <th class="text-center" style="width: 15%;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($attendances as $attendance)
                                    <tr>
                                        <td class="ps-4 fw-semibold text-dark">
                                            {{ \Carbon\Carbon::parse($attendance->date)->format('d M, Y') }}
                                        </td>
                                        <td class="text-muted">
                                            <i class="fas fa-map-marker-alt text-danger me-1"></i>
                                            {{ $attendance->location->name ?? 'N/A' }}
                                        </td>
                                        <td class="text-center fw-medium text-success">
                                            {{ $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('h:i A') : 'N/A' }}
                                        </td>
                                        <td class="text-center fw-medium text-secondary">
                                            {{ $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('h:i A') : 'N/A' }}
                                        </td>
                                        <td class="text-center text-muted fw-bold">
                                            @if($attendance->check_in && $attendance->check_out)
                                                @php
                                                    $start = \Carbon\Carbon::parse($attendance->check_in);
                                                    $end = \Carbon\Carbon::parse($attendance->check_out);
                                                    $duration = $start->diff($end)->format('%h h %i m');
                                                @endphp
                                                {{ $duration }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if(strtolower($attendance->status) === 'present')
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill fw-bold">
                                                    <i class="fas fa-check-circle me-1"></i> Present
                                                </span>
                                            @elseif(strtolower($attendance->status) === 'late')
                                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-3 py-2 rounded-pill fw-bold">
                                                    <i class="fas fa-exclamation-circle me-1"></i> Late
                                                </span>
                                            @else
                                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-2 rounded-pill fw-bold">
                                                    <i class="fas fa-times-circle me-1"></i> Absent
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <div class="mb-3">
                                                <i class="fas fa-calendar-times fa-3x text-light"></i>
                                            </div>
                                            <h6 class="fw-bold mb-1">No Attendance Records Found</h6>
                                            <p class="text-muted small mb-0">No daily work logs have been submitted yet.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($attendances->hasPages())
                    <div class="card-footer bg-white border-0 py-3">
                        <div class="d-flex justify-content-center">
                            {{ $attendances->links() }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
