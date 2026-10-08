@extends('layouts.website')

@section('title', 'Reset Password - Chotanagpur Attendance')

@push('styles')
<link rel="stylesheet" href="{{ asset('public/css/login.css') }}">

@endpush

@section('content')
<div class="login-container">
    <div class="login-image-section">
        <a href="https://cnlawcollege.ac.in/" target="_blank" class="brand-logo" style="text-decoration: none;">
            <img src="{{ asset('public/images/cnlc_logo.webp') }}" alt="Logo" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
        </a>
        <h2 class="college-name">CHOTANAGPUR<br>COLLEGE</h2>
        <p class="college-sub text-center">AN AUTONOMOUS INSTITUTION OF RANCHI UNIVERSITY<br>B++ NAAC ACCREDITED INSTITUTION</p>
        
        <div class="mt-5 text-center position-relative" style="z-index: 1;">
            <div class="d-flex align-items-center mb-3">
                <i class="fas fa-key me-3 fa-lg" style="color: #c9951b;"></i>
                <span class="fw-medium fs-5">Set New Password</span>
            </div>
            <div class="d-flex align-items-center">
                <i class="fas fa-shield-alt me-3 fa-lg" style="color: #c9951b;"></i>
                <span class="fw-medium fs-5">Secure Authentication</span>
            </div>
        </div>
    </div>

    <div class="login-form-section">
        <div class="text-center d-md-none">
            <a href="https://cnlawcollege.ac.in/" target="_blank" class="brand-logo mx-auto" style="width: 70px; height: 70px; text-decoration: none;">
                <img src="{{ asset('public/images/cnlc_logo.webp') }}" alt="Logo" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
            </a>
            <h4 class="fw-bold" style="color: var(--primary-color);">CHOTANAGPUR COLLEGE</h4>
            <p class="text-muted small">AN AUTONOMOUS INSTITUTION OF RANCHI UNIVERSITY<br>B++ NAAC ACCREDITED INSTITUTION</p>
        </div>

        <h3 class="fw-bold mb-2" style="color: #1e293b; font-family: math;">Create New Password 🔑</h3>
      <hr>

        <form method="POST" action="{{ route('password.store') }}">
            @csrf

            <!-- Password Reset Token -->
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" class="form-control with-icon @error('email') is-invalid @enderror" 
                        id="email" name="email" value="{{ old('email', $request->email) }}" 
                        required autofocus>
                </div>
                @error('email')
                    <div class="text-danger small mt-1"><i class="fas fa-info-circle me-1"></i>{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">New Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" class="form-control with-icon @error('password') is-invalid @enderror" 
                        id="password" name="password" placeholder="Min. 8 characters" required>
                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password', 'toggleIcon1')">
                        <i class="fas fa-eye" id="toggleIcon1"></i>
                    </button>
                </div>
                @error('password')
                    <div class="text-danger small mt-1"><i class="fas fa-info-circle me-1"></i>{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label for="password_confirmation" class="form-label">Confirm Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" class="form-control with-icon" 
                        id="password_confirmation" name="password_confirmation" placeholder="Retype password" required>
                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password_confirmation', 'toggleIcon2')">
                        <i class="fas fa-eye" id="toggleIcon2"></i>
                    </button>
                </div>
            </div>

            <div class="d-grid mb-3">
                <button type="submit" class="btn btn-login btn-md">
                    Reset Password
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function togglePassword(inputId, iconId) {
        const passwordInput = document.getElementById(inputId);
        const toggleIcon = document.getElementById(iconId);

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.classList.remove('fa-eye');
            toggleIcon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.add('fa-eye');
        }
    }
</script>
@endpush
@endsection
