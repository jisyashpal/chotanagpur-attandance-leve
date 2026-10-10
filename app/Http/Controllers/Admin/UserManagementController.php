<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AcademicSession;
use App\Models\Batch;
use App\Models\Course;
use App\Models\Section;
use App\Models\Semester;
use App\Models\StudentProfile;
use App\Models\FacultyProfile;
use App\Models\StaffProfile;
use App\Models\ActivityLog;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserManagementController extends Controller
{
    /**
     * Display a listing of Staff and Faculty users.
     */
    public function staffFaculty(Request $request)
    {
        $query = User::whereIn('role', ['staff', 'faculty'])
            ->with(['facultyProfile', 'staffProfile']);

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter by department
        if ($request->filled('department')) {
            $department = $request->department;
            $query->where(function ($q) use ($department) {
                $q->whereHas('facultyProfile', function ($sub) use ($department) {
                    $sub->where('department', $department);
                })->orWhereHas('staffProfile', function ($sub) use ($department) {
                    $sub->where('department', $department);
                });
            });
        }

        // Search by name, email, employee_id, designation, department
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('facultyProfile', function ($sub) use ($search) {
                      $sub->where('employee_id', 'like', "%{$search}%")
                          ->orWhere('designation', 'like', "%{$search}%")
                          ->orWhere('department', 'like', "%{$search}%");
                  })
                  ->orWhereHas('staffProfile', function ($sub) use ($search) {
                      $sub->where('employee_id', 'like', "%{$search}%")
                          ->orWhere('designation', 'like', "%{$search}%")
                          ->orWhere('department', 'like', "%{$search}%");
                  });
            });
        }

        $users = $query->latest()->paginate(20)->withQueryString();

        // Get distinct departments from both profiles for the filter dropdown
        $facultyDepts = DB::table('faculty_profiles')->whereNotNull('department')->where('department', '!=', '')->pluck('department')->toArray();
        $staffDepts = DB::table('staff_profiles')->whereNotNull('department')->where('department', '!=', '')->pluck('department')->toArray();
        $departments = array_unique(array_merge($facultyDepts, $staffDepts));
        sort($departments);

        return view('admin.users.staff_faculty', compact('users', 'departments'));
    }

    /**
     * Display a listing of Student users.
     */
    public function students(Request $request)
    {
        $users = $this->buildStudentQuery($request)
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $courses = Course::orderBy('name')->get();

        if ($request->filled('course_id')) {
            $batches = Batch::where('course_id', $request->course_id)->orderBy('start_year', 'desc')->get();
            $semesters = Semester::where('course_id', $request->course_id)->orderBy('semester_number')->get();
        } else {
            $batches = collect();
            $semesters = collect();
        }

        if ($request->filled('batch_id')) {
            $sessions = AcademicSession::where('batch_id', $request->batch_id)->orderBy('name')->get();
        } else {
            $sessions = collect();
        }

        if ($request->filled('semester_id')) {
            $sections = Section::where('semester_id', $request->semester_id)->orderBy('name')->get();
        } else {
            $sections = collect();
        }

        return view('admin.users.students', compact('users', 'courses', 'batches', 'sessions', 'semesters', 'sections'));
    }

    /**
     * Export filtered students as CSV.
     */
    public function exportStudents(Request $request)
    {
        $users = $this->buildStudentQuery($request)->latest()->get();

        $filename = 'students_export_' . now()->format('Y_m_d_His') . '.csv';
        $headers = [
            'Roll No',
            'Name',
            'Father Name',
            'Mother Name',
            'Gender',
            'DOB',
            'Mobile No',
            'WhatsApp No',
            'Aadhar No',
            'Email',
            'Blood Group',
            'Degree Obtained College/University',
            'University Registration Number',
            'Religion',
            'Category',
            'Permanent Address',
            'Local Address',
            'Nationality',
            'Session',
            'Course',
            'Semester',
            'Section',
            'Status',
        ];

        return response()->streamDownload(function () use ($users, $headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);

            foreach ($users as $user) {
                $profile = $user->studentProfile;

                fputcsv($file, [
                    $profile?->roll_no ?? '',
                    $user->name,
                    $profile?->father_name ?? '',
                    $profile?->mother_name ?? '',
                    $profile?->gender ?? '',
                    $profile?->dob?->format('d/m/Y') ?? '',
                    $profile?->phone_no ?? '',
                    $profile?->whatsapp_no ?? '',
                    $profile?->aadhar_no ?? '',
                    $user->email,
                    $profile?->blood_group ?? '',
                    $profile?->degree_obtained_college ?? '',
                    $profile?->university_registration_number ?? '',
                    $profile?->religion ?? '',
                    $profile?->category ?? '',
                    $profile?->address ?? '',
                    $profile?->local_address ?? '',
                    $profile?->nationality ?? '',
                    $profile?->session?->name ?? '',
                    $profile?->course?->name ?? '',
                    $profile?->semester?->name ?? '',
                    $profile?->section?->name ?? '',
                    $user->is_profile_complete ? 'Active' : 'Pending',
                ]);
            }

            fclose($file);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    /**
     * Build the filtered student listing query.
     */
    private function buildStudentQuery(Request $request)
    {
        $query = User::where('role', 'student')
            ->with(['studentProfile.course', 'studentProfile.semester', 'studentProfile.section', 'studentProfile.session', 'studentProfile.batch']);

        if ($request->filled('course_id')) {
            $query->whereHas('studentProfile', function ($q) use ($request) {
                $q->where('course_id', $request->course_id);
            });
        }

        if ($request->filled('batch_id')) {
            $query->whereHas('studentProfile', function ($q) use ($request) {
                $q->where('batch_id', $request->batch_id);
            });
        }

        if ($request->filled('session_id')) {
            $query->whereHas('studentProfile', function ($q) use ($request) {
                $q->where('session_id', $request->session_id);
            });
        }

        if ($request->filled('semester_id')) {
            $query->whereHas('studentProfile', function ($q) use ($request) {
                $q->where('semester_id', $request->semester_id);
            });
        }

        if ($request->filled('section_id')) {
            $query->whereHas('studentProfile', function ($q) use ($request) {
                $q->where('section_id', $request->section_id);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('studentProfile', function ($sp) use ($search) {
                        $sp->where('roll_no', 'like', "%{$search}%")
                            ->orWhere('university_registration_number', 'like', "%{$search}%");
                    });
            });
        }

        return $query;
    }

    /**
     * Display the specified staff or faculty member.
     */
    public function showStaffFaculty($id)
    {
        $user = User::findOrFail($id);
        $user->loadCompleteProfile();
        return view('admin.users.show_staff_faculty', compact('user'));
    }

    /**
     * Show the form for editing the specified staff/faculty.
     */
    public function editStaffFaculty($id)
    {
        $user = User::findOrFail($id);
        $profile = $this->getUserProfile($user);
        
        return view('admin.users.edit_staff_faculty', compact('user', 'profile'));
    }

    /**
     * Update the specified staff/faculty in storage.
     */
    public function updateStaffFaculty(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'employee_id' => 'nullable|string|max:50',
            'designation' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'phone_no' => 'nullable|string|max:20',
            'dob' => 'nullable|date',
            'gender' => 'nullable|string|max:20',
            'profile_photo' => 'nullable|image|max:2048',
        ]);

        DB::beginTransaction();
        try {
            // Update User
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);

            // Update Profile
            $profile = $this->getUserProfile($user);
            
            if (!$profile) {
                if ($user->role === 'faculty') {
                    $profile = new FacultyProfile(['user_id' => $user->id]);
                } else {
                    $profile = new StaffProfile(['user_id' => $user->id]);
                }
            }

            $profile->fill([
                'employee_id' => $request->employee_id,
                'designation' => $request->designation,
                'department' => $request->department,
                'phone_no' => $request->phone_no,
                'dob' => $request->dob,
                'gender' => $request->gender,
                'is_complete' => true
            ]);

            if ($request->hasFile('profile_photo')) {
                $photoPath = $this->handlePhotoUpload(
                    $request->file('profile_photo'), 
                    $user->id, 
                    'upload/profile', 
                    $user->profile_photo
                );
                $user->profile_photo = $photoPath;
                $user->save();
            }

            $profile->save();
            
            // Mark user profile as complete if it wasn't
            if (!$user->is_profile_complete) {
                $user->is_profile_complete = true;
                $user->save();
            }

            DB::commit();
            return redirect()->route('admin.users.staff_faculty')->with('success', 'Profile updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error updating profile: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified staff/faculty from storage.
     */
    public function destroyStaffFaculty($id)
    {
        $user = User::findOrFail($id);
        
        DB::beginTransaction();
        try {
            // Delete profile photo if exists
            if ($user->profile_photo) {
                $this->deleteOldPhoto($user->profile_photo);
            }
            
            // Delete Profile
            $profile = $this->getUserProfile($user);
            if ($profile) {
                $profile->delete();
            }
            
            // Delete User
            $user->delete();

            DB::commit();
            return redirect()->route('admin.users.staff_faculty')->with('success', 'User deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error deleting user: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new student.
     */
    public function createStudent()
    {
        $courses = Course::orderBy('name')->get();
        $batches = collect();
        $sessions = collect();
        $semesters = collect();
        $sections = collect();

        return view('admin.users.create_student', compact('courses', 'batches', 'sessions', 'semesters', 'sections'));
    }

    /**
     * Store a newly created student in storage.
     */
    public function storeStudent(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'nullable|string|min:6|max:100',
            'batch_id' => 'required|exists:batches,id',
            'session_id' => 'required|exists:academic_sessions,id',
            'course_id' => 'required|exists:courses,id',
            'semester_id' => 'required|exists:semesters,id',
            'section_id' => 'required|exists:sections,id',
            'roll_no' => 'nullable|string|max:50',
            'university_registration_number' => 'nullable|string|max:100',
            'father_name' => 'nullable|string|max:255',
            'mother_name' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:100',
            'caste' => 'nullable|string|max:100',
            'religion' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:1000',
            'local_address' => 'nullable|string|max:1000',
            'aadhar_no' => 'nullable|string|max:20',
            'nationality' => 'nullable|string|max:100',
            'phone_no' => 'nullable|string|max:20',
            'whatsapp_no' => 'nullable|string|max:20',
            'blood_group' => 'nullable|string|max:20',
            'degree_obtained_college' => 'nullable|string|max:255',
            'dob' => 'nullable|date',
            'gender' => 'nullable|string|max:20',
            'profile_photo' => 'nullable|image|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $batchBelongsToCourse = Batch::where('id', $request->batch_id)
                ->where('course_id', $request->course_id)
                ->exists();

            if (! $batchBelongsToCourse) {
                return back()->withInput()->with('error', 'Selected batch does not belong to selected course.');
            }

            $sessionBelongsToBatch = AcademicSession::where('id', $request->session_id)
                ->where('batch_id', $request->batch_id)
                ->exists();

            if (! $sessionBelongsToBatch) {
                return back()->withInput()->with('error', 'Selected session does not belong to selected batch.');
            }

            $semesterBelongsToCourse = Semester::where('id', $request->semester_id)
                ->where('course_id', $request->course_id)
                ->exists();

            if (! $semesterBelongsToCourse) {
                return back()->withInput()->with('error', 'Selected semester does not belong to selected course.');
            }

            $sectionBelongsToSemester = Section::where('id', $request->section_id)
                ->where('semester_id', $request->semester_id)
                ->exists();

            if (! $sectionBelongsToSemester) {
                return back()->withInput()->with('error', 'Selected section does not belong to selected semester.');
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->filled('password') ? $request->password : 'student123',
                'role' => 'student',
                'is_profile_complete' => true,
            ]);

            $profile = new StudentProfile(['user_id' => $user->id]);
            $profile->fill([
                'batch_id' => $request->batch_id,
                'session_id' => $request->session_id,
                'course_id' => $request->course_id,
                'semester_id' => $request->semester_id,
                'section_id' => $request->section_id,
                'roll_no' => $request->roll_no,
                'university_registration_number' => $request->university_registration_number,
                'father_name' => $request->father_name,
                'mother_name' => $request->mother_name,
                'category' => $request->category,
                'caste' => $request->caste,
                'religion' => $request->religion,
                'address' => $request->address,
                'local_address' => $request->local_address,
                'aadhar_no' => $request->aadhar_no,
                'nationality' => $request->nationality,
                'phone_no' => $request->phone_no,
                'whatsapp_no' => $request->whatsapp_no,
                'blood_group' => $request->blood_group,
                'degree_obtained_college' => $request->degree_obtained_college,
                'dob' => $request->dob,
                'gender' => $request->gender,
                'is_complete' => true,
            ]);

            if ($request->hasFile('profile_photo')) {
                $photoPath = $this->handlePhotoUpload(
                    $request->file('profile_photo'),
                    $user->id,
                    'upload/profile'
                );
                $user->profile_photo = $photoPath;
                $user->save();
            }

            $profile->save();

            DB::commit();
            return redirect()->route('admin.users.students')->with('success', 'Student added successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error adding student: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified student.
     */
    public function editStudent($id)
    {
        $student = User::with('studentProfile')->findOrFail($id);
        $courses = Course::orderBy('name')->get();
        $batches = Batch::where('course_id', $student->studentProfile->course_id ?? 0)->orderBy('name')->get();
        $sessions = AcademicSession::where('batch_id', $student->studentProfile->batch_id ?? 0)->orderBy('name')->get();
        $semesters = Semester::where('course_id', $student->studentProfile->course_id ?? 0)->get();
        $sections = Section::where('semester_id', $student->studentProfile->semester_id ?? 0)->get();

        return view('admin.users.edit_student', compact('student', 'sessions', 'courses', 'batches', 'semesters', 'sections'));
    }

    /**
     * Update the specified student in storage.
     */
    public function updateStudent(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'batch_id' => 'required|exists:batches,id',
            'session_id' => 'required|exists:academic_sessions,id',
            'course_id' => 'required|exists:courses,id',
            'semester_id' => 'required|exists:semesters,id',
            'section_id' => 'required|exists:sections,id',
            'roll_no' => 'nullable|string|max:50',
            'university_registration_number' => 'nullable|string|max:100',
            'father_name' => 'nullable|string|max:255',
            'mother_name' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:100',
            'caste' => 'nullable|string|max:100',
            'religion' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:1000',
            'local_address' => 'nullable|string|max:1000',
            'aadhar_no' => 'nullable|string|max:20',
            'nationality' => 'nullable|string|max:100',
            'phone_no' => 'nullable|string|max:20',
            'whatsapp_no' => 'nullable|string|max:20',
            'blood_group' => 'nullable|string|max:20',
            'degree_obtained_college' => 'nullable|string|max:255',
            'dob' => 'nullable|date',
            'gender' => 'nullable|string|max:20',
            'profile_photo' => 'nullable|image|max:2048',
        ]);

        DB::beginTransaction();
        try {
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

            // Update User
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);

            // Update Student Profile
            $profile = $user->studentProfile;
            if (!$profile) {
                $profile = new StudentProfile(['user_id' => $user->id]);
            }

            $profile->fill([
                'batch_id' => $request->batch_id,
                'session_id' => $request->session_id,
                'course_id' => $request->course_id,
                'semester_id' => $request->semester_id,
                'section_id' => $request->section_id,
                'roll_no' => $request->roll_no,
                'university_registration_number' => $request->university_registration_number,
                'father_name' => $request->father_name,
                'mother_name' => $request->mother_name,
                'category' => $request->category,
                'caste' => $request->caste,
                'religion' => $request->religion,
                'address' => $request->address,
                'local_address' => $request->local_address,
                'aadhar_no' => $request->aadhar_no,
                'nationality' => $request->nationality,
                'phone_no' => $request->phone_no,
                'whatsapp_no' => $request->whatsapp_no,
                'blood_group' => $request->blood_group,
                'degree_obtained_college' => $request->degree_obtained_college,
                'dob' => $request->dob,
                'gender' => $request->gender,
                'is_complete' => true
            ]);

            if ($request->hasFile('profile_photo')) {
                $photoPath = $this->handlePhotoUpload(
                    $request->file('profile_photo'),
                    $user->id,
                    'upload/profile',
                    $user->profile_photo
                );
                $user->profile_photo = $photoPath;
                $user->save();
            }

            $profile->save();

            // Mark user profile as complete if it wasn't
            if (!$user->is_profile_complete) {
                $user->is_profile_complete = true;
                $user->save();
            }

            DB::commit();
            return redirect()->route('admin.users.students')->with('success', 'Student updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error updating student: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified student from storage.
     */
    public function destroyStudent($id)
    {
        $user = User::findOrFail($id);
        
        DB::beginTransaction();
        try {
            // Delete profile photo if exists
            if ($user->profile_photo) {
                $this->deleteOldPhoto($user->profile_photo);
            }
            
            // Delete Profile
            if ($user->studentProfile) {
                $user->studentProfile->delete();
            }
            
            // Delete User
            $user->delete();

            DB::commit();
            return redirect()->route('admin.users.students')->with('success', 'Student deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error deleting student: ' . $e->getMessage());
        }
    }
    /**
     * Display all active logins (sessions).
     */
    public function activeLogins()
    {
        $sessions = DB::table('personal_access_tokens')
            ->join('users', 'personal_access_tokens.tokenable_id', '=', 'users.id')
            ->leftJoin('user_devices', function($join) {
                $join->on('personal_access_tokens.name', '=', 'user_devices.device_id')
                     ->on('personal_access_tokens.tokenable_id', '=', 'user_devices.user_id');
            })
            ->select(
                'personal_access_tokens.*',
                'users.name as user_name',
                'users.email as user_email',
                'users.role as user_role',
                'user_devices.device_name',
                'user_devices.platform'
            )
            ->where('personal_access_tokens.tokenable_type', 'App\Models\User')
            ->latest('personal_access_tokens.created_at')
            ->paginate(15);

        return view('admin.users.logins', compact('sessions'));
    }

    /**
     * Block or unblock a student, staff, or faculty account.
     */
    public function toggleActive($id)
    {
        $user = User::findOrFail($id);

        if (! in_array($user->role, ['student', 'faculty', 'staff'], true)) {
            return back()->with('error', 'Admin accounts cannot be blocked.');
        }

        $wasActive = (bool) $user->is_active;
        $user->is_active = ! $wasActive;
        $user->save();

        if (! $user->is_active) {
            $user->tokens()->delete();
            DB::table('sessions')->where('user_id', $user->id)->delete();
        }

        AuditLogger::log(
            $user->is_active ? 'unblock' : 'block',
            $user,
            ['is_active' => $wasActive],
            ['is_active' => (bool) $user->is_active],
            $user->is_active ? 'User unblocked' : 'User blocked'
        );

        return back()->with(
            'success',
            $user->is_active ? 'User unblocked successfully.' : 'User blocked successfully.'
        );
    }

    /**
     * Revoke a specific login session.
     */
    public function revokeLogin($id)
    {
        DB::table('personal_access_tokens')->where('id', $id)->delete();
        return back()->with('success', 'Session revoked successfully.');
    }

    /**
     * Display the specified student profile.
     */
    public function showStudent($id)
    {
        $user = User::findOrFail($id);
        $user->loadCompleteProfile();

        $activityLogs = ActivityLog::with('actor')
            ->where(function ($q) use ($user) {
                $q->where('actor_id', $user->id);
                if ($user->studentProfile) {
                    $q->orWhere(function ($sub) use ($user) {
                        $sub->where('subject_type', StudentProfile::class)
                            ->where('subject_id', $user->studentProfile->id);
                    });
                }
            })
            ->latest('created_at')
            ->limit(20)
            ->get();

        return view('admin.users.show_student', compact('user', 'activityLogs'));
    }
}
