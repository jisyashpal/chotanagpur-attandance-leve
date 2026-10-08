@extends('layouts.admin')

@section('title', 'Complete Your Profile')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-primary text-white p-4">
                    <h4 class="mb-0 fw-bold"><i class="fas fa-user-graduate me-2"></i>{{ auth()->user()->is_profile_complete ? 'Student Profile Details' : 'Complete Student Profile' }}</h4>
                    <p class="mb-0 small opacity-75">
                        Phone number, date of birth, and academic details cannot be changed by you. Contact admin if any correction is needed.
                    </p>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('student.profile.complete.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row g-3">
                            <!-- Basic Info -->
                            <div class="col-12">
                                <h6 class="text-primary fw-bold border-bottom pb-2">Basic Information</h6>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">
                                    Phone Number
                                    @if($locks['phone'])
                                        <span class="badge bg-secondary ms-1"><i class="fas fa-lock me-1"></i>Locked</span>
                                    @endif
                                </label>
                                <input type="text" name="phone_no" class="form-control"
                                    value="{{ old('phone_no', $profile->phone_no ?? '') }}"
                                    {{ $locks['phone'] ? 'readonly' : 'required' }}>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">
                                    Date of Birth
                                    @if($locks['dob'])
                                        <span class="badge bg-secondary ms-1"><i class="fas fa-lock me-1"></i>Locked</span>
                                    @endif
                                </label>
                                <input type="date" name="dob" class="form-control"
                                    value="{{ old('dob', $profile && $profile->dob ? (is_string($profile->dob) ? $profile->dob : $profile->dob->format('Y-m-d')) : '') }}"
                                    {{ $locks['dob'] ? 'readonly' : 'required' }}>
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
                            <div class="col-md-6">
                                <label class="form-label">Father Name</label>
                                <input type="text" name="father_name" class="form-control" value="{{ old('father_name', $profile->father_name ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Mother Name</label>
                                <input type="text" name="mother_name" class="form-control" value="{{ old('mother_name', $profile->mother_name ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">WhatsApp No</label>
                                <input type="text" name="whatsapp_no" class="form-control" value="{{ old('whatsapp_no', $profile->whatsapp_no ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Blood Group</label>
                                <input type="text" name="blood_group" class="form-control" value="{{ old('blood_group', $profile->blood_group ?? '') }}" placeholder="e.g. B+">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Category</label>
                                <input type="text" name="category" class="form-control" value="{{ old('category', $profile->category ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Caste</label>
                                <input type="text" name="caste" class="form-control" value="{{ old('caste', $profile->caste ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Religion</label>
                                <input type="text" name="religion" class="form-control" value="{{ old('religion', $profile->religion ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Aadhar No</label>
                                <input type="text" name="aadhar_no" class="form-control" value="{{ old('aadhar_no', $profile->aadhar_no ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nationality</label>
                                <input type="text" name="nationality" class="form-control" value="{{ old('nationality', $profile->nationality ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Degree Obtained College/University</label>
                                <input type="text" name="degree_obtained_college" class="form-control" value="{{ old('degree_obtained_college', $profile->degree_obtained_college ?? '') }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Permanent Address</label>
                                <textarea name="address" class="form-control" rows="2">{{ old('address', $profile->address ?? '') }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Local Address</label>
                                <textarea name="local_address" class="form-control" rows="2">{{ old('local_address', $profile->local_address ?? '') }}</textarea>
                            </div>

                            <!-- Academic Info -->
                            <div class="col-12 mt-4">
                                <h6 class="text-primary fw-bold border-bottom pb-2">
                                    Academic Details
                                    @if($locks['academic'])
                                        <span class="badge bg-secondary ms-2"><i class="fas fa-lock me-1"></i> Locked</span>
                                    @endif
                                </h6>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Admission Session</label>
                                <select name="session_id" class="form-select" {{ $locks['academic'] ? 'disabled' : 'required' }}>
                                    <option value="">Select Session</option>
                                    @foreach($sessions as $session)
                                        <option value="{{ $session->id }}" {{ old('session_id', $profile->session_id ?? '') == $session->id ? 'selected' : '' }}>{{ $session->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Course</label>
                                <select name="course_id" id="course_id" class="form-select" {{ $locks['academic'] ? 'disabled' : 'required' }}>
                                    <option value="">Select Course</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}" {{ old('course_id', $profile->course_id ?? '') == $course->id ? 'selected' : '' }}>{{ $course->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Semester</label>
                                <select name="semester_id" id="semester_id" class="form-select" {{ $locks['academic'] ? 'disabled' : 'required' }} disabled>
                                    <option value="">Select Course First</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Section</label>
                                <select name="section_id" id="section_id" class="form-select" {{ $locks['academic'] ? 'disabled' : 'required' }} disabled>
                                    <option value="">Select Semester First</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">University Registration Number</label>
                                <input type="text" name="university_registration_number" class="form-control" placeholder="e.g. URN12345" value="{{ old('university_registration_number', $profile->university_registration_number ?? '') }}" {{ $locks['academic'] ? 'disabled' : 'required' }}>
                            </div>
                        </div>

                        <div class="mt-5">
                            <button type="submit" class="btn btn-primary btn-lg w-100 shadow-sm">
                                {{ auth()->user()->is_profile_complete ? 'Update Profile' : 'Complete Registration' }} <i class="fas fa-arrow-right ms-2"></i>
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
    const sectionSelect = document.getElementById('section_id');
    const isAcademicLocked = {{ $locks['academic'] ? 'true' : 'false' }};

    function loadSemesters(courseId, selectedSemesterId = null) {
        const course = courses.find(c => c.id == courseId);
        semesterSelect.innerHTML = '<option value="">Select Semester</option>';
        sectionSelect.innerHTML = '<option value="">Select Semester First</option>';
        sectionSelect.disabled = true;
        if (course && course.semesters) {
            if (!isAcademicLocked) {
                semesterSelect.disabled = false;
            }
            course.semesters.forEach(sem => {
                const option = document.createElement('option');
                option.value = sem.id;
                option.textContent = sem.name;
                if (selectedSemesterId && sem.id == selectedSemesterId) {
                    option.selected = true;
                }
                semesterSelect.appendChild(option);
            });
        } else {
            semesterSelect.disabled = true;
        }
    }

    function loadSections(courseId, semesterId, selectedSectionId = null) {
        const course = courses.find(c => c.id == courseId);
        sectionSelect.innerHTML = '<option value="">Select Section</option>';

        if (!course || !course.semesters || !semesterId) {
            sectionSelect.disabled = true;
            return;
        }

        const semester = course.semesters.find(s => s.id == semesterId);
        if (!semester || !semester.sections) {
            sectionSelect.disabled = true;
            return;
        }

        if (!isAcademicLocked) {
            sectionSelect.disabled = false;
        }

        semester.sections.forEach(section => {
            const option = document.createElement('option');
            option.value = section.id;
            option.textContent = section.name;
            if (selectedSectionId && section.id == selectedSectionId) {
                option.selected = true;
            }
            sectionSelect.appendChild(option);
        });
    }

    if (!isAcademicLocked) {
        courseSelect.addEventListener('change', function() {
            loadSemesters(this.value);
        });

        semesterSelect.addEventListener('change', function() {
            loadSections(courseSelect.value, this.value);
        });
    }

    // Run on initial load
    if (courseSelect.value) {
        loadSemesters(courseSelect.value, "{{ old('semester_id', $profile->semester_id ?? '') }}");
        loadSections(
            courseSelect.value,
            "{{ old('semester_id', $profile->semester_id ?? '') }}",
            "{{ old('section_id', $profile->section_id ?? '') }}"
        );
    }
</script>
@endsection
