@extends('layouts.admin')

@section('title', 'Leave Management')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <i class="fas fa-chevron-right text-muted mx-2" style="font-size: 0.8rem;"></i>
    <span class="text-primary">Leave Management</span>
@endsection

@section('content')
<div class="container-fluid">
    <form action="{{ route('admin.leaves.index') }}" method="GET" class="mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0 fw-bold">Filter Leaves</h5>
            </div>
            <div class="card-body">
                <div class="filter-card-grid">
                    <div>
                        <label class="form-label small text-muted fw-bold">Role</label>
                        <select name="role" class="form-select form-select-sm">
                            <option value="">All Roles</option>
                            <option value="student" {{ request('role') === 'student' ? 'selected' : '' }}>Student</option>
                            <option value="faculty" {{ request('role') === 'faculty' ? 'selected' : '' }}>Faculty</option>
                            <option value="staff" {{ request('role') === 'staff' ? 'selected' : '' }}>Staff</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label small text-muted fw-bold">User</label>
                        <select name="user_id" class="form-select form-select-sm">
                            <option value="">All Users</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ (string) request('user_id') === (string) $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ ucfirst($user->role) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label small text-muted fw-bold">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label small text-muted fw-bold">From Date</label>
                        <input type="date" name="from_date" class="form-control form-control-sm" value="{{ request('from_date') }}">
                    </div>
                    <div>
                        <label class="form-label small text-muted fw-bold">To Date</label>
                        <input type="date" name="to_date" class="form-control form-control-sm" value="{{ request('to_date') }}">
                    </div>
                    <div>
                        <label class="form-label small text-muted fw-bold">Course</label>
                        <select name="course_id" id="filter_course_id" class="form-select form-select-sm">
                            <option value="">All Courses</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}" {{ (string) request('course_id') === (string) $course->id ? 'selected' : '' }}>
                                    {{ $course->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label small text-muted fw-bold">Batch</label>
                        <select name="batch_id" id="filter_batch_id" class="form-select form-select-sm" {{ request('course_id') ? '' : 'disabled' }}>
                            <option value="">{{ request('course_id') ? 'All Batches' : 'Select Course First' }}</option>
                            @foreach($batches as $batch)
                                <option value="{{ $batch->id }}" {{ (string) request('batch_id') === (string) $batch->id ? 'selected' : '' }}>
                                    {{ $batch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label small text-muted fw-bold">Session</label>
                        <select name="session_id" id="filter_session_id" class="form-select form-select-sm" {{ request('batch_id') ? '' : 'disabled' }}>
                            <option value="">{{ request('batch_id') ? 'All Sessions' : 'Select Batch First' }}</option>
                            @foreach($sessions as $session)
                                <option value="{{ $session->id }}" {{ (string) request('session_id') === (string) $session->id ? 'selected' : '' }}>
                                    {{ $session->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label small text-muted fw-bold">Semester</label>
                        <select name="semester_id" id="filter_semester_id" class="form-select form-select-sm" {{ request('course_id') ? '' : 'disabled' }}>
                            <option value="">{{ request('course_id') ? 'All Semesters' : 'Select Course First' }}</option>
                            @foreach($semesters as $semester)
                                <option value="{{ $semester->id }}" {{ (string) request('semester_id') === (string) $semester->id ? 'selected' : '' }}>
                                    {{ $semester->name }}{{ $semester->part ? ' ('.$semester->part.')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label small text-muted fw-bold">Section</label>
                        <select name="section_id" id="filter_section_id" class="form-select form-select-sm" {{ request('semester_id') ? '' : 'disabled' }}>
                            <option value="">{{ request('semester_id') ? 'All Sections' : 'Select Semester First' }}</option>
                            @foreach($sections as $section)
                                <option value="{{ $section->id }}" {{ (string) request('section_id') === (string) $section->id ? 'selected' : '' }}>
                                    {{ $section->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label small text-muted fw-bold">Subject</label>
                        <select name="subject_id" id="filter_subject_id" class="form-select form-select-sm" {{ request('semester_id') ? '' : 'disabled' }}>
                            <option value="">{{ request('semester_id') ? 'All Subjects' : 'Select Semester First' }}</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ (string) request('subject_id') === (string) $subject->id ? 'selected' : '' }}>
                                    {{ $subject->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="actions-col">
                        <button type="submit" class="btn btn-sm btn-primary w-100 shadow-sm">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <a href="{{ route('admin.leaves.index') }}" class="btn btn-sm btn-light border w-100">
                            Reset
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>



    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h5 class="card-title mb-0 fw-bold">Leave Requests</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">#</th>
                            <th>User</th>
                            <th>Role</th>
                            <th>Leave Dates</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Approved By</th>
                            <th class="text-end pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($leaves as $leave)
                            <tr>
                                <td class="ps-4">{{ ($leaves->currentPage() - 1) * $leaves->perPage() + $loop->iteration }}</td>
                                <td>
                                    <div class="fw-bold">{{ $leave->user->name ?? 'N/A' }}</div>
                                    <small class="text-muted">{{ $leave->user->email ?? '' }}</small>
                                </td>
                                <td>{{ ucfirst($leave->user->role ?? '-') }}</td>
                                <td>
                                    {{ \Carbon\Carbon::parse($leave->from_date)->format('d M, Y') }}
                                    <span class="text-muted">to</span>
                                    {{ \Carbon\Carbon::parse($leave->to_date)->format('d M, Y') }}
                                </td>
                                <td style="max-width: 250px;">
                                    <span class="d-inline-block text-truncate" style="max-width: 250px;" title="{{ $leave->reason }}">
                                        {{ $leave->reason }}
                                    </span>
                                    @if($leave->admin_remarks)
                                        <div class="small text-muted mt-1" style="font-size: 0.8rem; border-top: 1px dashed #dee2e6; padding-top: 4px;">
                                            <strong class="text-secondary"><i class="fas fa-comment-alt me-1"></i>Admin Note:</strong> {{ $leave->admin_remarks }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if($leave->status === 'approved')
                                        <span class="badge bg-success text-white px-3">Approved</span>
                                    @elseif($leave->status === 'rejected')
                                        <span class="badge bg-danger text-white px-3">Rejected</span>
                                    @else
                                        <span class="badge bg-warning text-dark px-3">Pending</span>
                                    @endif
                                </td>
                                <td>{{ $leave->approver->name ?? '-' }}</td>
                                <td class="text-end pe-4">
                                    @if($leave->status === 'pending')
                                        <div class="d-flex justify-content-end gap-2">
                                            <button type="button" class="btn btn-sm btn-success" onclick="openLeaveStatusModal({{ $leave->id }}, 'approved')">
                                                <i class="fas fa-check me-1"></i> Approve
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="openLeaveStatusModal({{ $leave->id }}, 'rejected')">
                                                <i class="fas fa-times me-1"></i> Reject
                                            </button>
                                        </div>
                                    @else
                                        <small class="text-muted">No action</small>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">No leave requests found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($leaves->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $leaves->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Leave Status Modal -->
<div class="modal fade" id="leaveStatusModal" tabindex="-1" aria-labelledby="leaveStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white" id="modalHeader">
                <h5 class="modal-title" id="leaveStatusModalLabel">Update Leave Status</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="leaveStatusForm" method="POST">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" id="modalStatusInput">
                
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="admin_remarks" class="form-label fw-bold">Admin Remarks / Note</label>
                        <textarea class="form-control border-2" id="admin_remarks" name="admin_remarks" rows="4" placeholder="Enter optional notes for approval or reason for rejection..."></textarea>
                        <div class="form-text text-muted">This note will be visible to the user in their leave history.</div>
                    </div>
                </div>
                
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-light border px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="modalSubmitBtn" class="btn px-4">Confirm</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function openLeaveStatusModal(leaveId, status) {
        const modal = document.getElementById('leaveStatusModal');
        const form = document.getElementById('leaveStatusForm');
        const statusInput = document.getElementById('modalStatusInput');
        const modalTitle = document.getElementById('leaveStatusModalLabel');
        const modalHeader = document.getElementById('modalHeader');
        const modalSubmitBtn = document.getElementById('modalSubmitBtn');
        const remarksTextarea = document.getElementById('admin_remarks');

        remarksTextarea.value = '';
        form.action = "{{ url('admin/leaves') }}/" + leaveId + "/status";
        statusInput.value = status;

        if (status === 'approved') {
            modalTitle.textContent = 'Approve Leave Request';
            modalHeader.className = 'modal-header bg-success text-white';
            modalSubmitBtn.textContent = 'Approve Leave';
            modalSubmitBtn.className = 'btn btn-success px-4 shadow-sm';
            remarksTextarea.placeholder = 'Enter optional approval remarks (e.g. Approved as per medical certificate)...';
        } else {
            modalTitle.textContent = 'Reject Leave Request';
            modalHeader.className = 'modal-header bg-danger text-white';
            modalSubmitBtn.textContent = 'Reject Leave';
            modalSubmitBtn.className = 'btn btn-danger px-4 shadow-sm';
            remarksTextarea.placeholder = 'Enter reason for rejection (recommended)...';
        }

        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
    }
</script>
@endpush
@include('admin.partials.academic_filter_cascade', [
    'courseSelectId' => 'filter_course_id',
    'batchSelectId' => 'filter_batch_id',
    'sessionSelectId' => 'filter_session_id',
    'semesterSelectId' => 'filter_semester_id',
    'sectionSelectId' => 'filter_section_id',
    'subjectSelectId' => 'filter_subject_id',
])
@endsection
