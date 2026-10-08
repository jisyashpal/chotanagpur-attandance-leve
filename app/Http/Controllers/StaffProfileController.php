<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use App\Models\StaffProfile;

class StaffProfileController extends Controller
{
    /**
     * Show the staff profile completion form.
     */
    public function completeShow(Request $request): View
    {
        $user = $request->user();
        $profile = $user->staffProfile;

        return view('profile.complete.staff', compact('profile'));
    }

    /**
     * Store or update the staff profile information.
     */
    public function completeStore(Request $request): RedirectResponse
    {
        $user = $request->user();
        $profile = $user->staffProfile;

        $rules = [
            'phone_no' => 'required|string|max:15',
            'dob' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'profile_photo' => 'nullable|image|max:2048',
            'employee_id' => 'required|unique:staff_profiles,employee_id,' . ($profile->id ?? 'NULL'),
            'designation' => 'required|string|max:255',
            'department' => 'required|string|max:255',
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
                $profile = StaffProfile::create($validated);
            }

            $user->update(['is_profile_complete' => true]);

            return redirect()->route('dashboard')->with('success', 'Staff profile completed/updated successfully!');
        });
    }
}
