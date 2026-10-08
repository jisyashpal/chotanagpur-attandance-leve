<aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-brand">
        <div class="sidebar-brand-icon">
            <img src="{{ asset('public/images/cnlc_logo.webp') }}" alt="CNLC Logo">
        </div>
        <div class="sidebar-brand-copy">
            <span class="sidebar-brand-text">CNLC Portal</span>
            <span class="sidebar-brand-sub">Attendance System</span>
        </div>
        <button type="button" class="sidebar-close d-lg-none" id="sidebarClose" aria-label="Close sidebar">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <!-- <div class="sidebar-user">
        <div class="sidebar-user-avatar">
            @if (auth()->user()->getProfilePhotoUrl())
                <img src="{{ auth()->user()->getProfilePhotoUrl() }}" alt="{{ auth()->user()->name }}">
            @else
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            @endif
        </div>
        <div class="sidebar-user-meta">
            <div class="sidebar-user-name">{{ auth()->user()->name }}</div>
            <div class="sidebar-user-role">{{ ucfirst(auth()->user()->role) }}</div>
        </div>
    </div> -->

    <nav class="sidebar-nav">
        <ul class="sidebar-menu">
            {{-- Admin Menu --}}
            @if (auth()->user()->role === 'admin')
                <li class="sidebar-menu-header">Main Menu</li>
                <li class="sidebar-menu-item">
                    <a href="{{ route('admin.dashboard') }}"
                        class="sidebar-menu-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <span class="sidebar-menu-icon"><i class="fas fa-chart-line"></i></span>
                        <span class="sidebar-menu-text">Dashboard</span>
                    </a>
                </li>

                <li class="sidebar-menu-header">Management</li>

                @php $academyOpen = request()->routeIs('admin.academy.*'); @endphp
                <li class="sidebar-menu-item {{ $academyOpen ? 'is-open' : '' }}">
                    <a href="#" class="sidebar-menu-link {{ $academyOpen ? 'has-active' : '' }}">
                        <span class="sidebar-menu-icon"><i class="fas fa-graduation-cap"></i></span>
                        <span class="sidebar-menu-text">Academic Setup</span>
                        <i class="sidebar-menu-arrow fas {{ $academyOpen ? 'fa-chevron-down' : 'fa-chevron-right' }}"></i>
                    </a>
                    <ul class="sidebar-submenu {{ $academyOpen ? 'show' : '' }}">
                        <li class="sidebar-submenu-item">
                            <a href="{{ route('admin.academy.courses') }}"
                                class="sidebar-submenu-link {{ request()->routeIs('admin.academy.courses') ? 'active' : '' }}">Courses</a>
                        </li>
                        <li class="sidebar-submenu-item">
                            <a href="{{ route('admin.academy.batches') }}"
                                class="sidebar-submenu-link {{ request()->routeIs('admin.academy.batches*') ? 'active' : '' }}">Batches</a>
                        </li>
                        <li class="sidebar-submenu-item">
                            <a href="{{ route('admin.academy.sessions') }}"
                                class="sidebar-submenu-link {{ request()->routeIs('admin.academy.sessions') ? 'active' : '' }}">Sessions</a>
                        </li>
                        <li class="sidebar-submenu-item">
                            <a href="{{ route('admin.academy.semesters') }}"
                                class="sidebar-submenu-link {{ request()->routeIs('admin.academy.semesters') ? 'active' : '' }}">Semesters</a>
                        </li>
                        <li class="sidebar-submenu-item">
                            <a href="{{ route('admin.academy.sections') }}"
                                class="sidebar-submenu-link {{ request()->routeIs('admin.academy.sections*') ? 'active' : '' }}">Sections</a>
                        </li>
                        <li class="sidebar-submenu-item">
                            <a href="{{ route('admin.academy.subjects') }}"
                                class="sidebar-submenu-link {{ request()->routeIs('admin.academy.subjects') ? 'active' : '' }}">Subjects</a>
                        </li>
                        <li class="sidebar-submenu-item">
                            <a href="{{ route('admin.academy.allocations') }}"
                                class="sidebar-submenu-link {{ request()->routeIs('admin.academy.allocations') ? 'active' : '' }}">Subject Allocation</a>
                        </li>
                        <li class="sidebar-submenu-item">
                            <a href="{{ route('admin.academy.routines') }}"
                                class="sidebar-submenu-link {{ request()->routeIs('admin.academy.routines*') ? 'active' : '' }}">Routine</a>
                        </li>
                    </ul>
                </li>

                @php $usersOpen = request()->routeIs('admin.users.*') && !request()->routeIs('admin.users.active_logins*'); @endphp
                <li class="sidebar-menu-item {{ $usersOpen ? 'is-open' : '' }}">
                    <a href="#" class="sidebar-menu-link {{ $usersOpen ? 'has-active' : '' }}">
                        <span class="sidebar-menu-icon"><i class="fas fa-users"></i></span>
                        <span class="sidebar-menu-text">User Management</span>
                        <i class="sidebar-menu-arrow fas {{ $usersOpen ? 'fa-chevron-down' : 'fa-chevron-right' }}"></i>
                    </a>
                    <ul class="sidebar-submenu {{ $usersOpen ? 'show' : '' }}">
                        <li class="sidebar-submenu-item">
                            <a href="{{ route('admin.users.staff_faculty') }}"
                                class="sidebar-submenu-link {{ request()->routeIs('admin.users.staff_faculty*') ? 'active' : '' }}">Staff & Faculty</a>
                        </li>
                        <li class="sidebar-submenu-item">
                            <a href="{{ route('admin.users.students') }}"
                                class="sidebar-submenu-link {{ request()->routeIs('admin.users.students*') && !request()->routeIs('admin.users.students.import*') ? 'active' : '' }}">Student List</a>
                        </li>
                        <li class="sidebar-submenu-item">
                            <a href="{{ route('admin.users.students.import.show') }}"
                                class="sidebar-submenu-link {{ request()->routeIs('admin.users.students.import*') ? 'active' : '' }}">Import Students</a>
                        </li>
                        <li class="sidebar-submenu-item">
                            <a href="{{ route('admin.users.students.promote') }}"
                                class="sidebar-submenu-link {{ request()->routeIs('admin.users.students.promote*') ? 'active' : '' }}">Promote Students</a>
                        </li>
                    </ul>
                </li>

                @php
                    $attendanceOpen = request()->routeIs('admin.attendance.*')
                        || request()->routeIs('admin.locations.*')
                        || request()->routeIs('admin.holidays.*')
                        || request()->routeIs('admin.leaves.*');
                @endphp
                <li class="sidebar-menu-item {{ $attendanceOpen ? 'is-open' : '' }}">
                    <a href="#" class="sidebar-menu-link {{ $attendanceOpen ? 'has-active' : '' }}">
                        <span class="sidebar-menu-icon"><i class="fas fa-calendar-check"></i></span>
                        <span class="sidebar-menu-text">Attendance</span>
                        <i class="sidebar-menu-arrow fas {{ $attendanceOpen ? 'fa-chevron-down' : 'fa-chevron-right' }}"></i>
                    </a>
                    <ul class="sidebar-submenu {{ $attendanceOpen ? 'show' : '' }}">
                        <li class="sidebar-submenu-item">
                            <a href="{{ route('admin.attendance.students') }}"
                                class="sidebar-submenu-link {{ request()->routeIs('admin.attendance.students') ? 'active' : '' }}">Student Attendance</a>
                        </li>
                        <li class="sidebar-submenu-item">
                            <a href="{{ route('admin.attendance.class_markings') }}"
                                class="sidebar-submenu-link {{ request()->routeIs('admin.attendance.class_markings*') ? 'active' : '' }}">Class Markings</a>
                        </li>
                        <li class="sidebar-submenu-item">
                            <a href="{{ route('admin.attendance.staff_faculty') }}"
                                class="sidebar-submenu-link {{ request()->routeIs('admin.attendance.staff_faculty') ? 'active' : '' }}">Staff & Faculty</a>
                        </li>
                        <li class="sidebar-submenu-item">
                            <a href="{{ route('admin.attendance.monthly') }}"
                                class="sidebar-submenu-link {{ request()->routeIs('admin.attendance.monthly*') ? 'active' : '' }}">Monthly Attendance</a>
                        </li>
                        <li class="sidebar-submenu-item">
                            <a href="{{ route('admin.leaves.index') }}"
                                class="sidebar-submenu-link {{ request()->routeIs('admin.leaves.*') ? 'active' : '' }}">Leave Management</a>
                        </li>
                        <li class="sidebar-submenu-item">
                            <a href="{{ route('admin.holidays.index') }}"
                                class="sidebar-submenu-link {{ request()->routeIs('admin.holidays.*') ? 'active' : '' }}">Holidays</a>
                        </li>
                        <li class="sidebar-submenu-item">
                            <a href="{{ route('admin.locations.index') }}"
                                class="sidebar-submenu-link {{ request()->routeIs('admin.locations.*') ? 'active' : '' }}">Attendance Locations</a>
                        </li>
                    </ul>
                </li>

                @php $notifyOpen = request()->routeIs('admin.notifications.*') || request()->routeIs('admin.notices.*'); @endphp
                <li class="sidebar-menu-item {{ $notifyOpen ? 'is-open' : '' }}">
                    <a href="#" class="sidebar-menu-link {{ $notifyOpen ? 'has-active' : '' }}">
                        <span class="sidebar-menu-icon"><i class="fas fa-bell"></i></span>
                        <span class="sidebar-menu-text">Notifications</span>
                        <i class="sidebar-menu-arrow fas {{ $notifyOpen ? 'fa-chevron-down' : 'fa-chevron-right' }}"></i>
                    </a>
                    <ul class="sidebar-submenu {{ $notifyOpen ? 'show' : '' }}">
                        <li class="sidebar-submenu-item">
                            <a href="{{ route('admin.notifications.create') }}"
                                class="sidebar-submenu-link {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}">Push Notification</a>
                        </li>
                        <li class="sidebar-submenu-item">
                            <a href="{{ route('admin.notices.index') }}"
                                class="sidebar-submenu-link {{ request()->routeIs('admin.notices.*') ? 'active' : '' }}">Link Notification</a>
                        </li>
                    </ul>
                </li>

                @php $activityOpen = request()->routeIs('admin.users.active_logins*') || request()->routeIs('admin.activity_logs.*'); @endphp
                <li class="sidebar-menu-item {{ $activityOpen ? 'is-open' : '' }}">
                    <a href="#" class="sidebar-menu-link {{ $activityOpen ? 'has-active' : '' }}">
                        <span class="sidebar-menu-icon"><i class="fas fa-history"></i></span>
                        <span class="sidebar-menu-text">Activity</span>
                        <i class="sidebar-menu-arrow fas {{ $activityOpen ? 'fa-chevron-down' : 'fa-chevron-right' }}"></i>
                    </a>
                    <ul class="sidebar-submenu {{ $activityOpen ? 'show' : '' }}">
                        <li class="sidebar-submenu-item">
                            <a href="{{ route('admin.users.active_logins') }}"
                                class="sidebar-submenu-link {{ request()->routeIs('admin.users.active_logins*') ? 'active' : '' }}">Active Sessions</a>
                        </li>
                        <li class="sidebar-submenu-item">
                            <a href="{{ route('admin.activity_logs.index') }}"
                                class="sidebar-submenu-link {{ request()->routeIs('admin.activity_logs.*') ? 'active' : '' }}">Activity Logs</a>
                        </li>
                    </ul>
                </li>
            @endif

            {{-- Student Menu --}}
            @if (auth()->user()->role === 'student')
                <li class="sidebar-menu-header">Student Portal</li>
                <li class="sidebar-menu-item">
                    <a href="{{ route('student.dashboard') }}"
                        class="sidebar-menu-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
                        <span class="sidebar-menu-icon"><i class="fas fa-user-graduate"></i></span>
                        <span class="sidebar-menu-text">Dashboard</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="{{ route('student.attendance') }}"
                        class="sidebar-menu-link {{ request()->routeIs('student.attendance') ? 'active' : '' }}">
                        <span class="sidebar-menu-icon"><i class="fas fa-calendar-check"></i></span>
                        <span class="sidebar-menu-text">Attendance</span>
                    </a>
                </li>
                @php $studentProfileOpen = request()->routeIs('student.profile.edit') || request()->routeIs('student.profile.complete.show'); @endphp
                <li class="sidebar-menu-item {{ $studentProfileOpen ? 'is-open' : '' }}">
                    <a href="#" class="sidebar-menu-link {{ $studentProfileOpen ? 'has-active' : '' }}">
                        <span class="sidebar-menu-icon"><i class="fas fa-user-circle"></i></span>
                        <span class="sidebar-menu-text">Profile</span>
                        <i class="sidebar-menu-arrow fas {{ $studentProfileOpen ? 'fa-chevron-down' : 'fa-chevron-right' }}"></i>
                    </a>
                    <ul class="sidebar-submenu {{ $studentProfileOpen ? 'show' : '' }}">
                        <li class="sidebar-submenu-item">
                            <a href="{{ route('student.profile.edit') }}"
                                class="sidebar-submenu-link {{ request()->routeIs('student.profile.edit') ? 'active' : '' }}">Update Profile</a>
                        </li>
                        <li class="sidebar-submenu-item">
                            <a href="{{ route('student.profile.complete.show') }}"
                                class="sidebar-submenu-link {{ request()->routeIs('student.profile.complete.show') ? 'active' : '' }}">Complete Profile</a>
                        </li>
                    </ul>
                </li>
            @endif

            {{-- Faculty Menu --}}
            @if (auth()->user()->role === 'faculty')
                <li class="sidebar-menu-header">Faculty Portal</li>
                <li class="sidebar-menu-item">
                    <a href="{{ route('faculty.dashboard') }}"
                        class="sidebar-menu-link {{ request()->routeIs('faculty.dashboard') ? 'active' : '' }}">
                        <span class="sidebar-menu-icon"><i class="fas fa-chalkboard-teacher"></i></span>
                        <span class="sidebar-menu-text">Dashboard</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="{{ route('faculty.attendance') }}"
                        class="sidebar-menu-link {{ request()->routeIs('faculty.attendance') ? 'active' : '' }}">
                        <span class="sidebar-menu-icon"><i class="fas fa-calendar-check"></i></span>
                        <span class="sidebar-menu-text">Attendance</span>
                    </a>
                </li>
                @php $facultyProfileOpen = request()->routeIs('faculty.profile.edit') || request()->routeIs('faculty.profile.complete.show'); @endphp
                <li class="sidebar-menu-item {{ $facultyProfileOpen ? 'is-open' : '' }}">
                    <a href="#" class="sidebar-menu-link {{ $facultyProfileOpen ? 'has-active' : '' }}">
                        <span class="sidebar-menu-icon"><i class="fas fa-user-tie"></i></span>
                        <span class="sidebar-menu-text">Profile</span>
                        <i class="sidebar-menu-arrow fas {{ $facultyProfileOpen ? 'fa-chevron-down' : 'fa-chevron-right' }}"></i>
                    </a>
                    <ul class="sidebar-submenu {{ $facultyProfileOpen ? 'show' : '' }}">
                        <li class="sidebar-submenu-item">
                            <a href="{{ route('faculty.profile.edit') }}"
                                class="sidebar-submenu-link {{ request()->routeIs('faculty.profile.edit') ? 'active' : '' }}">Update Profile</a>
                        </li>
                        <li class="sidebar-submenu-item">
                            <a href="{{ route('faculty.profile.complete.show') }}"
                                class="sidebar-submenu-link {{ request()->routeIs('faculty.profile.complete.show') ? 'active' : '' }}">Complete Profile</a>
                        </li>
                    </ul>
                </li>
            @endif

            {{-- Staff Menu --}}
            @if (auth()->user()->role === 'staff')
                <li class="sidebar-menu-header">Staff Portal</li>
                <li class="sidebar-menu-item">
                    <a href="{{ route('staff.dashboard') }}"
                        class="sidebar-menu-link {{ request()->routeIs('staff.dashboard') ? 'active' : '' }}">
                        <span class="sidebar-menu-icon"><i class="fas fa-users-cog"></i></span>
                        <span class="sidebar-menu-text">Dashboard</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="{{ route('staff.attendance') }}"
                        class="sidebar-menu-link {{ request()->routeIs('staff.attendance') ? 'active' : '' }}">
                        <span class="sidebar-menu-icon"><i class="fas fa-calendar-check"></i></span>
                        <span class="sidebar-menu-text">Attendance</span>
                    </a>
                </li>
                @php $staffProfileOpen = request()->routeIs('staff.profile.edit') || request()->routeIs('staff.profile.complete.show'); @endphp
                <li class="sidebar-menu-item {{ $staffProfileOpen ? 'is-open' : '' }}">
                    <a href="#" class="sidebar-menu-link {{ $staffProfileOpen ? 'has-active' : '' }}">
                        <span class="sidebar-menu-icon"><i class="fas fa-id-badge"></i></span>
                        <span class="sidebar-menu-text">Profile</span>
                        <i class="sidebar-menu-arrow fas {{ $staffProfileOpen ? 'fa-chevron-down' : 'fa-chevron-right' }}"></i>
                    </a>
                    <ul class="sidebar-submenu {{ $staffProfileOpen ? 'show' : '' }}">
                        <li class="sidebar-submenu-item">
                            <a href="{{ route('staff.profile.edit') }}"
                                class="sidebar-submenu-link {{ request()->routeIs('staff.profile.edit') ? 'active' : '' }}">Update Profile</a>
                        </li>
                        <li class="sidebar-submenu-item">
                            <a href="{{ route('staff.profile.complete.show') }}"
                                class="sidebar-submenu-link {{ request()->routeIs('staff.profile.complete.show') ? 'active' : '' }}">Complete Profile</a>
                        </li>
                    </ul>
                </li>
            @endif
        </ul>
    </nav>

    <!-- <div class="sidebar-footer">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="sidebar-logout-btn">
                <span class="sidebar-menu-icon"><i class="fas fa-sign-out-alt"></i></span>
                <span class="sidebar-menu-text">Logout</span>
            </button>
        </form>
    </div> -->
</aside>
