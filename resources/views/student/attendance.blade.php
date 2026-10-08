@extends('layouts.admin')

@section('title', 'Attendance Logs')

@section('content')
<div class="container-fluid px-0">
    <!-- Header banner -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card bg-primary text-white border-0 shadow-sm">
                <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <h3 class="fw-bold mb-1">Attendance History</h3>
                        <p class="mb-0 text-white-50"><i class="fas fa-history me-1"></i> View your detailed attendance logs</p>
                    </div>
                    @if($studentProfile)
                        <div class="d-flex gap-3 mt-3 mt-md-0 text-white-50 flex-wrap">
                            <div class="bg-white bg-opacity-10 px-3 py-2 rounded">
                                <small class="d-block text-uppercase fw-semibold" style="font-size: 0.65rem;">Roll No</small>
                                <span class="fw-bold text-white">{{ $studentProfile->roll_no ?? 'N/A' }}</span>
                            </div>
                            <div class="bg-white bg-opacity-10 px-3 py-2 rounded">
                                <small class="d-block text-uppercase fw-semibold" style="font-size: 0.65rem;">Course</small>
                                <span class="fw-bold text-white">{{ $studentProfile->course->name ?? 'N/A' }}</span>
                            </div>
                            <div class="bg-white bg-opacity-10 px-3 py-2 rounded">
                                <small class="d-block text-uppercase fw-semibold" style="font-size: 0.65rem;">Semester</small>
                                <span class="fw-bold text-white">{{ $studentProfile->semester->name ?? 'N/A' }}</span>
                            </div>
                            <div class="bg-white bg-opacity-10 px-3 py-2 rounded">
                                <small class="d-block text-uppercase fw-semibold" style="font-size: 0.65rem;">Session</small>
                                <span class="fw-bold text-white">{{ $studentProfile->session->name ?? 'N/A' }}</span>
                            </div>
                        </div>
                    @endif
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
                        <span class="text-muted fw-medium small">Overall Attendance</span>
                        <div class="bg-primary bg-opacity-10 p-2 rounded text-primary">
                            <i class="fas fa-chart-line fa-lg"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold mb-2">{{ $overallPercentage }}%</h2>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar {{ $overallPercentage >= 75 ? 'bg-success' : ($overallPercentage >= 60 ? 'bg-warning' : 'bg-danger') }}" 
                             role="progressbar" 
                             style="width: {{ $overallPercentage }}%" 
                             aria-valuenow="{{ $overallPercentage }}" 
                             aria-valuemin="0" 
                             aria-valuemax="100"></div>
                    </div>
                    <small class="text-muted d-block mt-2">
                        @if($overallPercentage >= 75)
                            <span class="text-success"><i class="fas fa-check-circle me-1"></i> Good Standing</span>
                        @else
                            <span class="text-danger"><i class="fas fa-exclamation-triangle me-1"></i> Below threshold (75%)</span>
                        @endif
                    </small>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted fw-medium small">Total Classes</span>
                        <div class="bg-info bg-opacity-10 p-2 rounded text-info">
                            <i class="fas fa-calendar-check fa-lg"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold mb-2">{{ $totalClasses }}</h2>
                    <span class="text-muted small">Total lectures recorded</span>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted fw-medium small">Classes Attended</span>
                        <div class="bg-success bg-opacity-10 p-2 rounded text-success">
                            <i class="fas fa-user-check fa-lg"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold mb-2 text-success">{{ $presentClasses }}</h2>
                    <span class="text-muted small">Lectures present</span>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted fw-medium small">Classes Missed</span>
                        <div class="bg-danger bg-opacity-10 p-2 rounded text-danger">
                            <i class="fas fa-user-times fa-lg"></i>
                        </div>
                    </div>
                    <h2 class="fw-bold mb-2 text-danger">{{ $absentClasses }}</h2>
                    <span class="text-muted small">Lectures absent</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Logs list -->
    <div class="row">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
                    <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-list me-2 text-primary"></i>Detailed Attendance Logs</h5>
                    <span class="badge bg-light text-dark">{{ $attendances->total() }} Total Records</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4" style="width: 15%;">Date</th>
                                    <th style="width: 15%;">Subject Code</th>
                                    <th style="width: 35%;">Subject Name</th>
                                    <th class="text-center" style="width: 15%;">Status</th>
                                    <th style="width: 20%;">Marked By</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($attendances as $attendance)
                                    <tr>
                                        <td class="ps-4 fw-medium text-dark">
                                            {{ \Carbon\Carbon::parse($attendance->date)->format('d M, Y') }}
                                        </td>
                                        <td class="fw-semibold text-primary">
                                            {{ $attendance->subject->code ?? 'N/A' }}
                                        </td>
                                        <td>
                                            {{ $attendance->subject->name ?? 'N/A' }}
                                        </td>
                                        <td class="text-center">
                                            @if(strtolower($attendance->status) === 'present')
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill fw-bold">
                                                    <i class="fas fa-check-circle me-1"></i> Present
                                                </span>
                                            @else
                                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-2 rounded-pill fw-bold">
                                                    <i class="fas fa-times-circle me-1"></i> Absent
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-muted">
                                            {{ $attendance->marker->name ?? 'N/A' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <div class="mb-3">
                                                <i class="fas fa-calendar-times fa-3x text-light"></i>
                                            </div>
                                            <h6 class="fw-bold mb-1">No Attendance Records Found</h6>
                                            <p class="text-muted small mb-0">You have no recorded attendance at this moment.</p>
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
