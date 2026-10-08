@extends('layouts.admin')

@section('title', 'Edit Student')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <i class="fas fa-chevron-right text-muted mx-2" style="font-size: 0.8rem;"></i>
    <a href="{{ route('admin.users.students') }}">Students</a>
    <i class="fas fa-chevron-right text-muted mx-2" style="font-size: 0.8rem;"></i>
    <span class="text-primary">Edit Student</span>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0 fw-bold">Edit Student Details</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.users.students.update', $student->id) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Full Name</label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                        value="{{ old('name', $student->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Email Address</label>
                                    <input type="email" name="email"
                                        class="form-control @error('email') is-invalid @enderror"
                                        value="{{ old('email', $student->email) }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">Profile Photo</label>
                                    <input type="file" name="profile_photo"
                                        class="form-control @error('profile_photo') is-invalid @enderror" accept="image/*">
                                    @if($student->getProfilePhotoUrl())
                                        <div class="mt-2">
                                            <img src="{{ $student->getProfilePhotoUrl() }}" alt="Current Photo" class="rounded"
                                                style="height: 60px;">
                                        </div>
                                    @endif
                                    @error('profile_photo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Course</label>
                                    <select name="course_id" id="course_id"
                                        class="form-select @error('course_id') is-invalid @enderror" required>
                                        <option value="">Select Course</option>
                                        @foreach($courses as $course)
                                            <option value="{{ $course->id }}" {{ old('course_id', $student->studentProfile->course_id ?? '') == $course->id ? 'selected' : '' }}>
                                                {{ $course->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('course_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Batch</label>
                                    <select name="batch_id" id="batch_id"
                                        class="form-select @error('batch_id') is-invalid @enderror" required>
                                        <option value="">Select Batch</option>
                                        @foreach($batches as $batch)
                                            <option value="{{ $batch->id }}" {{ old('batch_id', $student->studentProfile->batch_id ?? '') == $batch->id ? 'selected' : '' }}>
                                                {{ $batch->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('batch_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Session</label>
                                    <select name="session_id" id="session_id"
                                        class="form-select @error('session_id') is-invalid @enderror" required>
                                        <option value="">Select Session</option>
                                        @foreach($sessions as $session)
                                            <option value="{{ $session->id }}" {{ old('session_id', $student->studentProfile->session_id ?? '') == $session->id ? 'selected' : '' }}>
                                                {{ $session->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('session_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Semester</label>
                                    <select name="semester_id" id="semester_id"
                                        class="form-select @error('semester_id') is-invalid @enderror" required>
                                        <option value="">Select Semester</option>
                                        @foreach($semesters as $semester)
                                            <option value="{{ $semester->id }}" {{ old('semester_id', $student->studentProfile->semester_id ?? '') == $semester->id ? 'selected' : '' }}>
                                                {{ $semester->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('semester_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Section</label>
                                    <select name="section_id" id="section_id"
                                        class="form-select @error('section_id') is-invalid @enderror" required>
                                        <option value="">Select Section</option>
                                        @foreach($sections as $section)
                                            <option value="{{ $section->id }}" {{ old('section_id', $student->studentProfile->section_id ?? '') == $section->id ? 'selected' : '' }}>
                                                {{ $section->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('section_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Roll No</label>
                                    <input type="text" name="roll_no" class="form-control"
                                        value="{{ old('roll_no', $student->studentProfile->roll_no ?? '') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">University Registration Number</label>
                                    <input type="text" name="university_registration_number" class="form-control"
                                        value="{{ old('university_registration_number', $student->studentProfile->university_registration_number ?? '') }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Father Name</label>
                                    <input type="text" name="father_name" class="form-control"
                                        value="{{ old('father_name', $student->studentProfile->father_name ?? '') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Mother Name</label>
                                    <input type="text" name="mother_name" class="form-control"
                                        value="{{ old('mother_name', $student->studentProfile->mother_name ?? '') }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Gender</label>
                                    <select name="gender" class="form-select">
                                        <option value="">Select Gender</option>
                                        <option value="Male" {{ old('gender', $student->studentProfile->gender ?? '') == 'Male' ? 'selected' : '' }}>Male</option>
                                        <option value="Female" {{ old('gender', $student->studentProfile->gender ?? '') == 'Female' ? 'selected' : '' }}>Female</option>
                                        <option value="Other" {{ old('gender', $student->studentProfile->gender ?? '') == 'Other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Date of Birth</label>
                                    <input type="date" name="dob" class="form-control"
                                        value="{{ old('dob', $student->studentProfile->dob ? $student->studentProfile->dob->format('Y-m-d') : '') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Blood Group</label>
                                    <input type="text" name="blood_group" class="form-control"
                                        value="{{ old('blood_group', $student->studentProfile->blood_group ?? '') }}"
                                        placeholder="e.g. B+">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Mobile No</label>
                                    <input type="text" name="phone_no" class="form-control"
                                        value="{{ old('phone_no', $student->studentProfile->phone_no ?? '') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">WhatsApp No</label>
                                    <input type="text" name="whatsapp_no" class="form-control"
                                        value="{{ old('whatsapp_no', $student->studentProfile->whatsapp_no ?? '') }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Aadhar No</label>
                                    <input type="text" name="aadhar_no" class="form-control"
                                        value="{{ old('aadhar_no', $student->studentProfile->aadhar_no ?? '') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Degree Obtained College/University</label>
                                    <input type="text" name="degree_obtained_college" class="form-control"
                                        value="{{ old('degree_obtained_college', $student->studentProfile->degree_obtained_college ?? '') }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Religion</label>
                                    <input type="text" name="religion" class="form-control"
                                        value="{{ old('religion', $student->studentProfile->religion ?? '') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Category</label>
                                    <input type="text" name="category" class="form-control"
                                        value="{{ old('category', $student->studentProfile->category ?? '') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Caste</label>
                                    <input type="text" name="caste" class="form-control"
                                        value="{{ old('caste', $student->studentProfile->caste ?? '') }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Nationality</label>
                                    <input type="text" name="nationality" class="form-control"
                                        value="{{ old('nationality', $student->studentProfile->nationality ?? '') }}">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Permanent Address</label>
                                <textarea name="address" class="form-control" rows="2">{{ old('address', $student->studentProfile->address ?? '') }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Local Address</label>
                                <textarea name="local_address" class="form-control" rows="2">{{ old('local_address', $student->studentProfile->local_address ?? '') }}</textarea>
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <a href="{{ route('admin.users.students') }}" class="btn btn-light px-4">Cancel</a>
                                <button type="submit" class="btn btn-primary px-4">Update Student</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('course_id').addEventListener('change', function () {
            const courseId = this.value;
            const batchSelect = document.getElementById('batch_id');
            const sessionSelect = document.getElementById('session_id');
            const semesterSelect = document.getElementById('semester_id');
            const sectionSelect = document.getElementById('section_id');

            batchSelect.innerHTML = '<option value="">Loading...</option>';
            sessionSelect.innerHTML = '<option value="">Select Batch First</option>';
            semesterSelect.innerHTML = '<option value="">Loading...</option>';
            sectionSelect.innerHTML = '<option value="">Select Semester First</option>';

            if (!courseId) {
                batchSelect.innerHTML = '<option value="">Select Batch</option>';
                semesterSelect.innerHTML = '<option value="">Select Semester</option>';
                return;
            }

            fetch("{{ route('admin.academy.courses.batches_json', ['course_id' => '__ID__']) }}".replace('__ID__', courseId))
                .then(r => r.json())
                .then(data => {
                    batchSelect.innerHTML = '<option value="">Select Batch</option>';
                    (data.batches || []).forEach(batch => {
                        batchSelect.innerHTML += `<option value="${batch.id}">${batch.name}</option>`;
                    });
                });

            fetch("{{ route('admin.academy.courses.semesters_json', ['course_id' => '__COURSE_ID__']) }}".replace('__COURSE_ID__', courseId))
                .then(r => r.json())
                .then(data => {
                    semesterSelect.innerHTML = '<option value="">Select Semester</option>';
                    (data.semesters || []).forEach(semester => {
                        semesterSelect.innerHTML += `<option value="${semester.id}">${semester.name}</option>`;
                    });
                });
        });

        document.getElementById('batch_id').addEventListener('change', function () {
            const batchId = this.value;
            const sessionSelect = document.getElementById('session_id');
            sessionSelect.innerHTML = '<option value="">Loading...</option>';
            if (!batchId) {
                sessionSelect.innerHTML = '<option value="">Select Batch First</option>';
                return;
            }
            fetch("{{ route('admin.academy.batches.sessions_json', ['batch_id' => '__ID__']) }}".replace('__ID__', batchId))
                .then(r => r.json())
                .then(data => {
                    sessionSelect.innerHTML = '<option value="">Select Session</option>';
                    (data.sessions || []).forEach(session => {
                        sessionSelect.innerHTML += `<option value="${session.id}">${session.name}</option>`;
                    });
                });
        });

        document.getElementById('semester_id').addEventListener('change', function () {
            const semesterId = this.value;
            const sectionSelect = document.getElementById('section_id');

            if (!semesterId) {
                sectionSelect.innerHTML = '<option value="">Select Semester First</option>';
                return;
            }

            const url = "{{ route('admin.academy.semesters.sections_json', ['semester_id' => '__SEMESTER_ID__']) }}".replace('__SEMESTER_ID__', semesterId);
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    sectionSelect.innerHTML = '<option value="">Select Section</option>';
                    if (data && data.sections) {
                        data.sections.forEach(section => {
                            sectionSelect.innerHTML += `<option value="${section.id}">${section.name}</option>`;
                        });
                    }
                })
                .catch(() => {
                    sectionSelect.innerHTML = '<option value="">Error loading sections</option>';
                });
        });
    </script>
@endsection