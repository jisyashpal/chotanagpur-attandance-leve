@extends('layouts.admin')

@section('title', 'Faculty Subject Allocation')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <i class="fas fa-chevron-right text-muted mx-2" style="font-size: 0.8rem;"></i>
    <span class="text-primary">Allocations</span>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Filter and Search Form -->
    <form action="{{ route('admin.academy.allocations') }}" method="GET" class="mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="filter-card-grid">
                    <!-- Search Input -->
                    <div class="search-col">
                        <label class="form-label small text-muted fw-bold">Search Allocations</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0 text-muted">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" name="search" class="form-control border-start-0"
                                placeholder="Search by faculty, subject or course..."
                                value="{{ request('search') }}">
                        </div>
                    </div>

                    <!-- Course Filter -->
                    <div>
                        <label class="form-label small text-muted fw-bold">Course</label>
                        <select name="course_id" class="form-select form-select-sm">
                            <option value="">All Courses</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                                    {{ $course->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Semester Filter -->
                    <div>
                        <label class="form-label small text-muted fw-bold">Semester</label>
                        <select name="semester_id" class="form-select form-select-sm">
                            <option value="">All Semesters</option>
                            @foreach($semesters as $semester)
                                <option value="{{ $semester->id }}" {{ request('semester_id') == $semester->id ? 'selected' : '' }}>
                                    {{ $semester->course->name ?? 'N/A' }} - {{ $semester->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Action Buttons -->
                    <div class="actions-col d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-primary w-100 shadow-sm">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <a href="{{ route('admin.academy.allocations') }}" class="btn btn-sm btn-light border w-100">
                            Reset
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 border-top border-primary border-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-bold">Allocate Faculty</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.academy.allocations.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Select Faculty</label>
                            <select name="faculty_id" class="form-select" required>
                                <option value="">Choose Faculty</option>
                                @foreach($faculties as $faculty)
                                    <option value="{{ $faculty->id }}">{{ $faculty->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Course</label>
                                <select id="course_id" class="form-select">
                                    <option value="">Choose Course</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}">{{ $course->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Semester</label>
                                <select id="semester_id" class="form-select" disabled>
                                    <option value="">Select Course First</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Subject</label>
                                <select id="subject_select" class="form-select" disabled>
                                    <option value="" disabled selected>Select Semester First</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <h6 class="text-secondary small fw-bold mb-2">Selected Subjects:</h6>
                            <div id="selected_subjects_container" class="border rounded p-2 bg-light min-vh-25">
                                <p class="text-muted small mb-0" id="no_subjects_msg">No subjects selected yet.</p>
                                <ul class="list-group" id="selected_subjects_list"></ul>
                            </div>
                            <!-- Container for hidden inputs to be submitted -->
                            <div id="hidden_subject_inputs"></div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-link me-2"></i>Allocate Subject
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-bold">Current Allocations</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">#</th>
                                    <th>Faculty</th>
                                    <th>Subject</th>
                                    <th>Course & Sem</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($allocations as $allocation)
                                <tr>
                                    <td class="ps-4">{{ ($allocations->currentPage()-1) * $allocations->perPage() + $loop->iteration }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary text-white rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 30px; height: 30px; font-size: 0.8rem;">
                                                {{ substr($allocation->faculty->name, 0, 1) }}
                                            </div>
                                            <span class="fw-bold">{{ $allocation->faculty->name }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $allocation->subject->name }}</td>
                                    <td class="small">{{ $allocation->semester->course->name }} - {{ $allocation->semester->name }}</td>
                                    <td class="text-end pe-4">
                                        <form action="{{ route('admin.academy.allocations.destroy', $allocation->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this subject allocation?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-light text-danger" title="Delete Allocation">
                                                <i class="fas fa-unlink"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No allocations found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($allocations->hasPages())
                <div class="card-footer bg-white border-0 py-3">
                    {{ $allocations->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    const courses = @json($courses);
    const courseSelect = document.getElementById('course_id');
    const semesterSelect = document.getElementById('semester_id');
    const subjectSelect = document.getElementById('subject_select');
    
    const selectedSubjectsList = document.getElementById('selected_subjects_list');
    const hiddenSubjectInputs = document.getElementById('hidden_subject_inputs');
    const noSubjectsMsg = document.getElementById('no_subjects_msg');

    let selectedSubjects = [];

    courseSelect.addEventListener('change', function() {
        const courseId = this.value;
        const course = courses.find(c => c.id == courseId);
        
        semesterSelect.innerHTML = '<option value="">Select Semester</option>';
        subjectSelect.innerHTML = '<option value="" disabled selected>Select Semester First</option>';
        subjectSelect.disabled = true;

        if (course && course.semesters) {
            course.semesters.forEach(sem => {
                semesterSelect.innerHTML += `<option value="${sem.id}">${sem.name}</option>`;
            });
            semesterSelect.disabled = false;
        } else {
            semesterSelect.disabled = true;
        }
    });

    semesterSelect.addEventListener('change', function() {
        const courseId = courseSelect.value;
        const semesterId = this.value;
        const course = courses.find(c => c.id == courseId);
        
        subjectSelect.innerHTML = '<option value="" disabled selected>Select Subject</option>';
        
        if (course && course.semesters && semesterId) {
            const semester = course.semesters.find(s => s.id == semesterId);
            if (semester && semester.subjects && semester.subjects.length > 0) {
                semester.subjects.forEach(sub => {
                    subjectSelect.innerHTML += `<option value="${sub.id}" data-name="${sub.name}" data-code="${sub.code}" data-course="${course.name}" data-semester="${semester.name}">${sub.name} (${sub.code})</option>`;
                });
                subjectSelect.disabled = false;
            } else {
                subjectSelect.innerHTML = '<option value="" disabled selected>No subjects available</option>';
                subjectSelect.disabled = true;
            }
        } else {
            subjectSelect.innerHTML = '<option value="" disabled selected>Select Semester First</option>';
            subjectSelect.disabled = true;
        }
    });

    subjectSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (!selectedOption || !selectedOption.value) return;

        const subjectId = selectedOption.value;
        
        // Check if already added
        if (selectedSubjects.some(sub => sub.id === subjectId)) {
            alert('This subject is already added!');
            this.selectedIndex = 0;
            return;
        }

        const subjectData = {
            id: subjectId,
            name: selectedOption.getAttribute('data-name'),
            code: selectedOption.getAttribute('data-code'),
            course: selectedOption.getAttribute('data-course'),
            semester: selectedOption.getAttribute('data-semester')
        };

        selectedSubjects.push(subjectData);
        renderSelectedSubjects();
        
        // Reset subject dropdown selection
        this.selectedIndex = 0;
    });

    window.removeSubject = function(subjectId) {
        selectedSubjects = selectedSubjects.filter(sub => sub.id !== subjectId);
        renderSelectedSubjects();
    };

    function renderSelectedSubjects() {
        selectedSubjectsList.innerHTML = '';
        hiddenSubjectInputs.innerHTML = '';
        
        if (selectedSubjects.length === 0) {
            noSubjectsMsg.style.display = 'block';
            return;
        }
        
        noSubjectsMsg.style.display = 'none';

        selectedSubjects.forEach(sub => {
            // Add to UI List
            const li = document.createElement('li');
            li.className = 'list-group-item d-flex justify-content-between align-items-center mb-1 border rounded';
            li.innerHTML = `
                <div>
                    <span class="fw-bold">${sub.name} (${sub.code})</span><br>
                    <small class="text-muted">${sub.course} - ${sub.semester}</small>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeSubject('${sub.id}')">
                    <i class="fas fa-times"></i>
                </button>
            `;
            selectedSubjectsList.appendChild(li);

            // Add hidden input for form submission
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'subject_ids[]';
            input.value = sub.id;
            hiddenSubjectInputs.appendChild(input);
        });
    }

    // Optional form validation to ensure at least one subject is selected
    document.querySelector('form').addEventListener('submit', function(e) {
        if (selectedSubjects.length === 0) {
            e.preventDefault();
            alert('Please select at least one subject to allocate.');
        }
    });
</script>
@endsection
