@extends('layouts.admin')

@section('title', 'Add Student')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <i class="fas fa-chevron-right text-muted mx-2" style="font-size: 0.8rem;"></i>
    <a href="{{ route('admin.users.students') }}">Students</a>
    <i class="fas fa-chevron-right text-muted mx-2" style="font-size: 0.8rem;"></i>
    <span class="text-primary">Add Student</span>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 mx-auto">
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0 fw-bold">Add New Student</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.users.students.store') }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                        value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" name="email"
                                        class="form-control @error('email') is-invalid @enderror"
                                        value="{{ old('email') }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Password</label>
                                    <input type="text" name="password"
                                        class="form-control @error('password') is-invalid @enderror"
                                        value="{{ old('password') }}"
                                        placeholder="Default: student123">
                                    <small class="text-muted">Leave blank to use default password: student123</small>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Profile Photo</label>
                                    <input type="file" name="profile_photo"
                                        class="form-control @error('profile_photo') is-invalid @enderror" accept="image/*">
                                    @error('profile_photo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Course <span class="text-danger">*</span></label>
                                    <select name="course_id" id="course_id"
                                        class="form-select @error('course_id') is-invalid @enderror" required>
                                        <option value="">Select Course</option>
                                        @foreach($courses as $course)
                                            <option value="{{ $course->id }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>
                                                {{ $course->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('course_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Batch <span class="text-danger">*</span></label>
                                    <select name="batch_id" id="batch_id"
                                        class="form-select @error('batch_id') is-invalid @enderror" required>
                                        <option value="">Select Batch</option>
                                        @foreach($batches as $batch)
                                            <option value="{{ $batch->id }}" {{ old('batch_id') == $batch->id ? 'selected' : '' }}>
                                                {{ $batch->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('batch_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Session <span class="text-danger">*</span></label>
                                    <select name="session_id" id="session_id"
                                        class="form-select @error('session_id') is-invalid @enderror" required>
                                        <option value="">Select Session</option>
                                        @foreach($sessions as $session)
                                            <option value="{{ $session->id }}" {{ old('session_id') == $session->id ? 'selected' : '' }}>
                                                {{ $session->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('session_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Semester <span class="text-danger">*</span></label>
                                    <select name="semester_id" id="semester_id"
                                        class="form-select @error('semester_id') is-invalid @enderror" required>
                                        <option value="">Select Semester</option>
                                        @foreach($semesters as $semester)
                                            <option value="{{ $semester->id }}" {{ old('semester_id') == $semester->id ? 'selected' : '' }}>
                                                {{ $semester->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('semester_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Section <span class="text-danger">*</span></label>
                                    <select name="section_id" id="section_id"
                                        class="form-select @error('section_id') is-invalid @enderror" required>
                                        <option value="">Select Section</option>
                                        @foreach($sections as $section)
                                            <option value="{{ $section->id }}" {{ old('section_id') == $section->id ? 'selected' : '' }}>
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
                                        value="{{ old('roll_no') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">University Registration Number</label>
                                    <input type="text" name="university_registration_number" class="form-control"
                                        value="{{ old('university_registration_number') }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Father Name</label>
                                    <input type="text" name="father_name" class="form-control"
                                        value="{{ old('father_name') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Mother Name</label>
                                    <input type="text" name="mother_name" class="form-control"
                                        value="{{ old('mother_name') }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Gender</label>
                                    <select name="gender" class="form-select">
                                        <option value="">Select Gender</option>
                                        <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                                        <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                                        <option value="Other" {{ old('gender') == 'Other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Date of Birth</label>
                                    <input type="date" name="dob" class="form-control"
                                        value="{{ old('dob') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Blood Group</label>
                                    <input type="text" name="blood_group" class="form-control"
                                        value="{{ old('blood_group') }}"
                                        placeholder="e.g. B+">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Mobile No</label>
                                    <input type="text" name="phone_no" class="form-control"
                                        value="{{ old('phone_no') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">WhatsApp No</label>
                                    <input type="text" name="whatsapp_no" class="form-control"
                                        value="{{ old('whatsapp_no') }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Aadhar No</label>
                                    <input type="text" name="aadhar_no" class="form-control"
                                        value="{{ old('aadhar_no') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Degree Obtained College/University</label>
                                    <input type="text" name="degree_obtained_college" class="form-control"
                                        value="{{ old('degree_obtained_college') }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Religion</label>
                                    <input type="text" name="religion" class="form-control"
                                        value="{{ old('religion') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Category</label>
                                    <input type="text" name="category" class="form-control"
                                        value="{{ old('category') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Caste</label>
                                    <input type="text" name="caste" class="form-control"
                                        value="{{ old('caste') }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Nationality</label>
                                    <input type="text" name="nationality" class="form-control"
                                        value="{{ old('nationality') }}">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Permanent Address</label>
                                <textarea name="address" class="form-control" rows="2">{{ old('address') }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Local Address</label>
                                <textarea name="local_address" class="form-control" rows="2">{{ old('local_address') }}</textarea>
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <a href="{{ route('admin.users.students') }}" class="btn btn-light px-4">Cancel</a>
                                <button type="submit" class="btn btn-primary px-4">Add Student</button>
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

        // Repopulate dependent dropdowns after validation errors
        document.addEventListener('DOMContentLoaded', function () {
            const courseId = document.getElementById('course_id').value;
            const oldBatchId = @json(old('batch_id'));
            const oldSessionId = @json(old('session_id'));
            const oldSemesterId = @json(old('semester_id'));
            const oldSectionId = @json(old('section_id'));

            if (!courseId) {
                return;
            }

            Promise.all([
                fetch("{{ route('admin.academy.courses.batches_json', ['course_id' => '__ID__']) }}".replace('__ID__', courseId)).then(r => r.json()),
                fetch("{{ route('admin.academy.courses.semesters_json', ['course_id' => '__COURSE_ID__']) }}".replace('__COURSE_ID__', courseId)).then(r => r.json()),
            ]).then(([batchData, semesterData]) => {
                const batchSelect = document.getElementById('batch_id');
                const semesterSelect = document.getElementById('semester_id');

                batchSelect.innerHTML = '<option value="">Select Batch</option>';
                (batchData.batches || []).forEach(batch => {
                    const selected = String(batch.id) === String(oldBatchId) ? 'selected' : '';
                    batchSelect.innerHTML += `<option value="${batch.id}" ${selected}>${batch.name}</option>`;
                });

                semesterSelect.innerHTML = '<option value="">Select Semester</option>';
                (semesterData.semesters || []).forEach(semester => {
                    const selected = String(semester.id) === String(oldSemesterId) ? 'selected' : '';
                    semesterSelect.innerHTML += `<option value="${semester.id}" ${selected}>${semester.name}</option>`;
                });

                const loads = [];

                if (oldBatchId) {
                    loads.push(
                        fetch("{{ route('admin.academy.batches.sessions_json', ['batch_id' => '__ID__']) }}".replace('__ID__', oldBatchId))
                            .then(r => r.json())
                            .then(data => {
                                const sessionSelect = document.getElementById('session_id');
                                sessionSelect.innerHTML = '<option value="">Select Session</option>';
                                (data.sessions || []).forEach(session => {
                                    const selected = String(session.id) === String(oldSessionId) ? 'selected' : '';
                                    sessionSelect.innerHTML += `<option value="${session.id}" ${selected}>${session.name}</option>`;
                                });
                            })
                    );
                }

                if (oldSemesterId) {
                    loads.push(
                        fetch("{{ route('admin.academy.semesters.sections_json', ['semester_id' => '__SEMESTER_ID__']) }}".replace('__SEMESTER_ID__', oldSemesterId))
                            .then(r => r.json())
                            .then(data => {
                                const sectionSelect = document.getElementById('section_id');
                                sectionSelect.innerHTML = '<option value="">Select Section</option>';
                                (data.sections || []).forEach(section => {
                                    const selected = String(section.id) === String(oldSectionId) ? 'selected' : '';
                                    sectionSelect.innerHTML += `<option value="${section.id}" ${selected}>${section.name}</option>`;
                                });
                            })
                    );
                }

                return Promise.all(loads);
            });
        });
    </script>
@endsection
