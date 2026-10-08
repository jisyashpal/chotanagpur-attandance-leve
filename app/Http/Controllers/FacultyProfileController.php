<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use App\Models\AcademicSession;
use App\Models\Course;
use App\Models\FacultyProfile;
use App\Models\FacultySubjectAllocation;

class FacultyProfileController extends Controller
{
    /**
     * Show the faculty profile completion form.
     */
    public function completeShow(Request $request): View
    {
        $user = $request->user();
        $courses = Course::with('semesters.subjects')->get();
        $profile = $user->facultyProfile;
        
        // Fetch current subject allocations for pre-filling
        $allocatedSubjects = [];
        if ($profile) {
            $allocations = FacultySubjectAllocation::where('faculty_id', $user->id)
                ->with('subject.semester.course')
                ->get();
                
            foreach ($allocations as $alloc) {
                if ($alloc->subject) {
                    $allocatedSubjects[] = [
                        'id' => $alloc->subject->id,
                        'name' => $alloc->subject->name,
                        'code' => $alloc->subject->code,
                        'course' => $alloc->subject->semester->course->name ?? 'N/A',
                        'semester' => $alloc->subject->semester->name ?? 'N/A'
                    ];
                }
            }
        }

        return view('profile.complete.faculty', compact('courses', 'profile', 'allocatedSubjects'));
    }

    /**
     * Store or update the faculty profile information.
     */
    public function completeStore(Request $request): RedirectResponse
    {
        $user = $request->user();
        $profile = $user->facultyProfile;

        $rules = [
            'phone_no' => 'required|string|max:15',
            'dob' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'profile_photo' => 'nullable|image|max:2048',
            'employee_id' => 'required|unique:faculty_profiles,employee_id,' . ($profile->id ?? 'NULL'),
            'designation' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'subject_ids' => 'required|array',
            'subject_ids.*' => 'exists:subjects,id',
        ];

        $validated = $request->validate($rules);

        return DB::transaction(function () use ($request, $user, $validated, $profile) {
            // Handle Photo Upload
            if ($request->hasFile('profile_photo')) {
                $photoPath = $this->handlePhotoUpload($request->file('profile_photo'), $user->id);
                $user->update(['profile_photo' => $photoPath]);
                unset($validated['profile_photo']);
            }

            $validated['user_id'] = $user->id;
            $validated['is_complete'] = true;

            if ($profile) {
                $profile->update($validated);
            } else {
                $profile = FacultyProfile::create($validated);
            }

            // Sync Subject Allocations
            if (isset($validated['subject_ids'])) {
                // Delete existing allocations to prevent duplicates
                FacultySubjectAllocation::where('faculty_id', $user->id)->delete();

                foreach ($validated['subject_ids'] as $subjectId) {
                    $subject = \App\Models\Subject::find($subjectId);
                    if ($subject) {
                        FacultySubjectAllocation::create([
                            'faculty_id' => $user->id,
                            'subject_id' => $subjectId,
                            'semester_id' => $subject->semester_id,
                        ]);
                    }
                }
            }

            $user->update(['is_profile_complete' => true]);

            return redirect()->route('dashboard')->with('success', 'Faculty profile completed/updated successfully!');
        });
    }
}
