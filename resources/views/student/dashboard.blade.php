@extends('layouts.admin')

@section('title', 'Student Dashboard')

@section('content')
<div class="container-fluid px-0">
    <!-- Header banner -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card bg-primary text-white border-0 shadow-sm">
                <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <h3 class="fw-bold mb-1">Welcome, {{ auth()->user()->name }}!</h3>
                        <p class="mb-0 text-white-50"><i class="fas fa-graduation-cap me-1"></i> Student Attendance Portal</p>
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
                    <span class="text-muted small">Total lectures held</span>
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

    <!-- Subjectwise breakdown -->
    <div class="row">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
                    <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-book-open me-2 text-primary"></i>Subject-wise Attendance Breakdown</h5>
                    <span class="badge bg-light text-dark">{{ count($subjectSummary) }} Subjects</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Subject Code</th>
                                    <th>Subject Name</th>
                                    <th class="text-center">Total Lectures</th>
                                    <th class="text-center">Attended</th>
                                    <th class="text-center">Missed</th>
                                    <th class="text-center" style="width: 25%;">Attendance Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($subjectSummary as $subject)
                                    <tr>
                                        <td class="ps-4 fw-semibold text-primary">{{ $subject['code'] }}</td>
                                        <td>{{ $subject['name'] }}</td>
                                        <td class="text-center">{{ $subject['total'] }}</td>
                                        <td class="text-center text-success fw-medium">{{ $subject['present'] }}</td>
                                        <td class="text-center text-danger fw-medium">{{ $subject['absent'] }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="progress flex-grow-1 me-3" style="height: 8px;">
                                                    <div class="progress-bar {{ $subject['percentage'] >= 75 ? 'bg-success' : ($subject['percentage'] >= 60 ? 'bg-warning' : 'bg-danger') }}" 
                                                         role="progressbar" 
                                                         style="width: {{ $subject['percentage'] }}%" 
                                                         aria-valuenow="{{ $subject['percentage'] }}" 
                                                         aria-valuemin="0" 
                                                         aria-valuemax="100"></div>
                                                </div>
                                                <span class="fw-bold small {{ $subject['percentage'] >= 75 ? 'text-success' : ($subject['percentage'] >= 60 ? 'text-warning' : 'text-danger') }}">
                                                    {{ $subject['percentage'] }}%
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            <i class="fas fa-info-circle me-1"></i> No attendance record or subjects found for this semester.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
