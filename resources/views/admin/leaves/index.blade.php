@extends('layouts.admin')

@section('title', 'Leave Management')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <i class="fas fa-chevron-right text-muted mx-2" style="font-size: 0.8rem;"></i>
    <span class="text-primary">Leave Management</span>
@endsection

@section('content')
@php
    $listLabels = [
        'students' => 'Students',
        'faculty' => 'Faculty',
        'staff' => 'Staff',
    ];
    $listLabel = $listLabels[$tab] ?? 'Students';
@endphp
<div class="container-fluid">
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white border-0 p-0">
            <ul class="nav nav-tabs nav-fill" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link py-3 fw-bold {{ $tab === 'students' ? 'active text-primary' : 'text-muted' }}"
                        href="{{ route('admin.leaves.index', ['tab' => 'students']) }}">
                        <i class="fas fa-user-graduate me-2"></i>Students
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link py-3 fw-bold {{ $tab === 'faculty' ? 'active text-primary' : 'text-muted' }}"
                        href="{{ route('admin.leaves.index', ['tab' => 'faculty']) }}">
                        <i class="fas fa-chalkboard-teacher me-2"></i>Faculty
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link py-3 fw-bold {{ $tab === 'staff' ? 'active text-primary' : 'text-muted' }}"
                        href="{{ route('admin.leaves.index', ['tab' => 'staff']) }}">
                        <i class="fas fa-user-tie me-2"></i>Staff
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <form action="{{ route('admin.leaves.index') }}" method="GET" class="mb-4">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0 fw-bold">Filter {{ $listLabel }} Leaves</h5>
            </div>
            <div class="card-body">
                <div class="filter-card-grid">
                    <div class="search-col">
                        <label class="form-label small text-muted fw-bold">Search {{ $listLabel }}</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0 text-muted">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" name="search" id="person_search" class="form-control border-start-0"
                                placeholder="{{ $tab === 'students' ? 'Search by name, email or roll no' : 'Search by name, email or employee ID' }}"
                                value="{{ request('search') }}"
                                autocomplete="off">
                        </div>
                    </div>
                    <div>
                        <label class="form-label small text-muted fw-bold">Select {{ $listLabel }}</label>
                        <select name="user_id" id="person_select" class="form-select form-select-sm">
                            <option value="">All {{ $listLabel }}</option>
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
                    @if($tab === 'students')
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
                    @endif
                    <div class="actions-col">
                        <button type="submit" class="btn btn-sm btn-primary w-100 shadow-sm">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <a href="{{ route('admin.leaves.index', ['tab' => $tab]) }}" class="btn btn-sm btn-light border w-100">
                            Reset
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>



    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h5 class="card-title mb-0 fw-bold">{{ $listLabel }} Leave Requests</h5>
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
                                <td>
                                    @php
                                        $roleLabel = match ($leave->user->role ?? '') {
                                            'faculty' => 'Faculty',
                                            'staff' => 'Staff',
                                            'student' => 'Student',
                                            default => '-',
                                        };
                                    @endphp
                                    {{ $roleLabel }}
                                </td>
                                <td>
                                    {{ \Carbon\Carbon::parse($leave->from_date)->format('d M, Y') }}
                                    <span class="text-muted">to</span>
                                    {{ \Carbon\Carbon::parse($leave->to_date)->format('d M, Y') }}
                                </td>
                                <td style="max-width: 280px;">
                                    <div class="d-flex justify-content-between align-items-center gap-2">
                                        <span class="d-inline-block text-truncate" style="max-width: 160px;">
                                            {{ $leave->reason }}
                                        </span>
                                        <button type="button"
                                            class="btn btn-sm btn-primary flex-shrink-0"
                                            onclick="openLeaveReasonModal({{ \Illuminate\Support\Js::from([
                                                'name' => $leave->user->name ?? 'N/A',
                                                'email' => $leave->user->email ?? '',
                                                'role' => $roleLabel,
                                                'dates' => \Carbon\Carbon::parse($leave->from_date)->format('d M, Y').' to '.\Carbon\Carbon::parse($leave->to_date)->format('d M, Y'),
                                                'status' => ucfirst($leave->status),
                                                'reason' => $leave->reason,
                                                'remarks' => $leave->admin_remarks,
                                            ]) }})">
                                            <i class="fas fa-eye me-1"></i> View
                                        </button>
                                    </div>
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
                                <td colspan="8" class="text-center py-4 text-muted">No {{ strtolower($listLabel) }} leave requests found.</td>
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

<!-- Leave Reason Modal -->
<div class="modal fade" id="leaveReasonModal" tabindex="-1" aria-labelledby="leaveReasonModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="leaveReasonModalLabel">Leave Reason</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <div class="fw-bold" id="reasonViewName"></div>
                    <div class="small text-muted" id="reasonViewEmail"></div>
                </div>
                <div class="row g-2 mb-3 small">
                    <div class="col-6">
                        <div class="text-muted">List</div>
                        <div class="fw-semibold" id="reasonViewRole"></div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted">Status</div>
                        <div class="fw-semibold" id="reasonViewStatus"></div>
                    </div>
                    <div class="col-12">
                        <div class="text-muted">Leave Dates</div>
                        <div class="fw-semibold" id="reasonViewDates"></div>
                    </div>
                </div>
                <label class="form-label fw-bold mb-1">Reason</label>
                <div id="reasonViewBody" class="border rounded p-3 bg-light" style="white-space: pre-wrap;"></div>
                <div id="reasonViewRemarksWrap" class="d-none mt-3">
                    <label class="form-label fw-bold mb-1">Admin Note</label>
                    <div id="reasonViewRemarks" class="border rounded p-3" style="white-space: pre-wrap;"></div>
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-light border px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
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
    function openLeaveReasonModal(payload) {
        document.getElementById('reasonViewName').textContent = payload.name || 'N/A';
        document.getElementById('reasonViewEmail').textContent = payload.email || '';
        document.getElementById('reasonViewRole').textContent = payload.role || '-';
        document.getElementById('reasonViewStatus').textContent = payload.status || '-';
        document.getElementById('reasonViewDates').textContent = payload.dates || '-';
        document.getElementById('reasonViewBody').textContent = payload.reason || 'No reason provided.';

        const remarksWrap = document.getElementById('reasonViewRemarksWrap');
        const remarks = document.getElementById('reasonViewRemarks');
        if (payload.remarks) {
            remarks.textContent = payload.remarks;
            remarksWrap.classList.remove('d-none');
        } else {
            remarks.textContent = '';
            remarksWrap.classList.add('d-none');
        }

        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('leaveReasonModal'));
        modal.show();
    }

    (function () {
        const people = @json($people);
        const searchInput = document.getElementById('person_search');
        const select = document.getElementById('person_select');
        if (!searchInput || !select) return;

        let chosenId = @json((string) request('user_id'));
        const allLabel = @json('All '.$listLabel);

        function label(person) {
            if (person.meta) return person.name + ' — ' + person.meta;
            if (person.email) return person.name + ' — ' + person.email;
            return person.name;
        }

        function renderOptions() {
            const query = searchInput.value.trim().toLowerCase();
            const chosen = chosenId;
            let list = people.filter(function (person) {
                if (!query) return true;
                const haystack = (person.name + ' ' + (person.email || '') + ' ' + (person.meta || '')).toLowerCase();
                return haystack.indexOf(query) !== -1;
            });

            if (chosen) {
                const picked = people.find(function (person) { return String(person.id) === String(chosen); });
                if (picked && !list.some(function (person) { return person.id === picked.id; })) {
                    list = [picked].concat(list);
                }
            }

            select.innerHTML = '';
            const allOption = document.createElement('option');
            allOption.value = '';
            allOption.textContent = allLabel;
            select.appendChild(allOption);

            list.forEach(function (person) {
                const option = document.createElement('option');
                option.value = person.id;
                option.textContent = label(person);
                select.appendChild(option);
            });

            if (chosen && list.some(function (person) { return String(person.id) === String(chosen); })) {
                select.value = chosen;
            }
        }

        searchInput.addEventListener('input', function () {
            chosenId = '';
            renderOptions();
        });

        select.addEventListener('change', function () {
            chosenId = select.value;
        });

        renderOptions();
    })();

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
