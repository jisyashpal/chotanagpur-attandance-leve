<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel - CNLC Attendance')</title>
    <link rel="icon" type="image/png" href="{{ asset('public/images/cnlc_logo.webp') }}">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom Admin CSS -->
    <link rel="stylesheet" href="{{ asset('public/css/admin.css') }}">

    @stack('styles')
</head>

<body>

    <!-- Mobile Backdrop -->
    <div class="sidebar-backdrop"></div>

    <div class="admin-wrapper">
        <!-- Sidebar -->
        @include('layouts.partials.sidebar')


        <!-- Main Content -->
        <div class="admin-main">
            <!-- Top Bar -->
            <header class="admin-topbar">
                <div class="topbar-left">
                    <div class="sidebar-toggle d-lg-none">
                        <i class="fas fa-bars"></i>
                    </div>

                    <div class="topbar-breadcrumb  ">
                        @yield('breadcrumb')
                    </div>
                </div>

                <div class="topbar-right">
                    <div class="topbar-clock d-md-flex" id="topbarClock" title="Current time">
                        <i class="fas fa-clock"></i>
                        <div class="topbar-clock-text">
                            <span class="topbar-clock-time" id="topbarClockTime">--:--:--</span>
                            <span class="topbar-clock-date" id="topbarClockDate">---</span>
                        </div>
                    </div>

                    <div class="topbar-user dropdown">
                        <div class="d-flex align-items-center" role="button" data-bs-toggle="dropdown"
                            aria-expanded="false" style="cursor: pointer;">
                            <div class="topbar-avatar overflow-hidden">
                                @if (auth()->user()->getProfilePhotoUrl())
                                    <img src="{{ auth()->user()->getProfilePhotoUrl() }}" alt="Profile"
                                        style="width: 100%; height: 100%; object-fit: cover;">
                                @else
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                @endif
                            </div>
                            <div class="topbar-user-info d-none d-lg-block ms-3">
                                <div class="topbar-user-name">{{ auth()->user()->name }}</div>
                                <div class="topbar-user-role">
                                    {{ ucfirst(auth()->user()->role) }}</div>
                            </div>
                            <i class="fas fa-chevron-down ms-2 d-none d-lg-block"
                                style="font-size: 0.75rem; opacity: 0.7;"></i>
                        </div>

                        <!-- Dropdown Menu -->
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0" style="min-width: 200px;">
                            <li class="dropdown-header bg-light">
                                <div class="fw-bold text-dark">{{ auth()->user()->name }}</div>
                                <small class="text-muted">{{ auth()->user()->email }}</small>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route(auth()->user()->role . '.profile.edit') }}">
                                    <i class="fas fa-user-circle me-2 text-primary"></i> My Profile
                                </a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}" class="m-0">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>

            <!-- Content Area -->
            <div class="admin-content">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        <ul style="margin: 0; padding-left: 1.5rem;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom Admin JavaScript -->
    <script src="{{ asset('public/js/admin.js') }}"></script>

    @stack('scripts')

    @if (session('success'))
        <script>
            window.addEventListener('load', function() {
                showToast("{{ session('success') }}", 'success');
            });
        </script>
    @endif

    @if (session('error'))
        <script>
            window.addEventListener('load', function() {
                showToast("{{ session('error') }}", 'error');
            });
        </script>
    @endif

</body>

</html>
