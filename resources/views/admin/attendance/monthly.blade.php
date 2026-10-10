@extends('layouts.admin')

@section('title', 'Monthly Attendance Report')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <i class="fas fa-chevron-right text-muted mx-2" style="font-size: 0.8rem;"></i>
    <span class="text-primary">Monthly Attendance Report</span>
@endsection

@section('content')
<style>
    .attendance-matrix-container {
        max-width: 100%;
        overflow-x: auto;
        border-radius: 8px;
        border: 1px solid #dee2e6;
        position: relative;
    }
    
    .matrix-table {
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
        width: max-content;
        min-width: 100%;
    }
    
    .matrix-table th, .matrix-table td {
        text-align: center;
        padding: 8px 6px;
        font-size: 0.78rem;
        border-bottom: 1px solid #dee2e6;
        border-right: 1px solid #dee2e6;
    }
    
    .matrix-table th {
        background-color: #f8f9fa;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.72rem;
        letter-spacing: 0.5px;
        border-top: 1px solid #dee2e6;
    }
    
    /* Sticky Name and Roll No columns for readability */
    .sticky-col {
        position: -webkit-sticky;
        position: sticky;
        background-color: #fff;
        z-index: 10;
        text-align: left !important;
        border-right: 2px solid #dee2e6 !important;
        box-shadow: 2px 0 5px rgba(0,0,0,0.05);
    }
    
    .matrix-table th.sticky-col {
        background-color: #f8f9fa;
        z-index: 11;
    }
    
    .sticky-col-1 {
        left: 0;
        min-width: 150px;
    }
    
    .sticky-col-2 {
        left: 150px;
        min-width: 90px;
        border-right: 2px solid #dee2e6 !important;
    }
    
    /* Status cells */
    .cell-present {
        background-color: #d1e7dd !important;
        color: #0f5132 !important;
        font-weight: 700;
    }
    
    .cell-absent {
        background-color: #f8d7da !important;
        color: #842029 !important;
        font-weight: 700;
    }
    
    .cell-holiday {
        background-color: #f3e8ff !important;
        color: #581c87 !important;
        font-weight: 700;
        cursor: help;
    }
    
    .cell-leave {
        background-color: #ffe5d0 !important;
        color: #b24b00 !important;
        font-weight: 700;
    }
    
    .cell-sunday {
        background-color: #f1f3f5 !important;
        color: #adb5bd !important;
    }
    
    .cell-empty {
        background-color: #ffffff !important;
        color: #ced4da !important;
    }
    
    .legend-item {
        display: inline-flex;
        align-items: center;
        margin-right: 15px;
        font-size: 0.8rem;
    }
    
    .legend-box {
        width: 16px;
        height: 16px;
        border-radius: 4px;
        margin-right: 6px;
        display: inline-block;
        border: 1px solid #ced4da;
    }
</style>

<div class="container-fluid">
    <!-- Month and Page Level Selector -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body py-3 d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
            <div>
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="fas fa-calendar-alt text-primary me-2"></i>Monthly Attendance Matrix
                </h5>
                <small class="text-muted">Viewing attendance records for {{ $currentDate->format('F Y') }}</small>
            </div>
            
            <form action="{{ route('admin.attendance.monthly') }}" method="GET" id="monthForm" class="d-flex align-items-center gap-2 m-0">
                <!-- Keep track of active tab and filters -->
                <input type="hidden" name="tab" value="{{ request('tab', 'students') }}">
                @if(request('batch_id')) <input type="hidden" name="batch_id" value="{{ request('batch_id') }}"> @endif
                @if(request('session_id')) <input type="hidden" name="session_id" value="{{ request('session_id') }}"> @endif
                @if(request('course_id')) <input type="hidden" name="course_id" value="{{ request('course_id') }}"> @endif
                @if(request('semester_id')) <input type="hidden" name="semester_id" value="{{ request('semester_id') }}"> @endif
                @if(request('section_id')) <input type="hidden" name="section_id" value="{{ request('section_id') }}"> @endif
                @if(request('subject_id')) <input type="hidden" name="subject_id" value="{{ request('subject_id') }}"> @endif
                @if(request('role')) <input type="hidden" name="role" value="{{ request('role') }}"> @endif
                @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
                @if(request('student_id')) <input type="hidden" name="student_id" value="{{ request('student_id') }}"> @endif
                @if(request('user_id')) <input type="hidden" name="user_id" value="{{ request('user_id') }}"> @endif
                
                <label class="form-label small fw-bold mb-0 text-nowrap">Select Month:</label>
                <input type="month" name="month" class="form-control form-control-sm" value="{{ $monthParam }}" onchange="this.form.submit()">
            </form>
        </div>
    </div>

    <!-- Tab Section -->
    @php
        $activeTab = request('tab', 'students');
        // Auto-detect tab based on parameters if tab is not explicitly passed
        if (!request()->has('tab')) {
            if (request()->has('role') || request()->has('search') || request()->has('user_id')) {
                $activeTab = 'staff';
            }
        }
    @endphp

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-0 p-0">
            <ul class="nav nav-tabs nav-fill" id="attendanceTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link py-3 fw-bold {{ $activeTab === 'students' ? 'active text-primary border-primary border-bottom-2' : 'text-muted' }}" 
                            id="students-tab" 
                            data-bs-toggle="tab" 
                            data-bs-target="#students" 
                            type="button" 
                            role="tab" 
                            aria-controls="students" 
                            aria-selected="{{ $activeTab === 'students' ? 'true' : 'false' }}"
                            onclick="setTab('students')">
                        <i class="fas fa-user-graduate me-2"></i>Students
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link py-3 fw-bold {{ $activeTab === 'staff' ? 'active text-primary border-primary border-bottom-2' : 'text-muted' }}" 
                            id="staff-tab" 
                            data-bs-toggle="tab" 
                            data-bs-target="#staff" 
                            type="button" 
                            role="tab" 
                            aria-controls="staff" 
                            aria-selected="{{ $activeTab === 'staff' ? 'true' : 'false' }}"
                            onclick="setTab('staff')">
                        <i class="fas fa-users-cog me-2"></i>Faculty & Staff
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body p-4">
            <div class="tab-content" id="attendanceTabsContent">
                
                <!-- Student Attendance Tab -->
                <div class="tab-pane fade {{ $activeTab === 'students' ? 'show active' : '' }}" id="students" role="tabpanel" aria-labelledby="students-tab">
                    <!-- Filters -->
                    <form action="{{ route('admin.attendance.monthly') }}" method="GET" class="mb-4">
                        <input type="hidden" name="tab" value="students">
                        <input type="hidden" name="month" value="{{ $monthParam }}">

                        @if(request('student_id'))
                            <input type="hidden" name="student_id" value="{{ request('student_id') }}">
                        @endif

                        <div class="filter-card-grid">
                            <div>
                                <label class="form-label small text-muted fw-bold">Course <span class="text-danger">*</span></label>
                                <select name="course_id" id="course_id" class="form-select form-select-sm" required>
                                    <option value="">Select Course</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>{{ $course->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="form-label small text-muted fw-bold">Batch</label>
                                <select name="batch_id" id="batch_id" class="form-select form-select-sm" {{ request('course_id') ? '' : 'disabled' }}>
                                    <option value="">{{ request('course_id') ? 'All Batches' : 'Select Course First' }}</option>
                                    @foreach($batches as $batch)
                                        <option value="{{ $batch->id }}" {{ request('batch_id') == $batch->id ? 'selected' : '' }}>{{ $batch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="form-label small text-muted fw-bold">Session</label>
                                <select name="session_id" id="session_id" class="form-select form-select-sm" {{ request('batch_id') ? '' : 'disabled' }}>
                                    <option value="">{{ request('batch_id') ? 'All Sessions' : 'Select Batch First' }}</option>
                                    @foreach($sessions as $session)
                                        <option value="{{ $session->id }}" {{ request('session_id') == $session->id ? 'selected' : '' }}>{{ $session->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="form-label small text-muted fw-bold">Semester <span class="text-danger">*</span></label>
                                <select name="semester_id" id="semester_id" class="form-select form-select-sm" {{ request('course_id') ? 'required' : 'disabled' }}>
                                    <option value="">{{ request('course_id') ? 'Select Semester' : 'Select Course First' }}</option>
                                    @foreach($semesters as $semester)
                                        <option value="{{ $semester->id }}" {{ request('semester_id') == $semester->id ? 'selected' : '' }}>
                                            {{ $semester->name }}{{ $semester->part ? ' ('.$semester->part.')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="form-label small text-muted fw-bold">Section</label>
                                <select name="section_id" id="section_id" class="form-select form-select-sm" {{ request('semester_id') ? '' : 'disabled' }}>
                                    <option value="">{{ request('semester_id') ? 'All Sections' : 'Select Semester First' }}</option>
                                    @foreach($sections as $section)
                                        <option value="{{ $section->id }}" {{ request('section_id') == $section->id ? 'selected' : '' }}>{{ $section->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="form-label small text-muted fw-bold">Subject (Optional)</label>
                                <select name="subject_id" id="subject_id" class="form-select form-select-sm" {{ request('semester_id') ? '' : 'disabled' }}>
                                    <option value="">{{ request('semester_id') ? 'All Subjects (Show Summary)' : 'Select Semester First' }}</option>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>{{ $subject->name }} ({{ $subject->code }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="actions-col">
                                <button type="submit" class="btn btn-sm btn-primary w-100 shadow-sm">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                                <a href="{{ route('admin.attendance.monthly', ['tab' => 'students', 'month' => $monthParam]) }}" class="btn btn-sm btn-light border w-100">
                                    Reset
                                </a>
                            </div>
                        </div>
                    </form>

                    <!-- Profile link warning indicator -->
                    @if($selectedStudent)
                        <div class="alert alert-info d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <i class="fas fa-info-circle me-2"></i>Showing monthly report for filtered student: <strong>{{ $selectedStudent->name }}</strong>
                            </div>
                            <a href="{{ route('admin.attendance.monthly', ['tab' => 'students', 'month' => $monthParam, 'course_id' => request('course_id'), 'batch_id' => request('batch_id'), 'semester_id' => request('semester_id'), 'session_id' => request('session_id'), 'section_id' => request('section_id'), 'subject_id' => request('subject_id')]) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-table me-1"></i> View Full Batch
                            </a>
                        </div>
                    @endif

                    @if($isStudentFiltered)
                        @if(request('subject_id'))
                            <!-- Detailed Student Matrix -->
                            <div class="mb-3 d-flex justify-content-between align-items-center">
                                <h6 class="fw-bold mb-0 text-secondary">
                                    Student Attendance Matrix: {{ $subjects->find(request('subject_id'))->name ?? '' }}
                                </h6>
                                <div class="small text-muted">Scroll horizontally to view all days</div>
                            </div>

                            <div class="attendance-matrix-container shadow-sm mb-4">
                                <table class="table matrix-table align-middle">
                                    <thead>
                                        <tr>
                                            <th class="sticky-col sticky-col-1">Student Name</th>
                                            <th class="sticky-col sticky-col-2">Roll No</th>
                                            @for($d = 1; $d <= $daysInMonth; $d++)
                                                <th>{{ sprintf('%02d', $d) }}</th>
                                            @endfor
                                            <th class="bg-success text-white border-top">P</th>
                                            <th class="bg-danger text-white border-top">A</th>
                                            <th class="bg-info text-white border-top">LV</th>
                                            <th class="bg-dark text-white border-top">%</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($students as $student)
                                            <tr>
                                                <td class="sticky-col sticky-col-1 fw-bold">
                                                    {{ $student->name }}
                                                </td>
                                                <td class="sticky-col sticky-col-2 text-muted">
                                                    {{ $student->studentProfile->roll_no ?? 'N/A' }}
                                                </td>
                                                @for($day = 1; $day <= $daysInMonth; $day++)
                                                    @php
                                                        $cell = $studentAttendanceMatrix[$student->id][$day] ?? ['status' => '', 'detail' => ''];
                                                        $status = $cell['status'];
                                                        $detail = $cell['detail'];
                                                    @endphp
                                                    @if($status === 'present')
                                                        <td class="cell-present" title="Present">P</td>
                                                    @elseif($status === 'absent')
                                                        <td class="cell-absent" title="Absent">A</td>
                                                    @elseif($status === 'holiday')
                                                        <td class="cell-holiday" title="Holiday: {{ $detail }}">H</td>
                                                    @elseif($status === 'leave')
                                                        <td class="cell-leave" title="Approved Leave">LV</td>
                                                    @elseif($status === 'sunday')
                                                        <td class="cell-sunday">S</td>
                                                    @else
                                                        <td class="cell-empty">-</td>
                                                    @endif
                                                @endfor
                                                @php
                                                    $summary = $studentAttendanceSummary[$student->id] ?? ['present' => 0, 'absent' => 0, 'leave' => 0, 'percentage' => 0];
                                                @endphp
                                                <td class="bg-success-subtle fw-bold text-success">{{ $summary['present'] }}</td>
                                                <td class="bg-danger-subtle fw-bold text-danger">{{ $summary['absent'] }}</td>
                                                <td class="bg-info-subtle fw-bold text-info-emphasis">{{ $summary['leave'] }}</td>
                                                <td class="bg-dark-subtle fw-bold">{{ $summary['percentage'] }}%</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="{{ $daysInMonth + 6 }}" class="text-center py-4 text-muted">No students found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <!-- Aggregated Summary View (No subject selected) -->
                            @php
                                $batchSemester = request('semester_id') ? $semesters->find(request('semester_id')) : null;
                                $batchSubject = $batchSemester ? $batchSemester->subjects->first() : null;
                            @endphp
                            <div class="mb-3 d-flex justify-content-between align-items-center gap-2">
                                <h6 class="fw-bold mb-0 text-secondary">
                                    Overall Monthly Attendance Summary (All Subjects)
                                </h6>
                                @if($batchSubject)
                                    <a href="{{ route('admin.attendance.monthly', ['tab' => 'students', 'month' => $monthParam, 'course_id' => request('course_id'), 'batch_id' => request('batch_id'), 'semester_id' => request('semester_id'), 'session_id' => request('session_id'), 'section_id' => request('section_id'), 'subject_id' => $batchSubject->id]) }}" class="btn btn-sm btn-primary shadow-sm">
                                        <i class="fas fa-table me-1"></i> View Batch Matrix
                                    </a>
                                @endif
                            </div>

                            <div class="table-responsive shadow-sm rounded border">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-4">Student</th>
                                            <th>Roll No.</th>
                                            <th>Total Classes</th>
                                            <th>Attended</th>
                                            <th>Absent</th>
                                            <th>Leave</th>
                                            <th>Attendance %</th>
                                            <th class="pe-4 text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($students as $student)
                                            @php
                                                $summary = $studentAttendanceSummary[$student->id] ?? ['present' => 0, 'absent' => 0, 'leave' => 0, 'total' => 0, 'percentage' => 0];
                                            @endphp
                                            <tr>
                                                <td class="ps-4">
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sm me-2">
                                                            <img src="{{ $student->getProfilePhotoUrl() }}" class="rounded-circle" style="width: 32px; height: 32px; object-fit: cover;">
                                                        </div>
                                                        <span class="fw-bold">{{ $student->name }}</span>
                                                    </div>
                                                </td>
                                                <td>{{ $student->studentProfile->roll_no ?? 'N/A' }}</td>
                                                <td>{{ $summary['total'] }}</td>
                                                <td class="text-success fw-bold">{{ $summary['present'] }}</td>
                                                <td class="text-danger fw-bold">{{ $summary['absent'] }}</td>
                                                <td class="text-info fw-bold">{{ $summary['leave'] }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <span class="fw-bold me-2">{{ $summary['percentage'] }}%</span>
                                                        <div class="progress w-50" style="height: 6px;">
                                                            <div class="progress-bar {{ $summary['percentage'] >= 75 ? 'bg-success' : ($summary['percentage'] >= 50 ? 'bg-warning' : 'bg-danger') }}" 
                                                                 style="width: {{ $summary['percentage'] }}%"></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="pe-4 text-end">
                                                    <!-- Quick links to specific subjects -->
                                                    @if(request('semester_id'))
                                                        @php
                                                            $semester = $semesters->find(request('semester_id'));
                                                            $firstSubject = $semester ? $semester->subjects->first() : null;
                                                        @endphp
                                                        @if($firstSubject)
                                                            <a href="{{ route('admin.attendance.monthly', ['tab' => 'students', 'month' => $monthParam, 'course_id' => request('course_id'), 'batch_id' => request('batch_id'), 'semester_id' => request('semester_id'), 'session_id' => request('session_id'), 'section_id' => request('section_id'), 'subject_id' => $firstSubject->id, 'student_id' => $student->id]) }}" class="btn btn-sm btn-outline-primary py-1">
                                                                View Matrix
                                                            </a>
                                                        @endif
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center py-4 text-muted">No students found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        <!-- Legend -->
                        <div class="mt-4 p-3 bg-light rounded-3 border">
                            <h6 class="fw-bold mb-2 small text-uppercase" style="letter-spacing: 0.5px;">Matrix Legend</h6>
                            <div class="d-flex flex-wrap gap-2">
                                <span class="legend-item"><span class="legend-box cell-present"></span>Present (P)</span>
                                <span class="legend-item"><span class="legend-box cell-absent"></span>Absent (A)</span>
                                <span class="legend-item"><span class="legend-box cell-holiday"></span>Holiday (H)</span>
                                <span class="legend-item"><span class="legend-box cell-leave"></span>Approved Leave (LV)</span>
                                <span class="legend-item"><span class="legend-box cell-sunday"></span>Sunday (S)</span>
                                <span class="legend-item"><span class="legend-box cell-empty"></span>No Record (-)</span>
                            </div>
                        </div>
                    @else
                        <!-- No filter prompt -->
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-filter mb-3 d-block" style="font-size: 3rem; opacity: 0.3;"></i>
                            <h5 class="fw-bold">Select Filters</h5>
                            <p class="small mb-0">Please select a Course and Semester to view the Monthly Attendance report.</p>
                        </div>
                    @endif
                </div>

                <!-- Faculty & Staff Attendance Tab -->
                <div class="tab-pane fade {{ $activeTab === 'staff' ? 'show active' : '' }}" id="staff" role="tabpanel" aria-labelledby="staff-tab">
                    <!-- Filters -->
                    <form action="{{ route('admin.attendance.monthly') }}" method="GET" class="mb-4">
                        <input type="hidden" name="tab" value="staff">
                        <input type="hidden" name="month" value="{{ $monthParam }}">

                        @if(request('user_id'))
                            <input type="hidden" name="user_id" value="{{ request('user_id') }}">
                        @endif

                        <div class="filter-card-grid">
                            <div class="search-col">
                                <label class="form-label small text-muted fw-bold">Search Member</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white border-end-0 text-muted">
                                        <i class="fas fa-search"></i>
                                    </span>
                                    <input type="text" name="search" class="form-control border-start-0"
                                        placeholder="Search by name or email..."
                                        value="{{ request('search') }}">
                                </div>
                            </div>
                            <div>
                                <label class="form-label small text-muted fw-bold">Role</label>
                                <select name="role" class="form-select form-select-sm">
                                    <option value="">All Roles (Faculty & Staff)</option>
                                    <option value="faculty" {{ request('role') == 'faculty' ? 'selected' : '' }}>Faculty</option>
                                    <option value="staff" {{ request('role') == 'staff' ? 'selected' : '' }}>Staff</option>
                                </select>
                            </div>
                            <div class="actions-col">
                                <button type="submit" class="btn btn-sm btn-primary w-100 shadow-sm">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                                <a href="{{ route('admin.attendance.monthly', ['tab' => 'staff', 'month' => $monthParam]) }}" class="btn btn-sm btn-light border w-100">
                                    Reset
                                </a>
                            </div>
                        </div>
                    </form>

                    <!-- Profile link warning indicator -->
                    @if($selectedUser)
                        <div class="alert alert-info d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <i class="fas fa-info-circle me-2"></i>Showing monthly report for filtered user: <strong>{{ $selectedUser->name }} ({{ ucfirst($selectedUser->role) }})</strong>
                            </div>
                            <a href="{{ route('admin.attendance.monthly', ['tab' => 'staff', 'month' => $monthParam, 'role' => request('role'), 'search' => request('search')]) }}" class="btn btn-sm btn-link text-decoration-none">
                                <i class="fas fa-times-circle"></i> Clear User Filter
                            </a>
                        </div>
                    @endif

                    <div class="mb-3 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0 text-secondary">Faculty & Staff Daily Attendance Matrix</h6>
                        <div class="small text-muted">Scroll horizontally to view all days. Hover on cells for In/Out timings.</div>
                    </div>

                    <!-- Attendance Grid -->
                    <div class="attendance-matrix-container shadow-sm mb-4">
                        <table class="table matrix-table align-middle">
                            <thead>
                                <tr>
                                    <th class="sticky-col sticky-col-1">Name</th>
                                    <th class="sticky-col sticky-col-2">Role</th>
                                    @for($d = 1; $d <= $daysInMonth; $d++)
                                        <th>{{ sprintf('%02d', $d) }}</th>
                                    @endfor
                                    <th class="bg-success text-white border-top">P</th>
                                    <th class="bg-danger text-white border-top">A</th>
                                    <th class="bg-info text-white border-top">LV</th>
                                    <th class="bg-dark text-white border-top">%</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($staffUsers as $staff)
                                    <tr>
                                        <td class="sticky-col sticky-col-1 fw-bold">
                                            {{ $staff->name }}
                                        </td>
                                        <td class="sticky-col sticky-col-2 text-muted text-capitalize">
                                            <span class="badge {{ $staff->role === 'faculty' ? 'bg-info-subtle text-info border border-info-subtle' : 'bg-secondary-subtle text-secondary border border-secondary-subtle' }} px-2 py-1">
                                                {{ $staff->role }}
                                            </span>
                                        </td>
                                        @for($day = 1; $day <= $daysInMonth; $day++)
                                            @php
                                                $cell = $staffAttendanceMatrix[$staff->id][$day] ?? ['status' => '', 'detail' => ''];
                                                $status = $cell['status'];
                                                $detail = $cell['detail'];
                                            @endphp
                                            @if($status === 'present')
                                                <td class="cell-present" title="{{ $detail ?: 'Present' }}">P</td>
                                            @elseif($status === 'absent')
                                                <td class="cell-absent" title="Absent">A</td>
                                            @elseif($status === 'holiday')
                                                <td class="cell-holiday" title="Holiday: {{ $detail }}">H</td>
                                            @elseif($status === 'leave')
                                                <td class="cell-leave" title="Approved Leave">LV</td>
                                            @elseif($status === 'sunday')
                                                <td class="cell-sunday">S</td>
                                            @else
                                                <td class="cell-empty">-</td>
                                            @endif
                                        @endfor
                                        @php
                                            $summary = $staffAttendanceSummary[$staff->id] ?? ['present' => 0, 'absent' => 0, 'leave' => 0, 'percentage' => 0];
                                        @endphp
                                        <td class="bg-success-subtle fw-bold text-success">{{ $summary['present'] }}</td>
                                        <td class="bg-danger-subtle fw-bold text-danger">{{ $summary['absent'] }}</td>
                                        <td class="bg-info-subtle fw-bold text-info-emphasis">{{ $summary['leave'] }}</td>
                                        <td class="bg-dark-subtle fw-bold">{{ $summary['percentage'] }}%</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $daysInMonth + 6 }}" class="text-center py-4 text-muted">No records found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Legend -->
                    <div class="mt-4 p-3 bg-light rounded-3 border">
                        <h6 class="fw-bold mb-2 small text-uppercase" style="letter-spacing: 0.5px;">Matrix Legend</h6>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="legend-item"><span class="legend-box cell-present"></span>Present (P)</span>
                            <span class="legend-item"><span class="legend-box cell-absent"></span>Absent (A)</span>
                            <span class="legend-item"><span class="legend-box cell-holiday"></span>Holiday (H)</span>
                            <span class="legend-item"><span class="legend-box cell-leave"></span>Approved Leave (LV)</span>
                            <span class="legend-item"><span class="legend-box cell-sunday"></span>Sunday (S)</span>
                            <span class="legend-item"><span class="legend-box cell-empty"></span>No Record / Future (-)</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function setTab(tabName) {
        const activeTabInputs = document.querySelectorAll('input[name="tab"]');
        activeTabInputs.forEach(input => {
            input.value = tabName;
        });

        document.querySelector('#monthForm input[name="tab"]').value = tabName;
    }

    document.addEventListener('DOMContentLoaded', function() {
        const tabElList = [].slice.call(document.querySelectorAll('button[data-bs-toggle="tab"]'));
        tabElList.forEach(function (tabEl) {
            tabEl.addEventListener('shown.bs.tab', function (event) {
                const target = event.target.id;
                if (target === 'students-tab') {
                    setTab('students');
                } else if (target === 'staff-tab') {
                    setTab('staff');
                }
            });
        });
    });
</script>
@endpush
@include('admin.partials.academic_filter_cascade', [
    'courseSelectId' => 'course_id',
    'batchSelectId' => 'batch_id',
    'sessionSelectId' => 'session_id',
    'semesterSelectId' => 'semester_id',
    'sectionSelectId' => 'section_id',
    'subjectSelectId' => 'subject_id',
    'allSemesterLabel' => 'Select Semester',
    'allSubjectLabel' => 'All Subjects (Show Summary)',
])
@endsection
