@extends('layouts.website')

@section('title', 'Register - Chotanagpur Attendance')

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
        
        <!-- <div class="mt-5 text-center position-relative" style="z-index: 1;">
            <div class="d-flex align-items-center mb-3">
                <i class="fas fa-user-graduate me-3 fa-lg" style="color: #c9951b;"></i>
                <span class="fw-medium fs-5">Student Portal Access</span>
            </div>
            <div class="d-flex align-items-center mb-3">
                <i class="fas fa-chalkboard-teacher me-3 fa-lg" style="color: #c9951b;"></i>
                <span class="fw-medium fs-5">Faculty Dashboard</span>
            </div>
            <div class="d-flex align-items-center">
                <i class="fas fa-users-cog me-3 fa-lg" style="color: #c9951b;"></i>
                <span class="fw-medium fs-5">Staff Management</span>
            </div>
        </div> -->
    </div>

    <div class="login-form-section">
        <div class="text-center d-md-none">
            <a href="https://cnlawcollege.ac.in/" target="_blank" class="brand-logo mx-auto" style="width: 70px; height: 70px; text-decoration: none;">
                <img src="{{ asset('public/images/cnlc_logo.webp') }}" alt="Logo" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
            </a>
            <h4 class="fw-bold" style="color: var(--primary-color); font-family: math;">CHOTANAGPUR COLLEGE</h4>
            <p class="text-muted small">AN AUTONOMOUS INSTITUTION OF RANCHI UNIVERSITY<br>B++ NAAC ACCREDITED INSTITUTION</p>
        </div>

        <h3 class="fw-bold mb-2 text-center" style="color: #1e293b; font-family: math;">Create Account </h3>
        <hr>
        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Full Name</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" class="form-control with-icon @error('name') is-invalid @enderror" 
                            id="name" name="name" value="{{ old('name') }}" 
                            placeholder="Enter your full name" required autofocus>
                    </div>
                    @error('name')
                        <div class="text-danger small mt-1"><i class="fas fa-info-circle me-1"></i>{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" class="form-control with-icon @error('email') is-invalid @enderror" 
                            id="email" name="email" value="{{ old('email') }}" 
                            placeholder="your@email.com" required>
                    </div>
                    @error('email')
                        <div class="text-danger small mt-1"><i class="fas fa-info-circle me-1"></i>{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 mb-3">
                    <label for="role" class="form-label">Account Type (Role)</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user-tag"></i></span>
                        <select class="form-select with-icon @error('role') is-invalid @enderror" id="role" name="role" required>
                            <option value="" disabled {{ old('role') ? '' : 'selected' }}>Select your account role</option>
                            <option value="faculty" {{ old('role') == 'faculty' ? 'selected' : '' }}>Faculty</option>
                            <option value="staff" {{ old('role') == 'staff' ? 'selected' : '' }}>Staff</option>
                        </select>
                    </div>
                    @error('role')
                        <div class="text-danger small mt-1"><i class="fas fa-info-circle me-1"></i>{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-4">
                    <label for="password" class="form-label">Password</label>
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

                <div class="col-md-6 mb-4">
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
            </div>

            <div class="d-grid mb-3">
                <button type="submit" class="btn btn-login btn-md">
                    Create Account
                </button>
            </div>
            
            <div class="register-footer text-muted">
                Already have an account? 
                <a href="{{ route('login') }}" class="text-decoration-none fw-bold ms-1" style="color: var(--primary-color); position: relative;">
                    Login here
                    <span style="position: absolute; bottom: -2px; left: 0; width: 100%; height: 2px; background-color: var(--primary-color); transform: scaleX(0); transition: transform 0.3s ease;"></span>
                </a>
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

    document.querySelector('a[href="{{ route("login") }}"]').addEventListener('mouseenter', function() {
        this.querySelector('span').style.transform = 'scaleX(1)';
    });
    document.querySelector('a[href="{{ route("login") }}"]').addEventListener('mouseleave', function() {
        this.querySelector('span').style.transform = 'scaleX(0)';
    });
</script>
@endpush
@endsection
