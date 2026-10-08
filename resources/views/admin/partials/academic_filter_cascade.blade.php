@php
    $courseSelectId = $courseSelectId ?? 'course_id';
    $batchSelectId = $batchSelectId ?? 'batch_id';
    $sessionSelectId = $sessionSelectId ?? 'session_id';
    $semesterSelectId = $semesterSelectId ?? 'semester_id';
    $sectionSelectId = $sectionSelectId ?? null;
    $subjectSelectId = $subjectSelectId ?? null;
    $allSemesterLabel = $allSemesterLabel ?? 'All Semesters';
    $allSectionLabel = $allSectionLabel ?? 'All Sections';
    $allSubjectLabel = $allSubjectLabel ?? 'All Subjects';
@endphp
@push('scripts')
<script>
(function () {
    const courseSelect = document.getElementById(@json($courseSelectId));
    const batchSelect = document.getElementById(@json($batchSelectId));
    const sessionSelect = document.getElementById(@json($sessionSelectId));
    const semesterSelect = document.getElementById(@json($semesterSelectId));
    const sectionSelect = @json($sectionSelectId) ? document.getElementById(@json($sectionSelectId)) : null;
    const subjectSelect = @json($subjectSelectId) ? document.getElementById(@json($subjectSelectId)) : null;
    const allSemesterLabel = @json($allSemesterLabel);
    const allSectionLabel = @json($allSectionLabel);
    const allSubjectLabel = @json($allSubjectLabel);

    if (!courseSelect || !batchSelect || !sessionSelect || !semesterSelect) return;

    function semesterLabel(semester) {
        if (!semester) return '';
        const name = semester.name || ('Semester ' + (semester.semester_number || ''));
        return semester.part ? `${name} (${semester.part})` : name;
    }

    function resetBatchSelect(placeholder = 'Select Course First') {
        batchSelect.innerHTML = `<option value="">${placeholder}</option>`;
        batchSelect.disabled = placeholder === 'Select Course First';
    }

    function resetSessionSelect(placeholder = 'Select Batch First') {
        sessionSelect.innerHTML = `<option value="">${placeholder}</option>`;
        sessionSelect.disabled = placeholder === 'Select Batch First';
    }

    function resetSemesterSelect(placeholder = 'Select Course First') {
        semesterSelect.innerHTML = `<option value="">${placeholder}</option>`;
        semesterSelect.disabled = placeholder === 'Select Course First';
        if (semesterSelect.hasAttribute('required') && placeholder === 'Select Course First') {
            semesterSelect.removeAttribute('required');
            semesterSelect.dataset.wasRequired = '1';
        } else if (semesterSelect.dataset.wasRequired === '1' && placeholder !== 'Select Course First') {
            semesterSelect.setAttribute('required', 'required');
        }
    }

    function resetSectionSelect(placeholder = 'Select Semester First') {
        if (!sectionSelect) return;
        sectionSelect.innerHTML = `<option value="">${placeholder}</option>`;
        sectionSelect.disabled = placeholder === 'Select Semester First';
    }

    function resetSubjectSelect(placeholder = 'Select Semester First') {
        if (!subjectSelect) return;
        subjectSelect.innerHTML = `<option value="">${placeholder}</option>`;
        subjectSelect.disabled = placeholder === 'Select Semester First';
    }

    courseSelect.addEventListener('change', function () {
        const courseId = this.value;
        resetSessionSelect();
        resetSectionSelect();
        resetSubjectSelect();

        if (!courseId) {
            resetBatchSelect();
            resetSemesterSelect();
            return;
        }

        resetBatchSelect('Loading...');
        batchSelect.disabled = true;
        resetSemesterSelect('Loading...');
        semesterSelect.disabled = true;

        fetch("{{ route('admin.academy.courses.batches_json', ['course_id' => '__ID__']) }}".replace('__ID__', courseId))
            .then(response => response.json())
            .then(data => {
                resetBatchSelect('All Batches');
                batchSelect.disabled = false;
                (data.batches || []).forEach(batch => {
                    batchSelect.innerHTML += `<option value="${batch.id}">${batch.name}</option>`;
                });
            })
            .catch(() => resetBatchSelect('Error loading batches'));

        fetch("{{ route('admin.academy.courses.semesters_json', ['course_id' => '__COURSE_ID__']) }}".replace('__COURSE_ID__', courseId))
            .then(response => response.json())
            .then(data => {
                resetSemesterSelect(allSemesterLabel);
                semesterSelect.disabled = false;
                (data.semesters || []).forEach(semester => {
                    semesterSelect.innerHTML += `<option value="${semester.id}">${semesterLabel(semester)}</option>`;
                });
            })
            .catch(() => resetSemesterSelect('Error loading semesters'));
    });

    batchSelect.addEventListener('change', function () {
        const batchId = this.value;
        if (!batchId) {
            resetSessionSelect();
            return;
        }

        resetSessionSelect('Loading...');
        sessionSelect.disabled = true;

        fetch("{{ route('admin.academy.batches.sessions_json', ['batch_id' => '__ID__']) }}".replace('__ID__', batchId))
            .then(response => response.json())
            .then(data => {
                resetSessionSelect('All Sessions');
                sessionSelect.disabled = false;
                (data.sessions || []).forEach(session => {
                    sessionSelect.innerHTML += `<option value="${session.id}">${session.name}</option>`;
                });
            })
            .catch(() => resetSessionSelect('Error loading sessions'));
    });

    semesterSelect.addEventListener('change', function () {
        const semesterId = this.value;
        resetSectionSelect();
        resetSubjectSelect();

        if (!semesterId) return;

        if (sectionSelect) {
            resetSectionSelect('Loading...');
            sectionSelect.disabled = true;
            fetch("{{ route('admin.academy.semesters.sections_json', ['semester_id' => '__SEMESTER_ID__']) }}".replace('__SEMESTER_ID__', semesterId))
                .then(response => response.json())
                .then(data => {
                    resetSectionSelect(allSectionLabel);
                    sectionSelect.disabled = false;
                    (data.sections || []).forEach(section => {
                        sectionSelect.innerHTML += `<option value="${section.id}">${section.name}</option>`;
                    });
                })
                .catch(() => resetSectionSelect('Error loading sections'));
        }

        if (subjectSelect) {
            resetSubjectSelect('Loading...');
            subjectSelect.disabled = true;
            fetch("{{ route('admin.academy.semesters.subjects_json', ['semester_id' => '__SEMESTER_ID__']) }}".replace('__SEMESTER_ID__', semesterId))
                .then(response => response.json())
                .then(data => {
                    resetSubjectSelect(allSubjectLabel);
                    subjectSelect.disabled = false;
                    (data.subjects || []).forEach(subject => {
                        const code = subject.code ? ` (${subject.code})` : '';
                        subjectSelect.innerHTML += `<option value="${subject.id}">${subject.name}${code}</option>`;
                    });
                })
                .catch(() => resetSubjectSelect('Error loading subjects'));
        }
    });
})();
</script>
@endpush
