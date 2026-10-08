@extends('layouts.website')

@section('title', 'Login - Chotanagpur Attendance')

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
                <i class="fas fa-check-circle me-3 fa-lg" style="color: #2a963d;"></i>
                <span class="fw-medium">Secure SSL Environment</span>
            </div>
            <div class="d-flex align-items-center">
                <i class="fas fa-shield-alt me-3 fa-lg" style="color: #2a963d;"></i>
                <span class="fw-medium">Role-Based Access Control</span>
            </div> 
        </div> -->
    </div>

    <div class="login-form-section">
        <div class="text-center d-md-none">
            <a href="https://cnlawcollege.ac.in/" target="_blank" class="brand-logo mx-auto" style="width: 70px; height: 70px; text-decoration: none;">
                <img src="{{ asset('public/images/cnlc_logo.webp') }}" alt="Logo" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
            </a>
            <h4 class="fw-bold" style="color: #c02236;">CHOTANAGPUR COLLEGE</h4>
            <p class="text-muted">AN AUTONOMOUS INSTITUTION OF RANCHI UNIVERSITY<br>B++ NAAC ACCREDITED INSTITUTION</p>
        </div>

        <h3 class="fw-bold mb-2 text-center" style="color: #1e293b; font-family: math;">Login</h3>
        <hr>

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="mb-4">
                <label for="email" class="form-label">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope "></i></span>
                    <input type="email" class="form-control with-icon @error('email') is-invalid @enderror" 
                        id="email" name="email" value="{{ old('email') }}" 
                        placeholder="Enter your email" required autofocus>
                </div>
                @error('email')
                    <div class="text-danger small mt-1"><i class="fas fa-info-circle me-1"></i>{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label for="password" class="form-label mb-0">Password</label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-decoration-none small fw-semibold" style="color: #c02236;">Forgot password?</a>
                    @endif
                </div>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" class="form-control with-icon @error('password') is-invalid @enderror" 
                        id="password" name="password" placeholder="Enter your password" required>
                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                        <i class="fas fa-eye" id="toggleIcon"></i>
                    </button>
                </div>
                @error('password')
                    <div class="text-danger small mt-1"><i class="fas fa-info-circle me-1"></i>{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4 pb-2">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember"
                        {{ old('remember') ? 'checked' : '' }}>
                    <label class="form-check-label" for="remember">
                        Remember me
                    </label>
                </div>
            </div>

            <div class="d-grid mb-4">
                <button type="submit" class="btn btn-login btn-md">
                    Sign In
                </button>
            </div>
            
            <div class="text-center text-muted">
                Don't have an account? 
                <a href="{{ route('register') }}" class="text-decoration-none fw-bold ms-1" style="color: #c02236; position: relative;">
                    Register here
                    <span style="position: absolute; bottom: -2px; left: 0; width: 100%; height: 2px; background-color: #c02236; transform: scaleX(0); transition: transform 0.3s ease;"></span>
                </a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('toggleIcon');

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

    // Add a hover effect to the register link underline
    document.querySelector('a[href="{{ route("register") }}"]').addEventListener('mouseenter', function() {
        this.querySelector('span').style.transform = 'scaleX(1)';
    });
    document.querySelector('a[href="{{ route("register") }}"]').addEventListener('mouseleave', function() {
        this.querySelector('span').style.transform = 'scaleX(0)';
    });
</script>
@endpush
@endsection
