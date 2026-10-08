@extends('layouts.admin')

@section('title', 'Profile Settings - CNLC Attendance')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route(auth()->user()->role . '.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Profile Settings</li>
        </ol>
    </nav>
@endsection

@section('content')
<div class="container-fluid">

    <div class="row g-4">
        {{-- Profile Information --}}
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-primary py-3 border-0">
                    <h5 class="card-title mb-0 fw-bold text-white">
                        <i class="fas fa-id-card me-2"></i>Profile Information
                    </h5>
                    <p class="small mb-0 text-white text-justify">Update your account's profile information and email address.</p>
                </div>
                <div class="card-body">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>
        </div>

        {{-- Update Password --}}
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-primary py-3 border-0">
                    <h5 class="card-title mb-0 fw-bold text-white">
                        <i class="fas fa-key me-2"></i>Update Password
                    </h5>
                    <p class="small mb-0 text-white text-justify">Ensure your account is using a long, random password to stay secure.</p>
                </div>
                <div class="card-body">
                    @include('profile.partials.update-password-form')
                </div>
            </div>
        </div>

        {{-- Danger Zone / Delete Account --}}
        <!-- <div class="col-12">
            <div class="card shadow-sm border-0 border-start border-danger border-4">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="card-title mb-0 fw-bold text-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>Danger Zone
                    </h5>
                </div>
                <div class="card-body">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div> -->
    </div>
</div>
@endsection
