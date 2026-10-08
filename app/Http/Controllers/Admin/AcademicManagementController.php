<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Batch;
use App\Models\Course;
use App\Models\Section;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\User;
use App\Models\StudentProfile;
use App\Models\FacultySubjectAllocation;
use App\Models\ClassRoutine;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;

class AcademicManagementController extends Controller
{
    // Batches
    public function batches()
    {
        $courses = Course::orderBy('name')->get();
        $batches = Batch::with('course')->withCount('sessions')->latest()->paginate(10);

        return view('admin.academy.batches', compact('batches', 'courses'));
    }

    public function storeBatch(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'name' => 'required|string|max:255',
            'start_year' => 'required|integer|min:2000|max:2100',
            'end_year' => 'required|integer|min:2000|max:2100|gte:start_year',
        ]);

        $exists = Batch::where('course_id', $request->course_id)
            ->whereRaw('LOWER(name) = ?', [strtolower($request->name)])
            ->exists();

        if ($exists) {
            return back()->withErrors(['name' => 'Batch name already exists for this course.'])->withInput();
        }

        Batch::create([
            'course_id' => $request->course_id,
            'name' => $request->name,
            'start_year' => $request->start_year,
            'end_year' => $request->end_year,
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Batch created successfully.');
    }

    public function updateBatch(Request $request, $id)
    {
        $batch = Batch::findOrFail($id);

        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'name' => 'required|string|max:255',
            'start_year' => 'required|integer|min:2000|max:2100',
            'end_year' => 'required|integer|min:2000|max:2100|gte:start_year',
        ]);

        $exists = Batch::where('course_id', $request->course_id)
            ->whereRaw('LOWER(name) = ?', [strtolower($request->name)])
            ->where('id', '!=', $batch->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['name' => 'Batch name already exists for this course.'])->withInput();
        }

        $batch->update([
            'course_id' => $request->course_id,
            'name' => $request->name,
            'start_year' => $request->start_year,
            'end_year' => $request->end_year,
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Batch updated successfully.');
    }

    public function destroyBatch($id)
    {
        $batch = Batch::withCount(['sessions', 'studentProfiles'])->findOrFail($id);

        if ($batch->sessions_count > 0 || $batch->student_profiles_count > 0) {
            return back()->with('error', 'Cannot delete batch that has sessions or students assigned.');
        }

        $batch->delete();

        return back()->with('success', 'Batch deleted successfully.');
    }

    // Sessions
    public function sessions()
    {
        $batches = Batch::with('course')->orderBy('name')->get();
        $sessions = AcademicSession::with('batch.course')->latest()->paginate(10);

        return view('admin.academy.sessions', compact('sessions', 'batches'));
    }

    public function storeSession(Request $request)
    {
        $request->validate([
            'batch_id' => 'required|exists:batches,id',
            'name' => 'required|string|max:255',
            'session_year' => 'required|numeric',
        ]);

        AcademicSession::create([
            'batch_id' => $request->batch_id,
            'name' => $request->name,
            'session_year' => $request->session_year,
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Session created successfully.');
    }

    // Courses
    public function courses()
    {
        $courses = Course::withCount('semesters')->latest()->paginate(10);
        return view('admin.academy.courses', compact('courses'));
    }

    public function storeCourse(Request $request)
    {
        $request->validate(['name' => 'required', 'code' => 'required|unique:courses']);
        Course::create($request->all());
        return back()->with('success', 'Course created successfully.');
    }

    // Semesters
    public function semesters(Request $request)
    {
        $courses = Course::orderBy('name')->get();

        $query = Semester::with('course');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('part', 'like', '%' . $search . '%')
                    ->orWhere('semester_number', 'like', '%' . $search . '%')
                    ->orWhereHas('course', function ($qCourse) use ($search) {
                        $qCourse->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->input('course_id'));
        }

        $semesters = $query->latest()->paginate(10)->withQueryString();

        return view('admin.academy.semesters', compact('semesters', 'courses'));
    }

    public function storeSemester(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'semester_number' => 'required|numeric',
            'part' => 'required|string',
            'name' => 'required|string'
        ]);
        Semester::create($request->all());
        return back()->with('success', 'Semester created successfully.');
    }

    // Sections
    public function sections(Request $request)
    {
        $courses = Course::with('semesters')->orderBy('name')->get();
        $semesters = Semester::with('course')->orderBy('semester_number')->get();

        $query = Section::with('semester.course');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('code', 'like', '%' . $search . '%')
                    ->orWhereHas('semester', function ($qSem) use ($search) {
                        $qSem->where('name', 'like', '%' . $search . '%')
                            ->orWhereHas('course', function ($qCourse) use ($search) {
                                $qCourse->where('name', 'like', '%' . $search . '%');
                            });
                    });
            });
        }

        if ($request->filled('course_id')) {
            $courseId = $request->input('course_id');
            $query->whereHas('semester', function ($qSem) use ($courseId) {
                $qSem->where('course_id', $courseId);
            });
        }

        if ($request->filled('semester_id')) {
            $query->where('semester_id', $request->input('semester_id'));
        }

        $sections = $query->latest()->paginate(10)->withQueryString();

        return view('admin.academy.sections', compact('sections', 'courses', 'semesters'));
    }

    public function storeSection(Request $request)
    {
        $request->validate([
            'semester_id' => 'required|exists:semesters,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50',
        ]);

        $exists = Section::where('semester_id', $request->semester_id)
            ->whereRaw('LOWER(code) = ?', [strtolower($request->code)])
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'code' => 'Section code already exists for the selected semester.',
            ])->withInput();
        }

        Section::create([
            'semester_id' => $request->semester_id,
            'name' => $request->name,
            'code' => strtoupper(trim($request->code)),
        ]);

        return back()->with('success', 'Section created successfully.');
    }

    // Subjects
    public function subjects(Request $request)
    {
        $courses = Course::orderBy('name')->get();
        $semesters = Semester::with('course')->get();

        $query = Subject::with('semester.course');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('code', 'like', '%' . $search . '%')
                    ->orWhereHas('semester.course', function ($qCourse) use ($search) {
                        $qCourse->where('name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('semester', function ($qSem) use ($search) {
                        $qSem->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        if ($request->filled('course_id')) {
            $courseId = $request->input('course_id');
            $query->whereHas('semester', function ($qSem) use ($courseId) {
                $qSem->where('course_id', $courseId);
            });
        }

        if ($request->filled('semester_id')) {
            $query->where('semester_id', $request->input('semester_id'));
        }

        $subjects = $query->latest()->paginate(15)->withQueryString();

        return view('admin.academy.subjects', compact('subjects', 'semesters', 'courses'));
    }

    public function storeSubject(Request $request)
    {
        $request->validate([
            'semester_id' => 'required|exists:semesters,id',
            'name' => 'required',
            'code' => 'required|unique:subjects'
        ]);
        Subject::create($request->all());
        return back()->with('success', 'Subject created successfully.');
    }

    // Faculty Allocation
    public function allocations(Request $request)
    {
        $faculties = User::where('role', 'faculty')->get();
        $courses = Course::with('semesters.subjects')->get();
        $semesters = Semester::with('course')->get();

        $query = FacultySubjectAllocation::with(['faculty', 'subject', 'semester.course']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->whereHas('faculty', function($qFaculty) use ($search) {
                    $qFaculty->where('name', 'like', '%' . $search . '%');
                })
                ->orWhereHas('subject', function($qSubject) use ($search) {
                    $qSubject->where('name', 'like', '%' . $search . '%')
                            ->orWhere('code', 'like', '%' . $search . '%');
                })
                ->orWhereHas('semester.course', function($qCourse) use ($search) {
                    $qCourse->where('name', 'like', '%' . $search . '%');
                });
            });
        }

        if ($request->filled('course_id')) {
            $courseId = $request->input('course_id');
            $query->whereHas('semester', function($qSem) use ($courseId) {
                $qSem->where('course_id', $courseId);
            });
        }

        if ($request->filled('semester_id')) {
            $query->where('semester_id', $request->input('semester_id'));
        }

        $allocations = $query->latest()->paginate(10)->withQueryString();
        
        return view('admin.academy.allocations', compact('faculties', 'courses', 'semesters', 'allocations'));
    }

    public function storeAllocation(Request $request)
    {
        $request->validate([
            'faculty_id' => 'required|exists:users,id',
            'subject_ids' => 'required|array',
            'subject_ids.*' => 'exists:subjects,id',
        ]);

        foreach ($request->subject_ids as $subjectId) {
            $subject = Subject::find($subjectId);
            if ($subject) {
                FacultySubjectAllocation::firstOrCreate([
                    'faculty_id' => $request->faculty_id,
                    'subject_id' => $subjectId,
                    'semester_id' => $subject->semester_id
                ]);
            }
        }

        return back()->with('success', 'Faculty allocated to subjects successfully.');
    }

    // Student Import
    public function importStudentsShow()
    {
        $courses = Course::orderBy('name')->get();
        $batches = Batch::with('course')->orderBy('name')->get();
        $sessions = AcademicSession::with('batch')->orderBy('name')->get();
        $semesters = Semester::with('course')->get();
        $sections = Section::with('semester')->orderBy('name')->get();

        return view('admin.academy.import_students', compact('courses', 'batches', 'sessions', 'semesters', 'sections'));
    }

    public function importStudentsStore(Request $request)
    {
        $request->validate([
            'batch_id' => 'required|exists:batches,id',
            'session_id' => 'required|exists:academic_sessions,id',
            'course_id' => 'required|exists:courses,id',
            'semester_id' => 'required|exists:semesters,id',
            'section_id' => 'required|exists:sections,id',
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240',
        ]);

        $batchBelongsToCourse = Batch::where('id', $request->batch_id)
            ->where('course_id', $request->course_id)
            ->exists();

        if (! $batchBelongsToCourse) {
            return back()->with('error', 'Selected batch does not belong to selected course.');
        }

        $sessionBelongsToBatch = AcademicSession::where('id', $request->session_id)
            ->where('batch_id', $request->batch_id)
            ->exists();

        if (! $sessionBelongsToBatch) {
            return back()->with('error', 'Selected session does not belong to selected batch.');
        }

        // Guard against mismatched course/semester to avoid inconsistent student data.
        $semesterBelongsToCourse = Semester::where('id', $request->semester_id)
            ->where('course_id', $request->course_id)
            ->exists();

        if (!$semesterBelongsToCourse) {
            return back()->with('error', 'Selected semester does not belong to selected course.');
        }

        $sectionBelongsToSemester = Section::where('id', $request->section_id)
            ->where('semester_id', $request->semester_id)
            ->exists();

        if (!$sectionBelongsToSemester) {
            return back()->with('error', 'Selected section does not belong to selected semester.');
        }

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();
        
        $importedCount = 0;
        $skippedCount = 0;
        $invalidCount = 0;
        $invalidRows = [];

        // For simplicity and broad compatibility, we handle CSV directly.
        // If it's Excel, users should ideally save as CSV or we'd use a library.
        // Since we installed maatwebsite/excel, we could use it, but CSV is safer for now.
        
        if (in_array($extension, ['csv', 'txt'])) {
            $path = $file->getRealPath();
            // Use fgetcsv so newlines inside quoted fields (e.g. multi-line addresses) are kept.
            $handle = fopen($path, 'r');
            if ($handle === false) {
                return back()->with('error', 'Unable to read the uploaded CSV file.');
            }

            $data = [];
            while (($row = fgetcsv($handle)) !== false) {
                if (count(array_filter($row, fn($value) => trim((string) $value) !== '')) === 0) {
                    continue;
                }
                $data[] = $row;
            }
            fclose($handle);

            if (count($data) < 2) {
                return back()->with('error', 'CSV file is empty or has no student rows.');
            }

            $rawHeaders = array_shift($data); // Remove headers
            $headers = array_map(function ($header) {
                return $this->normalizeHeader((string) $header);
            }, $rawHeaders ?? []);

            $requiredHeaders = ['name'];
            foreach ($requiredHeaders as $requiredHeader) {
                if (!in_array($requiredHeader, $headers, true)) {
                    return back()->with('error', 'CSV is missing required column: ' . $requiredHeader);
                }
            }

            if (!in_array('dob', $headers, true) && !in_array('date_of_birth', $headers, true)) {
                return back()->with('error', 'CSV is missing required column: dob');
            }

            DB::beginTransaction();
            try {
                foreach ($data as $index => $row) {
                    $csvLine = $index + 2;

                    if (count(array_filter($row, fn($value) => trim((string) $value) !== '')) === 0) {
                        continue;
                    }

                    if (count($row) !== count($headers)) {
                        $invalidCount++;
                        $invalidRows[] = "Line {$csvLine}: column count mismatch.";
                        continue;
                    }

                    $studentData = array_combine($headers, $row);
                    if ($studentData === false) {
                        $invalidCount++;
                        $invalidRows[] = "Line {$csvLine}: invalid row structure.";
                        continue;
                    }

                    $name = trim((string) ($studentData['name'] ?? ''));
                    $rawDob = $studentData['dob'] ?? $studentData['date_of_birth'] ?? null;

                    if ($name === '') {
                        $invalidCount++;
                        $invalidRows[] = "Line {$csvLine}: name is required.";
                        continue;
                    }

                    $dob = $this->normalizeDob($rawDob);
                    if ($dob === null) {
                        $invalidCount++;
                        $invalidRows[] = "Line {$csvLine}: invalid DOB ({$rawDob}). Use dd/mm/yy.";
                        continue;
                    }

                    $email = strtolower(trim((string) ($studentData['email'] ?? '')));
                    if (!$this->isValidImportEmail($email)) {
                        $email = $this->generateImportEmail($name, $dob, $csvLine);
                    }
                    
                    // Check if user already exists
                    if (User::whereRaw('LOWER(email) = ?', [$email])->exists()) {
                        $skippedCount++;
                        continue;
                    }

                    // Create User
                    $user = User::create([
                        'name' => $name,
                        'email' => $email,
                        'password' => Hash::make('student123'),
                        'role' => 'student',
                        'is_profile_complete' => false
                    ]);

                    $registrationNumber = $this->cleanCsvValue(
                        $studentData['university_registration_number']
                            ?? $studentData['university_registration_no']
                            ?? $studentData['university_reg_number']
                            ?? $studentData['registration_number']
                            ?? $studentData['enrollment_no']
                            ?? null
                    );

                    $address = $this->cleanCsvValue(
                        $studentData['address']
                            ?? $studentData['permanent_address']
                            ?? null
                    );
                    $localAddress = $this->cleanCsvValue($studentData['local_address'] ?? null);
                    $phoneNo = $this->cleanCsvValue(
                        $studentData['mobile_no']
                            ?? $studentData['phone_no']
                            ?? $studentData['mobile']
                            ?? null
                    );

                    // Create Student Profile
                    StudentProfile::create([
                        'user_id' => $user->id,
                        'batch_id' => $request->batch_id,
                        'session_id' => $request->session_id,
                        'course_id' => $request->course_id,
                        'semester_id' => $request->semester_id,
                        'section_id' => $request->section_id,
                        'roll_no' => $this->cleanCsvValue($studentData['roll_no'] ?? null),
                        'university_registration_number' => $registrationNumber,
                        'father_name' => $this->cleanCsvValue($studentData['father_name'] ?? null),
                        'mother_name' => $this->cleanCsvValue($studentData['mother_name'] ?? null),
                        'category' => $this->cleanCsvValue($studentData['category'] ?? null),
                        'caste' => $this->cleanCsvValue($studentData['caste'] ?? null),
                        'religion' => $this->cleanCsvValue($studentData['religion'] ?? null),
                        'address' => $address,
                        'local_address' => $localAddress,
                        'aadhar_no' => $this->cleanCsvValue($studentData['aadhar_no'] ?? $studentData['adhar_no'] ?? null),
                        'nationality' => $this->cleanCsvValue($studentData['nationality'] ?? null),
                        'phone_no' => $phoneNo,
                        'whatsapp_no' => $this->cleanCsvValue($studentData['whatsapp_no'] ?? $studentData['whatsapp'] ?? null),
                        'blood_group' => $this->cleanCsvValue($studentData['blood_group'] ?? null),
                        'degree_obtained_college' => $this->cleanCsvValue(
                            $studentData['degree_obtained_college']
                                ?? $studentData['degree_obtained_college_university']
                                ?? null
                        ),
                        'dob' => $dob,
                        'gender' => $this->normalizeGender($studentData['gender'] ?? null),
                        'is_complete' => false
                    ]);

                    $importedCount++;
                }
                DB::commit();

                AuditLogger::log(
                    'imported',
                    null,
                    null,
                    null,
                    "Imported {$importedCount} students (skipped {$skippedCount}, invalid {$invalidCount})",
                    [
                        'batch_id' => (int) $request->batch_id,
                        'session_id' => (int) $request->session_id,
                        'course_id' => (int) $request->course_id,
                        'semester_id' => (int) $request->semester_id,
                        'section_id' => (int) $request->section_id,
                        'imported' => $importedCount,
                        'skipped' => $skippedCount,
                        'invalid' => $invalidCount,
                    ]
                );

                $successMessage = "Import completed: $importedCount imported, $skippedCount duplicate/skipped, $invalidCount invalid rows.";
                if ($invalidCount > 0) {
                    $preview = array_slice($invalidRows, 0, 8);
                    $suffix = $invalidCount > 8 ? " (+" . ($invalidCount - 8) . " more)" : '';
                    $successMessage .= ' Invalid row details: ' . implode(' ', $preview) . $suffix;
                }

                return back()->with('success', $successMessage);
            } catch (\Exception $e) {
                DB::rollBack();
                return back()->with('error', 'Error during import: ' . $e->getMessage());
            }
        } else {
            return back()->with('error', 'Please upload a CSV file. Excel support is being configured.');
        }
    }

    public function downloadSample()
    {
        $headers = [
            'roll_no',
            'name',
            'father_name',
            'mother_name',
            'gender',
            'dob',
            'mobile_no',
            'whatsapp_no',
            'aadhar_no',
            'email',
            'blood_group',
            'degree_obtained_college',
            'university_registration_number',
            'religion',
            'category',
            'address',
            'local_address',
            'nationality',
        ];
        $callback = function() use ($headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            fputcsv($file, [
                '101',
                'John Doe',
                'Ram Prasad',
                'Sita Devi',
                'Male',
                '01/01/05',
                '9876543210',
                '9876543210',
                '123412341234',
                'john@example.com',
                'B+',
                'Ranchi University',
                'URN101',
                'Hindu',
                'General',
                'Permanent Address, Ranchi',
                'Local Address, Ranchi',
                'Indian',
            ]);
            fputcsv($file, [
                '102',
                'Jane Smith',
                '',
                '',
                'Female',
                '15/05/05',
                '9876543211',
                '',
                '',
                'jane@example.com',
                '',
                '',
                'URN102',
                '',
                '',
                '',
                '',
                '',
            ]);
            fclose($file);
        };

        return Response::stream($callback, 200, [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=student_import_sample.csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ]);
    }

    public function updateSession(Request $request, $id)
    {
        $request->validate([
            'batch_id' => 'required|exists:batches,id',
            'name' => 'required|string|max:255',
            'session_year' => 'required|numeric',
        ]);

        $session = AcademicSession::findOrFail($id);
        $session->update([
            'batch_id' => $request->batch_id,
            'name' => $request->name,
            'session_year' => $request->session_year,
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Session updated successfully.');
    }

    public function destroySession($id)
    {
        $session = AcademicSession::findOrFail($id);
        $session->delete();
        return back()->with('success', 'Session deleted successfully.');
    }

    public function updateCourse(Request $request, $id)
    {
        $course = Course::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:courses,code,' . $course->id
        ]);
        $course->update($request->all());
        return back()->with('success', 'Course updated successfully.');
    }

    public function destroyCourse($id)
    {
        $course = Course::findOrFail($id);
        $course->delete();
        return back()->with('success', 'Course deleted successfully.');
    }

    public function updateSemester(Request $request, $id)
    {
        $semester = Semester::findOrFail($id);
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'semester_number' => 'required|numeric',
            'part' => 'required|string',
            'name' => 'required|string'
        ]);
        $semester->update($request->all());
        return back()->with('success', 'Semester updated successfully.');
    }

    public function updateSection(Request $request, $id)
    {
        $section = Section::findOrFail($id);
        $request->validate([
            'semester_id' => 'required|exists:semesters,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50',
        ]);

        $exists = Section::where('semester_id', $request->semester_id)
            ->whereRaw('LOWER(code) = ?', [strtolower($request->code)])
            ->where('id', '!=', $section->id)
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'code' => 'Section code already exists for the selected semester.',
            ])->withInput();
        }

        $section->update([
            'semester_id' => $request->semester_id,
            'name' => $request->name,
            'code' => strtoupper(trim($request->code)),
        ]);

        return back()->with('success', 'Section updated successfully.');
    }

    public function destroySemester($id)
    {
        $semester = Semester::findOrFail($id);
        $semester->delete();
        return back()->with('success', 'Semester deleted successfully.');
    }

    public function destroySection($id)
    {
        $section = Section::findOrFail($id);

        $hasStudents = StudentProfile::where('section_id', $section->id)->exists();
        if ($hasStudents) {
            return back()->with('error', 'Cannot delete section because students are assigned to it.');
        }

        $section->delete();
        return back()->with('success', 'Section deleted successfully.');
    }

    public function updateSubject(Request $request, $id)
    {
        $subject = Subject::findOrFail($id);
        $request->validate([
            'semester_id' => 'required|exists:semesters,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:subjects,code,' . $subject->id
        ]);
        $subject->update($request->all());
        return back()->with('success', 'Subject updated successfully.');
    }

    public function destroySubject($id)
    {
        $subject = Subject::findOrFail($id);
        $subject->delete();
        return back()->with('success', 'Subject deleted successfully.');
    }

    public function destroyAllocation($id)
    {
        $allocation = FacultySubjectAllocation::findOrFail($id);
        $allocation->delete();
        return back()->with('success', 'Allocation deleted successfully.');
    }

    // Class Routines
    public function routines(Request $request)
    {
        $courses = Course::orderBy('name')->get();
        $faculties = User::where('role', 'faculty')->orderBy('name')->get();

        $query = ClassRoutine::with([
            'course',
            'batch',
            'session',
            'semester',
            'section',
            'subject',
            'faculty',
        ]);

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }
        if ($request->filled('batch_id')) {
            $query->where('batch_id', $request->batch_id);
        }
        if ($request->filled('session_id')) {
            $query->where('session_id', $request->session_id);
        }
        if ($request->filled('semester_id')) {
            $query->where('semester_id', $request->semester_id);
        }
        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }
        if ($request->filled('day_of_week')) {
            $query->where('day_of_week', $request->day_of_week);
        }

        $routines = $query
            ->orderBy('day_of_week')
            ->orderBy('period_number')
            ->paginate(15)
            ->withQueryString();

        $days = ClassRoutine::DAYS;

        return view('admin.academy.routines', compact('routines', 'courses', 'faculties', 'days'));
    }

    public function storeRoutine(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'batch_id' => 'required|exists:batches,id',
            'session_id' => 'required|exists:academic_sessions,id',
            'semester_id' => 'required|exists:semesters,id',
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'faculty_id' => 'nullable|exists:users,id',
            'day_of_week' => 'required|integer|between:1,7',
            'period_number' => 'required|integer|min:1|max:12',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'room' => 'nullable|string|max:100',
        ]);

        $hierarchyError = $this->validateRoutineHierarchy($request);
        if ($hierarchyError) {
            return back()->with('error', $hierarchyError)->withInput();
        }

        $slotTaken = ClassRoutine::where('session_id', $request->session_id)
            ->where('section_id', $request->section_id)
            ->where('day_of_week', $request->day_of_week)
            ->where('period_number', $request->period_number)
            ->exists();

        if ($slotTaken) {
            return back()->with('error', 'A routine already exists for this session, section, day and period.')->withInput();
        }

        ClassRoutine::create([
            ...$validated,
            'is_active' => true,
        ]);

        return back()->with('success', 'Class routine created successfully.');
    }

    public function updateRoutine(Request $request, $id)
    {
        $routine = ClassRoutine::findOrFail($id);

        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'batch_id' => 'required|exists:batches,id',
            'session_id' => 'required|exists:academic_sessions,id',
            'semester_id' => 'required|exists:semesters,id',
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'faculty_id' => 'nullable|exists:users,id',
            'day_of_week' => 'required|integer|between:1,7',
            'period_number' => 'required|integer|min:1|max:12',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'room' => 'nullable|string|max:100',
            'is_active' => 'nullable|boolean',
        ]);

        $hierarchyError = $this->validateRoutineHierarchy($request);
        if ($hierarchyError) {
            return back()->with('error', $hierarchyError)->withInput();
        }

        $slotTaken = ClassRoutine::where('session_id', $request->session_id)
            ->where('section_id', $request->section_id)
            ->where('day_of_week', $request->day_of_week)
            ->where('period_number', $request->period_number)
            ->where('id', '!=', $routine->id)
            ->exists();

        if ($slotTaken) {
            return back()->with('error', 'A routine already exists for this session, section, day and period.')->withInput();
        }

        $routine->update([
            ...$validated,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Class routine updated successfully.');
    }

    public function destroyRoutine($id)
    {
        $routine = ClassRoutine::findOrFail($id);
        $routine->delete();

        return back()->with('success', 'Class routine deleted successfully.');
    }

    private function validateRoutineHierarchy(Request $request): ?string
    {
        $batchBelongsToCourse = Batch::where('id', $request->batch_id)
            ->where('course_id', $request->course_id)
            ->exists();
        if (! $batchBelongsToCourse) {
            return 'Selected batch does not belong to selected course.';
        }

        $sessionBelongsToBatch = AcademicSession::where('id', $request->session_id)
            ->where('batch_id', $request->batch_id)
            ->exists();
        if (! $sessionBelongsToBatch) {
            return 'Selected session does not belong to selected batch.';
        }

        $semesterBelongsToCourse = Semester::where('id', $request->semester_id)
            ->where('course_id', $request->course_id)
            ->exists();
        if (! $semesterBelongsToCourse) {
            return 'Selected semester does not belong to selected course.';
        }

        $sectionBelongsToSemester = Section::where('id', $request->section_id)
            ->where('semester_id', $request->semester_id)
            ->exists();
        if (! $sectionBelongsToSemester) {
            return 'Selected section does not belong to selected semester.';
        }

        $subjectBelongsToSemester = Subject::where('id', $request->subject_id)
            ->where('semester_id', $request->semester_id)
            ->exists();
        if (! $subjectBelongsToSemester) {
            return 'Selected subject does not belong to selected semester.';
        }

        if ($request->filled('faculty_id')) {
            $isFaculty = User::where('id', $request->faculty_id)->where('role', 'faculty')->exists();
            if (! $isFaculty) {
                return 'Selected faculty is invalid.';
            }
        }

        return null;
    }

    /**
     * Get semesters by course ID (JSON response).
     */
    public function getSemestersByCourseJson($course_id)
    {
        $course = Course::find($course_id);
        if (!$course) {
            return response()->json([
                'status' => 'error',
                'message' => 'Course not found'
            ], 404);
        }

        $semesters = Semester::where('course_id', $course_id)->orderBy('semester_number')->get();
        return response()->json([
            'status' => 'success',
            'course' => $course->name,
            'semesters' => $semesters
        ]);
    }

    /**
     * Get all semesters (JSON response).
     */
    public function getSemestersJson(Request $request)
    {
        $query = Semester::with('course');
        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }
        $semesters = $query->orderBy('semester_number')->get();
        return response()->json([
            'status' => 'success',
            'semesters' => $semesters
        ]);
    }

    /**
     * Get sections by semester ID (JSON response).
     */
    public function getSectionsBySemesterJson($semester_id)
    {
        $semester = Semester::find($semester_id);
        if (!$semester) {
            return response()->json([
                'status' => 'error',
                'message' => 'Semester not found'
            ], 404);
        }

        $sections = Section::where('semester_id', $semester_id)->orderBy('name')->get();
        return response()->json([
            'status' => 'success',
            'sections' => $sections
        ]);
    }

    public function getSubjectsBySemesterJson($semester_id)
    {
        $semester = Semester::find($semester_id);
        if (! $semester) {
            return response()->json([
                'status' => 'error',
                'message' => 'Semester not found',
            ], 404);
        }

        $subjects = Subject::where('semester_id', $semester_id)->orderBy('name')->get();

        return response()->json([
            'status' => 'success',
            'subjects' => $subjects,
        ]);
    }

    public function getBatchesByCourseJson($course_id)
    {
        $course = Course::find($course_id);
        if (! $course) {
            return response()->json(['status' => 'error', 'message' => 'Course not found'], 404);
        }

        $batches = Batch::where('course_id', $course_id)->orderBy('start_year', 'desc')->get();

        return response()->json([
            'status' => 'success',
            'batches' => $batches,
        ]);
    }

    public function getSessionsByBatchJson($batch_id)
    {
        $batch = Batch::find($batch_id);
        if (! $batch) {
            return response()->json(['status' => 'error', 'message' => 'Batch not found'], 404);
        }

        $sessions = AcademicSession::where('batch_id', $batch_id)->orderBy('session_year')->get();

        return response()->json([
            'status' => 'success',
            'sessions' => $sessions,
        ]);
    }

    private function normalizeHeader(string $header): string
    {
        $header = $this->sanitizeUtf8($header) ?? '';
        $header = preg_replace('/^\xEF\xBB\xBF/', '', $header) ?? $header;
        $header = strtolower($header);
        $header = preg_replace('/[^a-z0-9]+/', '_', $header) ?? $header;
        $header = trim($header, '_');

        return $header;
    }

    private function cleanCsvValue(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = $this->sanitizeUtf8($value) ?? '';
        if ($trimmed === '' || $this->isPlaceholder($trimmed)) {
            return null;
        }

        return $trimmed;
    }

    private function sanitizeUtf8(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $sanitized = $value;

        // Remove invalid UTF-8 bytes first; if conversion fails, try Windows-1252 fallback.
        $utf8 = @iconv('UTF-8', 'UTF-8//IGNORE', $sanitized);
        if ($utf8 === false) {
            $utf8 = @iconv('Windows-1252', 'UTF-8//IGNORE', $sanitized);
        }

        if ($utf8 !== false) {
            $sanitized = $utf8;
        }

        // Drop Unicode replacement character and control bytes.
        $sanitized = str_replace("\xEF\xBF\xBD", '', $sanitized);
        $sanitized = preg_replace('/[\x00-\x1F\x7F]/u', '', $sanitized) ?? $sanitized;

        return trim($sanitized);
    }

    private function isPlaceholder(string $value): bool
    {
        $normalized = strtoupper(trim($value));
        return in_array($normalized, ['NOT GIVEN', 'N/A', 'NA', '-'], true);
    }

    private function isValidImportEmail(string $email): bool
    {
        if ($email === '' || $this->isPlaceholder($email)) {
            return false;
        }

        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    private function normalizeDob(?string $dob): ?string
    {
        $dob = $this->cleanCsvValue($dob);
        if ($dob === null) {
            return null;
        }

        $formats = ['Y-m-d', 'd-m-Y', 'd/m/Y', 'd-m-y', 'd/m/y', 'm/d/Y'];
        foreach ($formats as $format) {
            try {
                $parsed = Carbon::createFromFormat($format, $dob);
                if ($parsed !== false) {
                    return $parsed->format('Y-m-d');
                }
            } catch (\Throwable $e) {
                // Try next known date format.
            }
        }

        try {
            return Carbon::parse($dob)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function normalizeGender(?string $gender): ?string
    {
        $gender = $this->cleanCsvValue($gender);
        if ($gender === null) {
            return null;
        }

        $value = strtoupper($gender);
        if ($value === 'M' || $value === 'MALE') {
            return 'Male';
        }
        if ($value === 'F' || $value === 'FEMALE') {
            return 'Female';
        }

        return ucfirst(strtolower($gender));
    }

    private function generateImportEmail(string $name, string $dob, int $csvLine): string
    {
        $base = strtolower(trim($name));
        $base = preg_replace('/[^a-z0-9]+/', '.', $base) ?? '';
        $base = trim($base, '.');

        if ($base === '') {
            $base = 'student';
        }

        $seed = str_replace('-', '', $dob) . '.' . $csvLine;
        $candidate = $base . '.' . $seed . '@import.local';

        $counter = 1;
        while (User::whereRaw('LOWER(email) = ?', [strtolower($candidate)])->exists()) {
            $candidate = $base . '.' . $seed . '.' . $counter . '@import.local';
            $counter++;
        }

        return strtolower($candidate);
    }
}
