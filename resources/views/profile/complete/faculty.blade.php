@extends('layouts.admin')

@section('title', 'Complete Your Faculty Profile')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-primary text-white p-4">
                    <h4 class="mb-0 fw-bold"><i class="fas fa-chalkboard-teacher me-2"></i>Complete Faculty Profile</h4>
                    <p class="mb-0 small opacity-75">Join our academic team by providing your official details.</p>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('faculty.profile.complete.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row g-3">
                            <div class="col-12">
                                <h6 class="text-primary fw-bold border-bottom pb-2">Employment Information</h6>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Employee ID</label>
                                <input type="text" name="employee_id" class="form-control" placeholder="e.g. EMP101" value="{{ old('employee_id', $profile->employee_id ?? '') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Designation</label>
                                <input type="text" name="designation" class="form-control" placeholder="e.g. Assistant Professor" value="{{ old('designation', $profile->designation ?? '') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Department</label>
                                <input type="text" name="department" class="form-control" placeholder="e.g. Computer Science" value="{{ old('department', $profile->department ?? '') }}" required>
                            </div>

                            <div class="col-12 mt-4">
                                <h6 class="text-primary fw-bold border-bottom pb-2">Academic Allocation</h6>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Course</label>
                                <select id="course_id" class="form-select">
                                    <option value="">Select Course</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}">{{ $course->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Semester</label>
                                <select id="semester_id" class="form-select" disabled>
                                    <option value="">Select Course First</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Subject</label>
                                <select id="subject_select" class="form-select" disabled>
                                    <option value="" disabled selected>Select Semester First</option>
                                </select>
                            </div>

                            <div class="col-12 mt-3">
                                <h6 class="text-secondary small fw-bold mb-2">Selected Subjects:</h6>
                                <div id="selected_subjects_container" class="border rounded p-3 bg-light min-vh-25">
                                    <p class="text-muted small mb-0" id="no_subjects_msg">No subjects selected yet.</p>
                                    <ul class="list-group" id="selected_subjects_list"></ul>
                                </div>
                                <!-- Container for hidden inputs to be submitted -->
                                <div id="hidden_subject_inputs"></div>
                            </div>

                            <div class="col-12 mt-4">
                                <h6 class="text-primary fw-bold border-bottom pb-2">Personal Information</h6>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone Number</label>
                                <input type="text" name="phone_no" class="form-control" value="{{ old('phone_no', $profile->phone_no ?? '') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" name="dob" class="form-control" value="{{ old('dob', $profile && $profile->dob ? (is_string($profile->dob) ? $profile->dob : $profile->dob->format('Y-m-d')) : '') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Gender</label>
                                <select name="gender" class="form-select" required>
                                    <option value="male" {{ (old('gender', $profile->gender ?? '') == 'male' || old('gender', $profile->gender ?? '') == 'Male') ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ (old('gender', $profile->gender ?? '') == 'female' || old('gender', $profile->gender ?? '') == 'Female') ? 'selected' : '' }}>Female</option>
                                    <option value="other" {{ (old('gender', $profile->gender ?? '') == 'other' || old('gender', $profile->gender ?? '') == 'Other') ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Profile Photo</label>
                                <input type="file" name="profile_photo" class="form-control" accept="image/*">
                            </div>
                        </div>

                        <div class="mt-5">
                            <button type="submit" class="btn btn-primary btn-lg w-100 shadow-sm">
                                {{ auth()->user()->is_profile_complete ? 'Update Profile' : 'Submit Faculty Details' }} <i class="fas fa-check-circle ms-2"></i>
                            </button>
                        </div>
                    </form>
                </div>
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

    let selectedSubjects = @json($allocatedSubjects ?? []);

    // Initial render if subjects already allocated
    renderSelectedSubjects();

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

    function removeSubject(subjectId) {
        selectedSubjects = selectedSubjects.filter(sub => sub.id !== subjectId);
        renderSelectedSubjects();
    }

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
</script>
@endsection
