<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\FacultySubjectAllocation;
use App\Models\Section;
use App\Models\Semester;
use App\Models\Subject;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Get the authenticated user's complete profile.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user()->toFlatProfileArray());
    }

    /**
     * Update the authenticated user's profile.
     */
    public function update(ProfileUpdateRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        // Keep calendar dates timezone-safe (store/return as Y-m-d only).
        if (isset($validated['dob'])) {
            $validated['dob'] = \Carbon\Carbon::parse($validated['dob'])->format('Y-m-d');
        }

        // Students cannot change phone/DOB once already set, or academic details once profile is complete.
        if ($user->role === 'student') {
            $profile = $user->studentProfile;

            if (filled($profile?->phone_no)) {
                unset($validated['phone_no']);
            }
            if (filled($profile?->dob)) {
                unset($validated['dob']);
            }

            if ($user->is_profile_complete) {
                unset(
                    $validated['batch_id'],
                    $validated['session_id'],
                    $validated['course_id'],
                    $validated['semester_id'],
                    $validated['section_id'],
                    $validated['university_registration_number']
                );
            }
        }

        if ($user->role === 'student' && isset($validated['batch_id'], $validated['course_id'])) {
            $batchBelongsToCourse = \App\Models\Batch::where('id', $validated['batch_id'])
                ->where('course_id', $validated['course_id'])
                ->exists();

            if (! $batchBelongsToCourse) {
                return response()->json([
                    'message' => 'Selected batch does not belong to the selected course.',
                    'errors' => ['batch_id' => ['Selected batch does not belong to the selected course.']],
                ], 422);
            }
        }

        if ($user->role === 'student' && isset($validated['session_id'], $validated['batch_id'])) {
            $sessionBelongsToBatch = \App\Models\AcademicSession::where('id', $validated['session_id'])
                ->where('batch_id', $validated['batch_id'])
                ->exists();

            if (! $sessionBelongsToBatch) {
                return response()->json([
                    'message' => 'Selected session does not belong to the selected batch.',
                    'errors' => ['session_id' => ['Selected session does not belong to the selected batch.']],
                ], 422);
            }
        }

        if ($user->role === 'student' && isset($validated['semester_id'], $validated['course_id'])) {
            $semesterBelongsToCourse = Semester::where('id', $validated['semester_id'])
                ->where('course_id', $validated['course_id'])
                ->exists();

            if (!$semesterBelongsToCourse) {
                return response()->json([
                    'message' => 'Selected semester does not belong to the selected course.',
                    'errors' => ['semester_id' => ['Selected semester does not belong to the selected course.']]
                ], 422);
            }
        }

        if ($user->role === 'student' && isset($validated['section_id'], $validated['semester_id'])) {
            $sectionBelongsToSemester = Section::where('id', $validated['section_id'])
                ->where('semester_id', $validated['semester_id'])
                ->exists();

            if (!$sectionBelongsToSemester) {
                return response()->json([
                    'message' => 'Selected section does not belong to the selected semester.',
                    'errors' => ['section_id' => ['Selected section does not belong to the selected semester.']]
                ], 422);
            }
        }

        return DB::transaction(function () use ($request, $user, $validated) {
            // 1. Update User Table
            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
            ]);

            // 2. Get Profile instances
            $profile = $this->getUserProfile($user);

            if (!$profile) {
                return response()->json([
                    'message' => 'Profile not found.',
                ], 404);
            }

            // 3. Handle Photo Upload if present
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

            // 4. Update Profile Table
            $profile->update($validated);

            // 5. Handle Faculty Subjects (Sync)
            if ($user->role === 'faculty' && isset($validated['subject_ids'])) {
                // Remove existing allocations
                FacultySubjectAllocation::where('faculty_id', $user->id)->delete();

                // Add new allocations
                foreach ($validated['subject_ids'] as $subjectId) {
                    $subject = Subject::find($subjectId);
                    FacultySubjectAllocation::create([
                        'faculty_id' => $user->id,
                        'subject_id' => $subjectId,
                        'semester_id' => $subject->semester_id
                    ]);
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Profile updated successfully.',
                'user' => $user->toFlatProfileArray()
            ]);
        });
    }

    /**
     * Change the authenticated user's password.
     */
    public function changePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Password updated successfully.',
        ]);
    }
    /**
     * Get all active sessions for the user.
     */
    public function getSessions(Request $request): JsonResponse
    {
        $user = $request->user();
        $tokens = $user->tokens()->get()->map(function ($token) use ($user) {
            $device = $user->devices()->where('device_id', $token->name)->first();
            return [
                'id' => $token->id,
                'device_id' => $token->name,
                'device_name' => $device->device_name ?? 'Unknown Device',
                'platform' => $device->platform ?? 'Unknown',
                'last_used_at' => $token->last_used_at,
                'is_current' => $token->id === $user->currentAccessToken()->id,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $tokens
        ]);
    }

    /**
     * Logout a specific session.
     */
    public function logoutSession(Request $request, $tokenId): JsonResponse
    {
        $user = $request->user();
        $token = $user->tokens()->where('id', $tokenId)->first();

        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Session not found.'
            ], 404);
        }

        $token->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Session revoked successfully.'
        ]);
    }
}
