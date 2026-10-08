<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\AcademicSession;
use App\Models\Course;
use App\Models\StudentProfile;
use App\Models\FacultyProfile;
use App\Models\StaffProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Models\FacultySubjectAllocation;


class ProfileController extends Controller
{
    public function index(Request $request): RedirectResponse
    {
        return Redirect::route($request->user()->role . '.profile.edit');
    }

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        return DB::transaction(function () use ($request, $user) {
            $validated = $request->validated();
            $oldPhotoPath = $user->profile_photo;

            if ($request->hasFile('profile_photo')) {
                $photoPath = $this->handlePhotoUpload($request->file('profile_photo'), $user->id, 'upload/profile', $oldPhotoPath);
                $validated['profile_photo'] = $photoPath;
            } else {
                unset($validated['profile_photo']);
            }

            $user->fill($validated);

            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
            }

            $user->save();

            return Redirect::route($user->role . '.profile.edit')->with('status', 'profile-updated');
        });
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        return DB::transaction(function () use ($request, $user) {
            // Cleanup profile photo before deleting
            if ($user->profile_photo) {
                $this->deleteOldPhoto($user->profile_photo);
            }

            Auth::logout();
            $user->delete();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return Redirect::to('/');
        });
    }



}
