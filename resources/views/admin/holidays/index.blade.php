@extends('layouts.admin')

@section('title', 'Holiday Management')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <i class="fas fa-chevron-right text-muted mx-2" style="font-size: 0.8rem;"></i>
    <span class="text-primary">Holiday Management</span>
@endsection

@section('content')
<style>
    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 1px;
        background-color: #e9ecef;
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid #e9ecef;
    }
    .calendar-header-cell {
        background-color: #f8f9fa;
        padding: 12px 10px;
        text-align: center;
        font-weight: 700;
        font-size: 0.85rem;
        color: #495057;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 1px solid #e9ecef;
    }
    .calendar-day-cell {
        background-color: #fff;
        min-height: 100px;
        padding: 10px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: all 0.2s ease-in-out;
        cursor: pointer;
        position: relative;
    }
    .calendar-day-cell:hover:not(.other-month) {
        background-color: #f1f3f5;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.05);
        z-index: 2;
    }
    .calendar-day-cell.other-month {
        background-color: #f8f9fa;
        color: #adb5bd;
        cursor: not-allowed;
    }
    .calendar-day-cell.holiday {
        background-color: #fdf2f2;
        border-left: 3px solid #dc3545;
    }
    .calendar-day-cell.holiday:hover {
        background-color: #fde8e8;
    }
    .day-number {
        font-weight: 600;
        font-size: 0.95rem;
        color: #495057;
    }
    .other-month .day-number {
        color: #ced4da;
    }
    .holiday-badge {
        background-color: #dc3545;
        color: #fff;
        padding: 5px 8px;
        border-radius: 6px;
        font-size: 0.72rem;
        font-weight: 600;
        white-space: normal;
        text-align: left;
        margin-top: 5px;
        box-shadow: 0 1px 3px rgba(220, 53, 69, 0.2);
        line-height: 1.2;
    }
    .add-holiday-hint {
        opacity: 0;
        position: absolute;
        bottom: 8px;
        right: 8px;
        font-size: 0.8rem;
        color: #0d6efd;
        transition: opacity 0.2s ease;
    }
    .calendar-day-cell:hover:not(.other-month):not(.holiday) .add-holiday-hint {
        opacity: 1;
    }
    .holiday-title {
        color: #212529;
    }
    .holiday-desc {
        font-size: 0.75rem;
        color: #6c757d;
        margin-top: 2px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>

<div class="container-fluid">
    <div class="row g-4">
        <!-- Calendar Section -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3">
                    <div>
                        <h5 class="card-title mb-0 fw-bold">
                            <i class="fas fa-calendar-alt text-primary me-2"></i>
                            {{ $currentDate->format('F Y') }}
                        </h5>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        @php
                            $prevMonth = $currentDate->copy()->subMonth()->format('Y-m');
                            $nextMonth = $currentDate->copy()->addMonth()->format('Y-m');
                        @endphp
                        <a href="{{ route('admin.holidays.index', ['month' => $prevMonth]) }}" class="btn btn-outline-secondary btn-sm" title="Previous Month">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                        <form action="{{ route('admin.holidays.index') }}" method="GET" class="m-0">
                            <input type="month" name="month" class="form-control form-control-sm" value="{{ $monthParam }}" onchange="this.form.submit()">
                        </form>
                        <a href="{{ route('admin.holidays.index', ['month' => $nextMonth]) }}" class="btn btn-outline-secondary btn-sm" title="Next Month">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                        <a href="{{ route('admin.holidays.index', ['month' => date('Y-m')]) }}" class="btn btn-primary btn-sm px-3 ms-1">
                            Today
                        </a>
                    </div>
                </div>
                <div class="card-body p-3">
                    <div class="calendar-grid">
                        <!-- Weekdays -->
                        <div class="calendar-header-cell">Sun</div>
                        <div class="calendar-header-cell">Mon</div>
                        <div class="calendar-header-cell">Tue</div>
                        <div class="calendar-header-cell">Wed</div>
                        <div class="calendar-header-cell">Thu</div>
                        <div class="calendar-header-cell">Fri</div>
                        <div class="calendar-header-cell">Sat</div>

                        @php
                            $startDayOfWeek = $currentDate->copy()->startOfMonth()->dayOfWeek;
                            $daysInMonth = $currentDate->daysInMonth;
                            
                            $prevMonthDate = $currentDate->copy()->subMonth();
                            $prevMonthDays = $prevMonthDate->daysInMonth;
                            
                            // Lead days from previous month
                            $leadDays = [];
                            for ($i = $startDayOfWeek - 1; $i >= 0; $i--) {
                                $leadDays[] = $prevMonthDays - $i;
                            }
                            
                            // Days of current month
                            $currentDays = range(1, $daysInMonth);
                            
                            // Total slots needed (rows of 7)
                            $totalSlots = (count($leadDays) + count($currentDays) > 35) ? 42 : 35;
                            $trailDaysCount = $totalSlots - (count($leadDays) + count($currentDays));
                            $trailDays = range(1, $trailDaysCount);
                        @endphp

                        <!-- Render Previous Month Lead Days -->
                        @foreach($leadDays as $day)
                            <div class="calendar-day-cell other-month">
                                <span class="day-number">{{ $day }}</span>
                            </div>
                        @endforeach

                        <!-- Render Current Month Days -->
                        @foreach($currentDays as $day)
                            @php
                                $dateStr = sprintf('%04d-%02d-%02d', $currentDate->year, $currentDate->month, $day);
                                $holiday = $holidays->get($dateStr);
                                $isSunday = Carbon\Carbon::parse($dateStr)->isSunday();
                            @endphp
                            <div class="calendar-day-cell {{ $holiday ? 'holiday' : '' }}" 
                                 @if($holiday)
                                     onclick="openEditHolidayModal({{ $holiday->id }}, '{{ $holiday->name }}', '{{ $holiday->date->format('Y-m-d') }}', '{{ addslashes($holiday->description) }}')"
                                 @else
                                     onclick="openAddHolidayModal('{{ $dateStr }}')"
                                 @endif>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="day-number {{ $isSunday ? 'text-danger' : '' }}">{{ $day }}</span>
                                    @if($isSunday && !$holiday)
                                        <small class="text-danger small fw-semibold" style="font-size: 0.65rem;">Sunday</small>
                                    @endif
                                </div>
                                @if($holiday)
                                    <div class="holiday-badge text-truncate">
                                        <i class="fas fa-umbrella-beach me-1"></i>{{ $holiday->name }}
                                    </div>
                                @else
                                    <div class="add-holiday-hint">
                                        <i class="fas fa-plus-circle"></i>
                                    </div>
                                @endif
                            </div>
                        @endforeach

                        <!-- Render Next Month Trail Days -->
                        @foreach($trailDays as $day)
                            <div class="calendar-day-cell other-month">
                                <span class="day-number">{{ $day }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar List Section -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold">Holidays in Current Month</h6>
                    <span class="badge bg-danger rounded-pill">{{ $holidays->count() }}</span>
                </div>
                <div class="card-body p-0" style="max-height: 350px; overflow-y: auto;">
                    @if($holidays->isEmpty())
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-calendar-times mb-2 d-block" style="font-size: 2rem; opacity: 0.4;"></i>
                            No holidays added for this month.
                        </div>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach($holidays as $holiday)
                                <li class="list-group-item py-3 d-flex justify-content-between align-items-start hover-bg-light" 
                                    style="cursor: pointer;"
                                    onclick="openEditHolidayModal({{ $holiday->id }}, '{{ $holiday->name }}', '{{ $holiday->date->format('Y-m-d') }}', '{{ addslashes($holiday->description) }}')">
                                    <div class="me-auto overflow-hidden">
                                        <div class="fw-bold holiday-title">{{ $holiday->name }}</div>
                                        <span class="text-muted small">
                                            <i class="far fa-calendar-alt text-secondary me-1"></i>
                                            {{ $holiday->date->format('d M, Y (l)') }}
                                        </span>
                                        @if($holiday->description)
                                            <div class="holiday-desc text-muted mt-1">{{ $holiday->description }}</div>
                                        @endif
                                    </div>
                                    <button class="btn btn-sm btn-link text-primary p-0 ms-2" type="button">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold">Upcoming Holidays</h6>
                </div>
                <div class="card-body p-0" style="max-height: 350px; overflow-y: auto;">
                    @if($upcomingHolidays->isEmpty())
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-calendar-check mb-2 d-block" style="font-size: 2rem; opacity: 0.4;"></i>
                            No upcoming holidays.
                        </div>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach($upcomingHolidays as $holiday)
                                <li class="list-group-item py-3 hover-bg-light"
                                    style="cursor: pointer;"
                                    onclick="openEditHolidayModal({{ $holiday->id }}, '{{ $holiday->name }}', '{{ $holiday->date->format('Y-m-d') }}', '{{ addslashes($holiday->description) }}')">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <div class="fw-bold holiday-title">{{ $holiday->name }}</div>
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 py-1 px-2 small" style="font-size: 0.65rem;">
                                            {{ Carbon\Carbon::parse($holiday->date)->diffForHumans() }}
                                        </span>
                                    </div>
                                    <span class="text-muted small">
                                        <i class="far fa-calendar-alt text-secondary me-1"></i>
                                        {{ $holiday->date->format('d M, Y (l)') }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Holiday Modal -->
<div class="modal fade" id="addHolidayModal" tabindex="-1" aria-labelledby="addHolidayModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('admin.holidays.store') }}" method="POST" class="modal-content border-0 shadow-lg">
            @csrf
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fw-bold" id="addHolidayModalLabel">
                    <i class="fas fa-plus-circle me-2"></i>Add Holiday
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label for="add_holiday_date" class="form-label small fw-bold">Holiday Date</label>
                    <input type="date" name="date" id="add_holiday_date" class="form-control" required readonly>
                    <small class="text-muted">Selected date from calendar.</small>
                </div>
                <div class="mb-3">
                    <label for="add_holiday_name" class="form-label small fw-bold">Holiday Name</label>
                    <input type="text" name="name" id="add_holiday_name" class="form-control" placeholder="e.g. Christmas, Diwali, Independence Day" required>
                </div>
                <div class="mb-0">
                    <label for="add_holiday_description" class="form-label small fw-bold">Description (Optional)</label>
                    <textarea name="description" id="add_holiday_description" class="form-control" rows="3" placeholder="Additional details about the holiday..."></textarea>
                </div>
            </div>
            <div class="modal-footer bg-light border-top-0 py-3">
                <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-check-circle me-1"></i>Save Holiday
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit/Delete Holiday Modal -->
<div class="modal fade" id="editHolidayModal" tabindex="-1" aria-labelledby="editHolidayModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg">
            <form id="editHolidayForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header bg-dark text-white py-3">
                    <h5 class="modal-title fw-bold" id="editHolidayModalLabel">
                        <i class="fas fa-edit me-2"></i>Edit Holiday
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="edit_holiday_date" class="form-label small fw-bold">Holiday Date</label>
                        <input type="date" name="date" id="edit_holiday_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_holiday_name" class="form-label small fw-bold">Holiday Name</label>
                        <input type="text" name="name" id="edit_holiday_name" class="form-control" placeholder="e.g. Christmas, Diwali" required>
                    </div>
                    <div class="mb-0">
                        <label for="edit_holiday_description" class="form-label small fw-bold">Description (Optional)</label>
                        <textarea name="description" id="edit_holiday_description" class="form-control" rows="3" placeholder="Additional details about the holiday..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0 py-3 d-flex justify-content-between">
                    <div>
                        <button type="button" class="btn btn-danger px-3" onclick="confirmDeleteHoliday()">
                            <i class="fas fa-trash-alt me-1"></i>Delete
                        </button>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-dark px-4">
                            <i class="fas fa-check-circle me-1"></i>Update
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Hidden delete form -->
<form id="deleteHolidayForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

@push('scripts')
<script>
    let currentHolidayId = null;
    let addModal = null;
    let editModal = null;

    function openAddHolidayModal(dateStr) {
        document.getElementById('add_holiday_date').value = dateStr;
        document.getElementById('add_holiday_name').value = '';
        document.getElementById('add_holiday_description').value = '';
        
        if (!addModal) {
            addModal = new bootstrap.Modal(document.getElementById('addHolidayModal'));
        }
        addModal.show();
    }

    function openEditHolidayModal(id, name, date, description) {
        currentHolidayId = id;
        document.getElementById('edit_holiday_date').value = date;
        document.getElementById('edit_holiday_name').value = name;
        document.getElementById('edit_holiday_description').value = description || '';

        // Dynamically update form action URL using Laravel route helper with placeholder
        const form = document.getElementById('editHolidayForm');
        form.action = "{{ route('admin.holidays.update', ':id') }}".replace(':id', id);
        
        if (!editModal) {
            editModal = new bootstrap.Modal(document.getElementById('editHolidayModal'));
        }
        editModal.show();
    }

    function confirmDeleteHoliday() {
        if (confirm('Are you sure you want to delete this holiday?')) {
            const deleteForm = document.getElementById('deleteHolidayForm');
            deleteForm.action = "{{ route('admin.holidays.destroy', ':id') }}".replace(':id', currentHolidayId);
            deleteForm.submit();
        }
    }
</script>
@endpush
@endsection
