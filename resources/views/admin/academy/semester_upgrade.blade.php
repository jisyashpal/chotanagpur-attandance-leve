@extends('layouts.admin')

@section('title', 'Semester Upgrade')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <i class="fas fa-chevron-right text-muted mx-2" style="font-size: 0.8rem;"></i>
    <span class="text-primary">Semester Upgrade</span>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row g-4">
            <div class="col-lg-5">
                <div class="card shadow-sm border-0 border-top border-primary border-4 h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0 fw-bold">Select Current Section</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info border-0 bg-info-subtle text-info-emphasis small">
                            Promotion creates a new current semester record for each student. Old academic records and attendance history stay preserved.
                        </div>

                        <form action="{{ route('admin.academy.semester_upgrade') }}" method="GET">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Academic Session</label>
                                <select name="session_id" class="form-select" required>
                                    <option value="">Select Session</option>
                                    @foreach ($sessions as $session)
                                        <option value="{{ $session->id }}" {{ (string) $selectedSessionId === (string) $session->id ? 'selected' : '' }}>
                                            {{ $session->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Course</label>
                                <select name="course_id" id="course_id" class="form-select" required>
                                    <option value="">Select Course</option>
                                    @foreach ($courses as $course)
                                        <option value="{{ $course->id }}" {{ (string) $selectedCourseId === (string) $course->id ? 'selected' : '' }}>
                                            {{ $course->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Source Semester</label>
                                <select name="source_semester_id" id="source_semester_id" class="form-select" required>
                                    <option value="">Select Course First</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Source Section</label>
                                <select name="source_section_id" id="source_section_id" class="form-select" required>
                                    <option value="">Select Semester First</option>
                                </select>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-2"></i>Preview Cohort
                                </button>
                                <a href="{{ route('admin.academy.semester_upgrade') }}" class="btn btn-light border">Reset</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 fw-bold">Promote Students</h5>
                        <span class="badge bg-primary-subtle text-primary border px-3 py-2">{{ $studentCount }} students found</span>
                    </div>
                    <div class="card-body">
                        @if ($selectedSessionId && $selectedCourseId && $selectedSourceSemesterId && $selectedSourceSectionId)
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <div class="border rounded-3 p-3 bg-light h-100">
                                        <div class="text-muted small fw-semibold mb-1">Current Cohort</div>
                                        <div class="fw-bold">Session ID: {{ $selectedSessionId }}</div>
                                        <div>Course ID: {{ $selectedCourseId }}</div>
                                        <div>Semester ID: {{ $selectedSourceSemesterId }}</div>
                                        <div>Section ID: {{ $selectedSourceSectionId }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="border rounded-3 p-3 bg-light h-100">
                                        <div class="text-muted small fw-semibold mb-1">Next Semester Suggestion</div>
                                        @if ($nextSemester)
                                            <div class="fw-bold">{{ $nextSemester->name }}</div>
                                            <div class="text-muted small">Semester Number: {{ $nextSemester->semester_number }}</div>
                                        @else
                                            <div class="text-danger fw-semibold">No higher semester found for this course.</div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <form action="{{ route('admin.academy.semester_upgrade.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="session_id" value="{{ $selectedSessionId }}">
                                <input type="hidden" name="course_id" value="{{ $selectedCourseId }}">
                                <input type="hidden" name="source_semester_id" value="{{ $selectedSourceSemesterId }}">
                                <input type="hidden" name="source_section_id" value="{{ $selectedSourceSectionId }}">

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Target Semester</label>
                                        <select name="target_semester_id" id="target_semester_id" class="form-select" required>
                                            <option value="">Select Target Semester</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Target Section</label>
                                        <select name="target_section_id" id="target_section_id" class="form-select" required>
                                            <option value="">Select Target Semester First</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="alert alert-warning border-0 bg-warning-subtle text-warning-emphasis mt-4 mb-0">
                                    This action marks the selected source profiles as historical and creates new current profiles in the destination semester and section.
                                </div>

                                <div class="text-end mt-4">
                                    <button type="submit" class="btn btn-success px-4" {{ $studentCount < 1 ? 'disabled' : '' }}>
                                        <i class="fas fa-arrow-up me-2"></i>Promote Students
                                    </button>
                                </div>
                            </form>
                        @else
                            <div class="d-flex align-items-center justify-content-center h-100 text-center text-muted py-5">
                                <div>
                                    <i class="fas fa-user-graduate fa-2x mb-3"></i>
                                    <div class="fw-semibold">Choose a current section first.</div>
                                    <div class="small">After previewing the cohort, target semester and target section options will be available here.</div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const semesters = @json($semesters);
        const sections = @json($sections);
        const selectedCourseId = @json((string) $selectedCourseId);
        const selectedSourceSemesterId = @json(old('source_semester_id', (string) $selectedSourceSemesterId));
        const selectedSourceSectionId = @json(old('source_section_id', (string) $selectedSourceSectionId));
        const selectedTargetSemesterId = @json(old('target_semester_id', (string) optional($nextSemester)->id));
        const selectedTargetSectionId = @json(old('target_section_id', ''));

        function populateSemesterOptions(selectId, courseId, selectedValue, placeholder) {
            const select = document.getElementById(selectId);
            if (!select) {
                return;
            }

            select.innerHTML = `<option value="">${placeholder}</option>`;

            if (!courseId) {
                return;
            }

            semesters.forEach((semester) => {
                if (String(semester.course_id) === String(courseId)) {
                    const selected = String(selectedValue) === String(semester.id) ? 'selected' : '';
                    select.innerHTML += `<option value="${semester.id}" ${selected}>${semester.name}</option>`;
                }
            });
        }

        function populateSectionOptions(selectId, semesterId, selectedValue, placeholder) {
            const select = document.getElementById(selectId);
            if (!select) {
                return;
            }

            select.innerHTML = `<option value="">${placeholder}</option>`;

            if (!semesterId) {
                return;
            }

            sections.forEach((section) => {
                if (String(section.semester_id) === String(semesterId)) {
                    const selected = String(selectedValue) === String(section.id) ? 'selected' : '';
                    select.innerHTML += `<option value="${section.id}" ${selected}>${section.name} (${section.code})</option>`;
                }
            });
        }

        const courseSelect = document.getElementById('course_id');
        const sourceSemesterSelect = document.getElementById('source_semester_id');
        const targetSemesterSelect = document.getElementById('target_semester_id');

        if (courseSelect) {
            courseSelect.addEventListener('change', function () {
                populateSemesterOptions('source_semester_id', this.value, '', 'Select Source Semester');
                populateSectionOptions('source_section_id', '', '', 'Select Semester First');
            });
        }

        if (sourceSemesterSelect) {
            sourceSemesterSelect.addEventListener('change', function () {
                populateSectionOptions('source_section_id', this.value, '', 'Select Source Section');
            });
        }

        if (targetSemesterSelect) {
            targetSemesterSelect.addEventListener('change', function () {
                populateSectionOptions('target_section_id', this.value, '', 'Select Target Section');
            });
        }

        populateSemesterOptions('source_semester_id', selectedCourseId, selectedSourceSemesterId, 'Select Source Semester');
        populateSectionOptions('source_section_id', selectedSourceSemesterId, selectedSourceSectionId, 'Select Source Section');
        populateSemesterOptions('target_semester_id', selectedCourseId, selectedTargetSemesterId, 'Select Target Semester');
        populateSectionOptions('target_section_id', selectedTargetSemesterId, selectedTargetSectionId, 'Select Target Section');
    </script>
@endpush