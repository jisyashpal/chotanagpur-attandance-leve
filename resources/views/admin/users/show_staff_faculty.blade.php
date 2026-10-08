@extends('layouts.admin')

@section('title', 'View Profile - ' . $user->name)

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <i class="fas fa-chevron-right text-muted mx-2" style="font-size: 0.8rem;"></i>
    <a href="{{ route('admin.users.staff_faculty') }}">Staff & Faculty</a>
    <i class="fas fa-chevron-right text-muted mx-2" style="font-size: 0.8rem;"></i>
    <span class="text-primary">View Profile</span>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4">
                <!-- Profile Card -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body text-center py-5">
                        <div class="mb-4">
                            <img src="{{ $user->getProfilePhotoUrl() }}" alt="{{ $user->name }}"
                                class="rounded-circle shadow-sm profile-photo-zoomable"
                                data-zoom-image="{{ $user->getProfilePhotoUrl() }}"
                                data-zoom-alt="{{ $user->name }}"
                                title="Click to zoom"
                                style="width: 120px; height: 120px; object-fit: cover; border: 4px solid #fff;">
                        </div>
                        <h4 class="fw-bold mb-1">{{ $user->name }}</h4>
                        <p class="text-muted mb-3">{{ ucfirst($user->role) }}</p>
                        <span
                            class="badge {{ $user->is_profile_complete ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }} px-3 py-2 border">
                            {{ $user->is_profile_complete ? 'Profile Complete' : 'Incomplete Profile' }}
                        </span>
                    </div>
                    <div class="card-footer bg-white border-top-0 pb-4">
                        <div class="d-grid gap-2">
                            <a href="{{ route('admin.users.staff_faculty.edit', $user->id) }}" class="btn btn-primary shadow-sm">
                                <i class="fas fa-edit me-2"></i>Edit Profile
                            </a>
                            <a href="{{ route('admin.attendance.monthly', ['user_id' => $user->id]) }}" class="btn btn-outline-primary shadow-sm">
                                <i class="fas fa-calendar-check me-2"></i>View Attendance Report
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <!-- Details Card -->
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0 fw-bold">Profile Details</h5>
                    </div>
                    <div class="card-body">
                        @php
                            $profile = $user->role === 'faculty' ? $user->facultyProfile : $user->staffProfile;
                        @endphp

                        <div class="row mb-4">
                            <div class="col-sm-4 text-muted">Full Name</div>
                            <div class="col-sm-8 fw-bold">{{ $user->name }}</div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-sm-4 text-muted">Email Address</div>
                            <div class="col-sm-8 text-primary">{{ $user->email }}</div>
                        </div>
                        <hr class="opacity-10">

                        @if($profile)
                            <div class="row mb-4">
                                <div class="col-sm-4 text-muted">Employee ID</div>
                                <div class="col-sm-8 fw-bold">{{ $profile->employee_id ?? 'N/A' }}</div>
                            </div>
                            <div class="row mb-4">
                                <div class="col-sm-4 text-muted">Designation</div>
                                <div class="col-sm-8 fw-bold">{{ $profile->designation ?? 'N/A' }}</div>
                            </div>
                            <div class="row mb-4">
                                <div class="col-sm-4 text-muted">Department</div>
                                <div class="col-sm-8 fw-bold">{{ $profile->department ?? 'N/A' }}</div>
                            </div>
                            <div class="row mb-4">
                                <div class="col-sm-4 text-muted">Phone Number</div>
                                <div class="col-sm-8 fw-bold">{{ $profile->phone_no ?? 'N/A' }}</div>
                            </div>
                            <div class="row mb-4">
                                <div class="col-sm-4 text-muted">Gender</div>
                                <div class="col-sm-8 fw-bold">{{ $profile->gender ?? 'N/A' }}</div>
                            </div>
                            <div class="row mb-4">
                                <div class="col-sm-4 text-muted">Date of Birth</div>
                                <div class="col-sm-8 fw-bold">{{ $profile->dob ? $profile->dob->format('d M Y') : 'N/A' }}</div>
                            </div>
                        @else
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-info-circle mb-2 d-block fa-2x"></i>
                                Profile details haven't been completed yet.
                            </div>
                        @endif
                    </div>
                </div>

                @if($user->role === 'faculty' && $user->facultyProfile && $user->facultyProfile->subjectAllocations->count() > 0)
                    <!-- Subject Allocations -->
                    <div class="card shadow-sm border-0 mt-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="card-title mb-0 fw-bold">Subject Allocations</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light text-muted small text-uppercase">
                                        <tr>
                                            <th class="ps-4">Subject</th>
                                            <th>Semester</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($user->facultyProfile->subjectAllocations as $allocation)
                                            <tr>
                                                <td class="ps-4">
                                                    <div class="fw-bold">{{ $allocation->subject->name }}</div>
                                                    <div class="small text-muted">{{ $allocation->subject->code }}</div>
                                                </td>
                                                <td>{{ $allocation->semester->name }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection