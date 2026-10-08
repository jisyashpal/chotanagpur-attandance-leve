@extends('layouts.admin')

@section('title', 'Send Notification')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <i class="fas fa-chevron-right text-muted mx-2" style="font-size: 0.8rem;"></i>
    <span class="text-primary">Send Notification</span>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12 mx-auto">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="card-title mb-0 fw-bold"><i class="fas fa-bell text-primary me-2"></i> Broadcast Notification</h5>
                </div>
                
                <div class="card-body p-4">
                    <form action="{{ route('admin.notifications.send') }}" method="POST">
                        @csrf
                        
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Target Audience</label>
                                    <div class="target-audience-selector">
                                        <button type="button" class="target-btn active" data-value="all" id="btnTargetAll">
                                            <i class="fas fa-users"></i> All Users
                                        </button>
                                        <button type="button" class="target-btn specific" data-value="specific" id="btnTargetSpecific">
                                            <i class="fas fa-user-tag"></i> Specific Users
                                        </button>
                                    </div>
                                    <input type="hidden" name="send_to_all" id="send_to_all" value="1">
                                    <input type="hidden" name="target_type" id="target_type" value="all">
                                </div>

                                <div class="mb-3">
                                    <label for="title" class="form-label small fw-bold">Notification Title</label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" required placeholder="Enter title" value="{{ old('title') }}">
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="message" class="form-label small fw-bold">Message Content</label>
                                    <textarea class="form-control @error('message') is-invalid @enderror" id="message" name="message" rows="5" required placeholder="Type your message here...">{{ old('message') }}</textarea>
                                    @error('message')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary px-5">
                                        <i class="fas fa-paper-plane me-2"></i> Send Notification
                                    </button>
                                </div>
                            </div>

                             <!-- Right: User Selection (Conditional) -->
                             <div class="col-md-6 d-none" id="user_select_container">
                                 <div class="d-flex flex-column h-100">
                                     <div class="d-flex justify-content-between align-items-center mb-2">
                                         <label class="form-label small fw-bold mb-0">Select Recipients</label>
                                         <button type="button" id="selectAllUsers" class="btn btn-link btn-sm p-0 text-decoration-none" style="font-size: 0.75rem;">Select All</button>
                                     </div>

                                     <!-- Role Tabs -->
                                     <ul class="nav nav-pills nav-justified mb-3 small" id="roleTabs" role="tablist" style="background: #f8f9fa; padding: 3px; border-radius: 8px;">
                                         <li class="nav-item">
                                             <button class="nav-link active py-1 role-tab" data-role="student" type="button">Students</button>
                                         </li>
                                         <li class="nav-item">
                                             <button class="nav-link py-1 role-tab" data-role="faculty" type="button">Teachers</button>
                                         </li>
                                         <li class="nav-item">
                                             <button class="nav-link py-1 role-tab" data-role="staff" type="button">Staff</button>
                                         </li>
                                     </ul>
                                     
                                     <!-- Academic Filters (Only for Students) -->
                                     <div id="studentFilters" class="row g-2 mb-2">
                                         <div class="col-4">
                                             <select id="filterSession" class="form-select form-select-sm academic-filter">
                                                 <option value="">Session</option>
                                                 @foreach($sessions as $session)
                                                     <option value="{{ $session->id }}">{{ $session->name }}</option>
                                                 @endforeach
                                             </select>
                                         </div>
                                         <div class="col-4">
                                             <select id="filterCourse" class="form-select form-select-sm academic-filter">
                                                 <option value="">Course</option>
                                                 @foreach($courses as $course)
                                                     <option value="{{ $course->id }}">{{ $course->name }}</option>
                                                 @endforeach
                                             </select>
                                         </div>
                                         <div class="col-4">
                                             <select id="filterSemester" class="form-select form-select-sm academic-filter">
                                                 <option value="">Sem</option>
                                                 @foreach($semesters as $semester)
                                                     <option value="{{ $semester->id }}">{{ $semester->name }}</option>
                                                 @endforeach
                                             </select>
                                         </div>
                                     </div>

                                     <div class="input-group input-group-sm mb-2">
                                         <span class="input-group-text bg-white"><i class="fas fa-search"></i></span>
                                         <input type="text" id="userSearch" class="form-control" placeholder="Search by name...">
                                     </div>
                                     
                                     <div class="border rounded bg-light flex-grow-1" style="height: 300px; overflow-y: auto;">
                                         <div class="list-group list-group-flush" id="userList">
                                             @foreach($users as $user)
                                                 @php
                                                     $hasDevice = $user->devices->whereNotNull('fcm_token')->count() > 0;
                                                 @endphp
                                                 <label class="list-group-item d-flex align-items-center py-2 px-3 user-item {{ !$hasDevice ? 'opacity-75' : '' }}" 
                                                        data-role="{{ $user->role }}"
                                                        data-session="{{ $user->studentProfile->session_id ?? '' }}"
                                                        data-course="{{ $user->studentProfile->course_id ?? '' }}"
                                                        data-semester="{{ $user->studentProfile->semester_id ?? '' }}">
                                                     <input class="form-check-input me-3" type="checkbox" name="user_ids[]" value="{{ $user->id }}" 
                                                            data-name="{{ strtolower($user->name) }}">
                                                     <div class="flex-grow-1">
                                                         <div class="fw-bold small {{ !$hasDevice ? 'text-muted' : '' }}">
                                                             {{ $user->name }}
                                                             @if(!$hasDevice)
                                                                 <i class="fas fa-exclamation-triangle text-warning ms-1" title="No device registered" style="font-size: 0.7rem;"></i>
                                                             @endif
                                                         </div>
                                                         <span class="text-muted" style="font-size: 0.65rem;">
                                                             {{ $user->email }}
                                                             @if(!$hasDevice)
                                                                 <span class="text-warning ms-1" style="font-size: 0.6rem;">(Push Unavailable)</span>
                                                             @endif
                                                         </span>
                                                     </div>
                                                 </label>
                                             @endforeach
                                         </div>
                                     </div>
                                     @error('user_ids')
                                         <div class="text-danger x-small mt-1" style="font-size: 0.75rem;">{{ $message }}</div>
                                     @enderror
                                 </div>
                             </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function toggleUserSelect(value) {
        const container = document.getElementById('user_select_container');
        const sendToAllInput = document.getElementById('send_to_all');
        const checkboxes = document.querySelectorAll('input[name="user_ids[]"]');
        
        if (value === 'specific') {
            container.classList.remove('d-none');
            sendToAllInput.value = '0';
        } else {
            container.classList.add('d-none');
            sendToAllInput.value = '1';
            // Uncheck all when switching to 'All'
            checkboxes.forEach(cb => cb.checked = false);
        }
    }

    // Search, Role Tabs and Academic Filter Logic
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('userSearch');
        const userItems = document.querySelectorAll('.user-item');
        const selectAllBtn = document.getElementById('selectAllUsers');
        const academicFilters = document.querySelectorAll('.academic-filter');
        const roleTabs = document.querySelectorAll('.role-tab');
        const studentFilters = document.getElementById('studentFilters');

        let activeRole = 'student';

        // Target Audience Buttons
        const targetBtns = document.querySelectorAll('.target-btn');
        const targetTypeInput = document.getElementById('target_type');
        const sendToAllInput = document.getElementById('send_to_all');

        targetBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                targetBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                const value = this.dataset.value;
                targetTypeInput.value = value;
                sendToAllInput.value = (value === 'all' ? '1' : '0');
                
                toggleUserSelect(value);
            });
        });

        function applyFilters() {
            const searchTerm = searchInput.value.toLowerCase();
            const sessionId = document.getElementById('filterSession').value;
            const courseId = document.getElementById('filterCourse').value;
            const semesterId = document.getElementById('filterSemester').value;

            // Show/Hide academic filters based on role
            if (activeRole === 'student') {
                studentFilters.classList.remove('d-none');
            } else {
                studentFilters.classList.add('d-none');
            }

            userItems.forEach(item => {
                const input = item.querySelector('input');
                const name = input.dataset.name || '';
                const itemRole = item.dataset.role || '';
                const itemSession = item.dataset.session || '';
                const itemCourse = item.dataset.course || '';
                const itemSemester = item.dataset.semester || '';

                const matchesRole = itemRole === activeRole;
                const matchesSearch = name.includes(searchTerm);
                
                let matchesAcademic = true;
                if (activeRole === 'student') {
                    matchesAcademic = (!sessionId || itemSession === sessionId) &&
                                     (!courseId || itemCourse === courseId) &&
                                     (!semesterId || itemSemester === semesterId);
                }

                if (matchesRole && matchesSearch && matchesAcademic) {
                    item.classList.remove('d-none');
                    item.classList.add('d-flex');
                } else {
                    item.classList.add('d-none');
                    item.classList.remove('d-flex');
                }
            });

            // Update Select All text
            const visibleCheckboxes = document.querySelectorAll('.user-item:not(.d-none) input[name="user_ids[]"]');
            const allChecked = visibleCheckboxes.length > 0 && Array.from(visibleCheckboxes).every(cb => cb.checked);
            selectAllBtn.textContent = allChecked ? 'Unselect All' : 'Select All';
        }

        // Tab Switching
        roleTabs.forEach(tab => {
            tab.addEventListener('click', function() {
                roleTabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                activeRole = this.dataset.role;
                applyFilters();
            });
        });

        if (searchInput) {
            searchInput.addEventListener('input', applyFilters);
        }

        academicFilters.forEach(filter => {
            filter.addEventListener('change', applyFilters);
        });

        if (selectAllBtn) {
            selectAllBtn.addEventListener('click', function() {
                const visibleCheckboxes = document.querySelectorAll('.user-item:not(.d-none) input[name="user_ids[]"]');
                if (visibleCheckboxes.length === 0) return;

                const allChecked = Array.from(visibleCheckboxes).every(cb => cb.checked);
                visibleCheckboxes.forEach(cb => {
                    cb.checked = !allChecked;
                });
                this.textContent = allChecked ? 'Select All' : 'Unselect All';
            });
        }

        // Initial apply
        applyFilters();
    });
</script>
@endpush
@endsection
