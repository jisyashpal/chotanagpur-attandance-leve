@extends('layouts.admin')

@section('title', 'View Profile - ' . $user->name)

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <i class="fas fa-chevron-right text-muted mx-2" style="font-size: 0.8rem;"></i>
    <a href="{{ route('admin.users.students') }}">Student List</a>
    <i class="fas fa-chevron-right text-muted mx-2" style="font-size: 0.8rem;"></i>
    <span class="text-primary">View Student Profile</span>
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
                            class="badge {{ $user->is_profile_complete ? 'bg-success bg-opacity-10 text-success border border-success border-opacity-25' : 'bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25' }} px-3 py-2">
                            {{ $user->is_profile_complete ? 'Profile Complete' : 'Incomplete Profile' }}
                        </span>
                    </div>
                    <div class="card-footer bg-white border-top-0 pb-4">
                        <div class="d-grid gap-2">
                            <a href="{{ route('admin.users.students.edit', $user->id) }}" class="btn btn-primary shadow-sm">
                                <i class="fas fa-edit me-2"></i>Edit Profile
                            </a>
                            <a href="{{ route('admin.attendance.monthly', ['student_id' => $user->id]) }}" class="btn btn-outline-primary shadow-sm">
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
                        <h5 class="card-title mb-0 fw-bold">Student Profile Details</h5>
                    </div>
                    <div class="card-body">
                        @php
                            $profile = $user->studentProfile;
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
                                <div class="col-sm-4 text-muted">Batch</div>
                                <div class="col-sm-8 fw-bold">{{ $profile->batch->name ?? 'N/A' }}</div>
                            </div>
                            <div class="row mb-4">
                                <div class="col-sm-4 text-muted">Academic Session</div>
                                <div class="col-sm-8 fw-bold">{{ $profile->session->name ?? 'N/A' }}</div>
                            </div>
                            <div class="row mb-4">
                                <div class="col-sm-4 text-muted">Course</div>
                                <div class="col-sm-8 fw-bold">{{ $profile->course->name ?? 'N/A' }}</div>
                            </div>
                            <div class="row mb-4">
                                <div class="col-sm-4 text-muted">Semester</div>
                                <div class="col-sm-8 fw-bold">{{ $profile->semester->name ?? 'N/A' }}</div>
                            </div>
                            <div class="row mb-4">
                                <div class="col-sm-4 text-muted">Section</div>
                                <div class="col-sm-8 fw-bold">{{ $profile->section->name ?? 'N/A' }}</div>
                            </div>
                            <div class="row mb-4">
                                <div class="col-sm-4 text-muted">University Registration Number</div>
                                <div class="col-sm-8 fw-semibold text-primary">{{ $profile->university_registration_number ?? 'N/A' }}</div>
                            </div>
                            <div class="row mb-4">
                                <div class="col-sm-4 text-muted">Roll No</div>
                                <div class="col-sm-8 fw-bold">{{ $profile->roll_no ?? 'N/A' }}</div>
                            </div>
                            <div class="row mb-4">
                                <div class="col-sm-4 text-muted">Father Name</div>
                                <div class="col-sm-8 fw-bold">{{ $profile->father_name ?? 'N/A' }}</div>
                            </div>
                            <div class="row mb-4">
                                <div class="col-sm-4 text-muted">Mother Name</div>
                                <div class="col-sm-8 fw-bold">{{ $profile->mother_name ?? 'N/A' }}</div>
                            </div>
                            <div class="row mb-4">
                                <div class="col-sm-4 text-muted">Gender</div>
                                <div class="col-sm-8 fw-bold">{{ ucfirst($profile->gender ?? 'N/A') }}</div>
                            </div>
                            <div class="row mb-4">
                                <div class="col-sm-4 text-muted">Date of Birth</div>
                                <div class="col-sm-8 fw-bold">
                                    @if($profile->dob)
                                        {{ is_string($profile->dob) ? \Carbon\Carbon::parse($profile->dob)->format('d M Y') : $profile->dob->format('d M Y') }}
                                    @else
                                        N/A
                                    @endif
                                </div>
                            </div>
                            <div class="row mb-4">
                                <div class="col-sm-4 text-muted">Mobile No</div>
                                <div class="col-sm-8 fw-bold">{{ $profile->phone_no ?? 'N/A' }}</div>
                            </div>
                            <div class="row mb-4">
                                <div class="col-sm-4 text-muted">WhatsApp No</div>
                                <div class="col-sm-8 fw-bold">{{ $profile->whatsapp_no ?? 'N/A' }}</div>
                            </div>
                            <div class="row mb-4">
                                <div class="col-sm-4 text-muted">Aadhar No</div>
                                <div class="col-sm-8 fw-bold">{{ $profile->aadhar_no ?? 'N/A' }}</div>
                            </div>
                            <div class="row mb-4">
                                <div class="col-sm-4 text-muted">Blood Group</div>
                                <div class="col-sm-8 fw-bold">{{ $profile->blood_group ?? 'N/A' }}</div>
                            </div>
                            <div class="row mb-4">
                                <div class="col-sm-4 text-muted">Degree Obtained College/University</div>
                                <div class="col-sm-8 fw-bold">{{ $profile->degree_obtained_college ?? 'N/A' }}</div>
                            </div>
                            <div class="row mb-4">
                                <div class="col-sm-4 text-muted">Religion</div>
                                <div class="col-sm-8 fw-bold">{{ $profile->religion ?? 'N/A' }}</div>
                            </div>
                            <div class="row mb-4">
                                <div class="col-sm-4 text-muted">Category</div>
                                <div class="col-sm-8 fw-bold">{{ $profile->category ?? 'N/A' }}</div>
                            </div>
                            <div class="row mb-4">
                                <div class="col-sm-4 text-muted">Caste</div>
                                <div class="col-sm-8 fw-bold">{{ $profile->caste ?? 'N/A' }}</div>
                            </div>
                            <div class="row mb-4">
                                <div class="col-sm-4 text-muted">Permanent Address</div>
                                <div class="col-sm-8 fw-bold">{{ $profile->address ?? 'N/A' }}</div>
                            </div>
                            <div class="row mb-4">
                                <div class="col-sm-4 text-muted">Local Address</div>
                                <div class="col-sm-8 fw-bold">{{ $profile->local_address ?? 'N/A' }}</div>
                            </div>
                            <div class="row mb-4">
                                <div class="col-sm-4 text-muted">Nationality</div>
                                <div class="col-sm-8 fw-bold">{{ $profile->nationality ?? 'N/A' }}</div>
                            </div>
                        @else
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-info-circle mb-3 d-block fa-3x text-light"></i>
                                <h6 class="fw-bold mb-1">Incomplete Profile</h6>
                                <p class="text-muted small mb-0">Student details haven't been completed yet by the user.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card shadow-sm border-0 mt-4">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 fw-bold">Recent Activity</h5>
                        <a href="{{ route('admin.activity_logs.index', ['search' => $user->name]) }}" class="btn btn-sm btn-outline-primary">View all logs</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table mb-0 align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-3">When</th>
                                        <th>Action</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($activityLogs as $log)
                                        <tr>
                                            <td class="ps-3 small">{{ $log->created_at?->format('d M Y H:i') }}</td>
                                            <td><span class="badge bg-primary-subtle text-primary">{{ $log->action }}</span></td>
                                            <td>{{ $log->description }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-3">No activity yet.</td>
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