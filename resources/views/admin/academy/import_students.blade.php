@extends('layouts.admin')

@section('title', 'Import Students')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <i class="fas fa-chevron-right text-muted mx-2" style="font-size: 0.8rem;"></i>
    <span class="text-primary">Import Students</span>
@endsection


@section('content')
    @if (session('error'))
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle"></i>
            <ul style="margin: 0; padding-left: 1.5rem;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-12 col-lg-10 col-xl-8">
                <div class="card shadow-sm border-0 border-top border-primary border-4">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 fw-bold">Import Students from Excel/CSV</h5>
                        <a href="{{ route('admin.users.students.import.sample') }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-download me-2"></i>Download Sample Format
                        </a>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('admin.users.students.import.store') }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-semibold">Course</label>
                                    <select name="course_id" id="course_id" class="form-select" required>
                                        <option value="">Select Course</option>
                                        @foreach ($courses as $course)
                                            <option value="{{ $course->id }}">{{ $course->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-semibold">Batch</label>
                                    <select name="batch_id" id="batch_id" class="form-select" required>
                                        <option value="">Select Course First</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-semibold">Academic Session</label>
                                    <select name="session_id" id="session_id" class="form-select" required>
                                        <option value="">Select Batch First</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-semibold">Semester</label>
                                    <select name="semester_id" id="semester_id" class="form-select" required>
                                        <option value="">Select Semester</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-semibold">Section</label>
                                    <select name="section_id" id="section_id" class="form-select" required>
                                        <option value="">Select Semester First</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-4 mt-3">
                                <label class="form-label fw-semibold">Upload Excel/CSV File</label>
                                <div class="input-group">
                                    <input type="file" name="file" class="form-control"
                                        accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel"
                                        required>
                                    <label class="input-group-text"><i class="fas fa-file-excel"></i></label>
                                </div>
                                <small class="text-muted">Max file size: 10MB. Supported formats: .csv, .xlsx, .xls</small>
                            </div>

                            <div class="alert alert-info border-0 shadow-sm bg-info-subtle text-info-emphasis">
                                <h6 class="fw-bold"><i class="fas fa-info-circle me-2"></i>Instructions:</h6>
                                <ul class="mb-0 small">
                                    <li>Ensure the file follows the sample format provided.</li>
                                    <li>The file should have headers (in this order): <strong>roll_no, name, father_name, mother_name, gender, dob (DD/MM/YY), mobile_no, whatsapp_no, aadhar_no, email, blood_group, degree_obtained_college, university_registration_number, religion, category, address, local_address, nationality</strong>.</li>
                                    <li><strong>Note:</strong> <strong>address</strong> is Permanent Address. Aliases also supported: <strong>phone_no</strong> for mobile_no, <strong>adhar_no</strong> for aadhar_no, <strong>enrollment_no</strong> for university registration, and <strong>permanent_address</strong> for address.</li>
                                    <li><strong>Required fields:</strong> name and dob only. All other fields are optional and can be left blank.</li>
                                    <li>If email is missing/invalid, system will auto-generate it and continue import.</li>
                                    <li>Passwords will be automatically generated as <strong>student123</strong> (users can
                                        change it later).</li>
                                    <li>Duplicate emails will be skipped.</li>
                                </ul>
                            </div>

                            <div class="text-end mt-4">
                                <button type="submit" class="btn btn-primary px-5 py-2">
                                    <i class="fas fa-upload me-2"></i>Start Import
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const allSections = @json($sections);

        function loadBatches(courseId, selectedBatchId = null) {
            const batchSelect = document.getElementById('batch_id');
            const sessionSelect = document.getElementById('session_id');
            batchSelect.innerHTML = '<option value="">Loading...</option>';
            sessionSelect.innerHTML = '<option value="">Select Batch First</option>';

            if (!courseId) {
                batchSelect.innerHTML = '<option value="">Select Course First</option>';
                return Promise.resolve();
            }

            const url = "{{ route('admin.academy.courses.batches_json', ['course_id' => '__ID__']) }}".replace('__ID__', courseId);
            return fetch(url)
                .then(r => r.json())
                .then(data => {
                    batchSelect.innerHTML = '<option value="">Select Batch</option>';
                    (data.batches || []).forEach(batch => {
                        const selected = String(selectedBatchId) === String(batch.id) ? 'selected' : '';
                        batchSelect.innerHTML += `<option value="${batch.id}" ${selected}>${batch.name}</option>`;
                    });
                })
                .catch(() => {
                    batchSelect.innerHTML = '<option value="">Error loading batches</option>';
                });
        }

        function loadSessions(batchId, selectedSessionId = null) {
            const sessionSelect = document.getElementById('session_id');
            sessionSelect.innerHTML = '<option value="">Loading...</option>';

            if (!batchId) {
                sessionSelect.innerHTML = '<option value="">Select Batch First</option>';
                return Promise.resolve();
            }

            const url = "{{ route('admin.academy.batches.sessions_json', ['batch_id' => '__ID__']) }}".replace('__ID__', batchId);
            return fetch(url)
                .then(r => r.json())
                .then(data => {
                    sessionSelect.innerHTML = '<option value="">Select Session</option>';
                    (data.sessions || []).forEach(session => {
                        const selected = String(selectedSessionId) === String(session.id) ? 'selected' : '';
                        sessionSelect.innerHTML += `<option value="${session.id}" ${selected}>${session.name}</option>`;
                    });
                })
                .catch(() => {
                    sessionSelect.innerHTML = '<option value="">Error loading sessions</option>';
                });
        }

        function loadSemesters(courseId, selectedSemesterId = null) {
            const semesterSelect = document.getElementById('semester_id');
            const sectionSelect = document.getElementById('section_id');
            semesterSelect.innerHTML = '<option value="">Loading...</option>';
            sectionSelect.innerHTML = '<option value="">Select Semester First</option>';

            if (!courseId) {
                semesterSelect.innerHTML = '<option value="">Select Semester</option>';
                return Promise.resolve();
            }

            const url = "{{ route('admin.academy.courses.semesters_json', ['course_id' => '__COURSE_ID__']) }}".replace('__COURSE_ID__', courseId);
            return fetch(url)
                .then(r => r.json())
                .then(data => {
                    semesterSelect.innerHTML = '<option value="">Select Semester</option>';
                    (data.semesters || []).forEach(semester => {
                        const selected = String(selectedSemesterId) === String(semester.id) ? 'selected' : '';
                        semesterSelect.innerHTML += `<option value="${semester.id}" ${selected}>${semester.name}</option>`;
                    });
                })
                .catch(() => {
                    semesterSelect.innerHTML = '<option value="">Error loading semesters</option>';
                });
        }

        function fillSections(semesterId, selectedSectionId = null) {
            const sectionSelect = document.getElementById('section_id');
            sectionSelect.innerHTML = '<option value="">Select Section</option>';
            if (!semesterId) {
                sectionSelect.innerHTML = '<option value="">Select Semester First</option>';
                return;
            }
            allSections.forEach(section => {
                if (String(section.semester_id) === String(semesterId)) {
                    const selected = String(selectedSectionId) === String(section.id) ? 'selected' : '';
                    sectionSelect.innerHTML += `<option value="${section.id}" ${selected}>${section.name}</option>`;
                }
            });
        }

        document.getElementById('course_id').addEventListener('change', function () {
            loadBatches(this.value);
            loadSemesters(this.value);
        });

        document.getElementById('batch_id').addEventListener('change', function () {
            loadSessions(this.value);
        });

        document.getElementById('semester_id').addEventListener('change', function () {
            fillSections(this.value);
        });

        const oldCourse = "{{ old('course_id') }}";
        const oldBatch = "{{ old('batch_id') }}";
        const oldSession = "{{ old('session_id') }}";
        const oldSemester = "{{ old('semester_id') }}";
        const oldSection = "{{ old('section_id') }}";

        if (oldCourse) {
            document.getElementById('course_id').value = oldCourse;
            Promise.all([
                loadBatches(oldCourse, oldBatch),
                loadSemesters(oldCourse, oldSemester),
            ]).then(() => {
                if (oldBatch) {
                    return loadSessions(oldBatch, oldSession);
                }
            }).then(() => {
                if (oldSemester) {
                    fillSections(oldSemester, oldSection);
                }
            });
        }
    </script>
@endpush
