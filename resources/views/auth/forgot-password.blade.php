@extends('layouts.website')

@section('title', 'Forgot Password - Chotanagpur Attendance')

@push('styles')
<link rel="stylesheet" href="{{ asset('public/css/login.css') }}">
<style>
    .login-form-section {
        padding: 4rem 3rem;
    }
    @media (max-width: 480px) {
        .login-form-section {
            padding: 2rem 1.25rem;
        }
    }
</style>
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
                <i class="fas fa-lock-open me-3 fa-lg" style="color: #c9951b;"></i>
                <span class="fw-medium fs-5">Secure Password Recovery</span>
            </div>
            <div class="d-flex align-items-center">
                <i class="fas fa-shield-alt me-3 fa-lg" style="color: #c9951b;"></i>
                <span class="fw-medium fs-5">Account Protection</span>
            </div>
        </div> -->
    </div>

    <div class="login-form-section">
        <div class="text-center d-md-none">
            <a href="https://cnlawcollege.ac.in/" target="_blank" class="brand-logo mx-auto" style="width: 70px; height: 70px; text-decoration: none;">
                <img src="{{ asset('public/images/cnlc_logo.webp') }}" alt="Logo" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
            </a>
            <h4 class="fw-bold" style="color: var(--primary-color);">CHOTANAGPUR COLLEGE</h4>
            <p class="text-muted small">AN AUTONOMOUS INSTITUTION OF RANCHI UNIVERSITY<br>B++ NAAC ACCREDITED INSTITUTION</p>
        </div>

        <h3 class="fw-bold mb-2 text-center" style="color: #1e293b; font-family: math;">Forgot Password? </h3>
        <hr>

        @if (session('status'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('status') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="mb-4">
                <label for="email" class="form-label">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" class="form-control with-icon @error('email') is-invalid @enderror" 
                        id="email" name="email" value="{{ old('email') }}" 
                        placeholder="your@email.com" required autofocus>
                </div>
                @error('email')
                    <div class="text-danger small mt-1"><i class="fas fa-info-circle me-1"></i>{{ $message }}</div>
                @enderror
            </div>

            <div class="d-grid mb-4">
                <button type="submit" class="btn btn-login btn-md">
                    Email Password Reset Link
                </button>
            </div>
            
            <div class="text-center text-muted">
                Remember your password? 
                <a href="{{ route('login') }}" class="text-decoration-none fw-bold ms-1" style="color: var(--primary-color); position: relative;">
                    Back to Login
                    <span style="position: absolute; bottom: -2px; left: 0; width: 100%; height: 2px; background-color: var(--primary-color); transform: scaleX(0); transition: transform 0.3s ease;"></span>
                </a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.querySelector('a[href="{{ route("login") }}"]').addEventListener('mouseenter', function() {
        this.querySelector('span').style.transform = 'scaleX(1)';
    });
    document.querySelector('a[href="{{ route("login") }}"]').addEventListener('mouseleave', function() {
        this.querySelector('span').style.transform = 'scaleX(0)';
    });
</script>
@endpush
@endsection
