<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use App\Models\AcademicSession;
use App\Models\Batch;
use App\Models\Course;
use App\Models\Section;
use App\Models\Semester;
use App\Models\StudentProfile;

class StudentProfileController extends Controller
{
    /**
     * Show the student profile completion form.
     */
    public function completeShow(Request $request): View
    {
        $user = $request->user();
        $sessions = AcademicSession::with('batch')->where('is_active', true)->get();
        $courses = Course::with(['batches', 'semesters.sections'])->get();
        $batches = Batch::with('course')->where('is_active', true)->orderBy('name')->get();
        $profile = $user->studentProfile;
        $locks = $this->fieldLocks($user, $profile);

        return view('profile.complete.student', compact('sessions', 'courses', 'batches', 'profile', 'locks'));
    }

    /**
     * Store or update the student profile information.
     */
    public function completeStore(Request $request): RedirectResponse
    {
        $user = $request->user();
        $profile = $user->studentProfile;
        $locks = $this->fieldLocks($user, $profile);

        $rules = [
            'gender' => 'required|in:male,female,other',
            'father_name' => 'nullable|string|max:255',
            'mother_name' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:100',
            'caste' => 'nullable|string|max:100',
            'religion' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:1000',
            'local_address' => 'nullable|string|max:1000',
            'aadhar_no' => 'nullable|string|max:20',
            'nationality' => 'nullable|string|max:100',
            'whatsapp_no' => 'nullable|string|max:20',
            'blood_group' => 'nullable|string|max:20',
            'degree_obtained_college' => 'nullable|string|max:255',
            'profile_photo' => 'nullable|image|max:2048',
        ];

        if (! $locks['phone']) {
            $rules['phone_no'] = 'required|string|max:15';
        }

        if (! $locks['dob']) {
            $rules['dob'] = 'required|date';
        }

        if (! $locks['academic']) {
            $rules = array_merge($rules, [
                'batch_id' => 'nullable|exists:batches,id',
                'session_id' => 'required|exists:academic_sessions,id',
                'course_id' => 'required|exists:courses,id',
                'semester_id' => 'required|exists:semesters,id',
                'section_id' => 'required|exists:sections,id',
                'university_registration_number' => 'required|unique:student_profiles,university_registration_number,' . ($profile->id ?? 'NULL'),
            ]);
        }

        $validated = $request->validate($rules);

        if (! $locks['academic']) {
            if (empty($validated['batch_id'])) {
                $validated['batch_id'] = AcademicSession::where('id', $validated['session_id'])->value('batch_id');
            }

            if (! empty($validated['batch_id'])) {
                $batchBelongsToCourse = Batch::where('id', $validated['batch_id'])
                    ->where('course_id', $validated['course_id'])
                    ->exists();
                if (! $batchBelongsToCourse) {
                    return back()->withErrors([
                        'batch_id' => 'Selected batch does not belong to the selected course.',
                    ])->withInput();
                }

                $sessionBelongsToBatch = AcademicSession::where('id', $validated['session_id'])
                    ->where('batch_id', $validated['batch_id'])
                    ->exists();
                if (! $sessionBelongsToBatch) {
                    return back()->withErrors([
                        'session_id' => 'Selected session does not belong to the selected batch.',
                    ])->withInput();
                }
            }

            $semesterBelongsToCourse = Semester::where('id', $validated['semester_id'])
                ->where('course_id', $validated['course_id'])
                ->exists();

            if (! $semesterBelongsToCourse) {
                return back()->withErrors([
                    'semester_id' => 'Selected semester does not belong to the selected course.',
                ])->withInput();
            }

            $sectionBelongsToSemester = Section::where('id', $validated['section_id'])
                ->where('semester_id', $validated['semester_id'])
                ->exists();

            if (! $sectionBelongsToSemester) {
                return back()->withErrors([
                    'section_id' => 'Selected section does not belong to the selected semester.',
                ])->withInput();
            }
        }

        return DB::transaction(function () use ($request, $user, $validated, $profile, $locks) {
            if ($request->hasFile('profile_photo')) {
                $photoPath = $this->handlePhotoUpload($request->file('profile_photo'), $user->id);
                $user->update(['profile_photo' => $photoPath]);
                unset($validated['profile_photo']);
            }

            // Never accept locked fields from the request payload.
            if ($locks['phone']) {
                unset($validated['phone_no']);
            }
            if ($locks['dob']) {
                unset($validated['dob']);
            }
            if ($locks['academic']) {
                unset(
                    $validated['batch_id'],
                    $validated['session_id'],
                    $validated['course_id'],
                    $validated['semester_id'],
                    $validated['section_id'],
                    $validated['university_registration_number']
                );
            }

            $validated['user_id'] = $user->id;
            $validated['is_complete'] = true;

            if ($profile) {
                $profile->update($validated);
            } else {
                StudentProfile::create($validated);
            }

            $user->update(['is_profile_complete' => true]);

            return redirect()->route('dashboard')->with('success', 'Profile completed/updated successfully!');
        });
    }

    /**
     * Fields locked for student self-edit (already set by admin/import or previous save).
     *
     * @return array{phone: bool, dob: bool, academic: bool}
     */
    private function fieldLocks($user, ?StudentProfile $profile): array
    {
        $hasAcademic = $profile
            && filled($profile->batch_id)
            && filled($profile->session_id)
            && filled($profile->course_id)
            && filled($profile->semester_id)
            && filled($profile->section_id)
            && filled($profile->university_registration_number);

        return [
            'phone' => filled($profile?->phone_no),
            'dob' => filled($profile?->dob),
            'academic' => (bool) $user->is_profile_complete || $hasAcademic,
        ];
    }
}
