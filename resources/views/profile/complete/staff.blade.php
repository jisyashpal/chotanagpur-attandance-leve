@extends('layouts.admin')

@section('title', 'Complete Your Staff Profile')

@section('content')
<div class="container ">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-primary text-white p-4">
                    <h4 class="mb-0 fw-bold"><i class="fas fa-id-badge me-2"></i>{{ auth()->user()->is_profile_complete ? 'Staff Profile Details' : 'Complete Staff Profile' }}</h4>
                    <p class="mb-0 small opacity-75">Provide your information to access the staff portal.</p>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('staff.profile.complete.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row g-3">
                            <div class="col-12">
                                <h6 class="text-primary fw-bold border-bottom pb-2">Employment Details</h6>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Employee ID</label>
                                <input type="text" name="employee_id" class="form-control" placeholder="e.g. STAFF01" value="{{ old('employee_id', $profile->employee_id ?? '') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Designation</label>
                                <input type="text" name="designation" class="form-control" placeholder="e.g. Registrar" value="{{ old('designation', $profile->designation ?? '') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Department</label>
                                <input type="text" name="department" class="form-control" placeholder="e.g. Administration" value="{{ old('department', $profile->department ?? '') }}" required>
                            </div>

                            <div class="col-12 mt-4">
                                <h6 class="text-primary fw-bold border-bottom pb-2">Personal Details</h6>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone Number</label>
                                <input type="text" name="phone_no" class="form-control" value="{{ old('phone_no', $profile->phone_no ?? '') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" name="dob" class="form-control" value="{{ old('dob', $profile && $profile->dob ? (is_string($profile->dob) ? $profile->dob : $profile->dob->format('Y-m-d')) : '') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Gender</label>
                                <select name="gender" class="form-select" required>
                                    <option value="male" {{ (old('gender', $profile->gender ?? '') == 'male' || old('gender', $profile->gender ?? '') == 'Male') ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ (old('gender', $profile->gender ?? '') == 'female' || old('gender', $profile->gender ?? '') == 'Female') ? 'selected' : '' }}>Female</option>
                                    <option value="other" {{ (old('gender', $profile->gender ?? '') == 'other' || old('gender', $profile->gender ?? '') == 'Other') ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Profile Photo</label>
                                <input type="file" name="profile_photo" class="form-control" accept="image/*">
                            </div>
                        </div>

                        <div class="mt-5">
                            <button type="submit" class="btn btn-primary btn-lg w-100 shadow-sm">
                                {{ auth()->user()->is_profile_complete ? 'Update Profile' : 'Register Staff Details' }} <i class="fas fa-check-circle ms-2"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
