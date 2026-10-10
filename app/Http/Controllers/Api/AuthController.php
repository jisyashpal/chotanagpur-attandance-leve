<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\Subject;
use App\Models\FacultySubjectAllocation;
use App\Services\AuditLogger;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Handle user registration.
     */
    public function register(Request $request)
    {
        $request->merge([
            'device_id' => $request->header('X-DEVICE-ID'),
            'device_name' => $request->header('X-DEVICE-NAME'),
            'platform' => $request->header('X-PLATFORM'),
            'fcm_token' => $request->header('X-FCM-TOKEN'),
        ]);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', 'in:faculty,staff'],
            
            // Common Profile Fields
            'phone_no' => ['required', 'string', 'max:15'],
            'dob' => ['required', 'date'],
            'gender' => ['required', 'string', 'in:Male,Female,Other'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],

            // Role specific fields
            'enrollment_no' => ['required_if:role,student'],
            'roll_no' => ['required_if:role,student'],
            'course_id' => ['required_if:role,student', 'exists:courses,id'],
            'semester_id' => ['required_if:role,student', 'exists:semesters,id'],
            'section_id' => ['required_if:role,student', 'exists:sections,id'],
            'employee_id' => ['required_if:role,faculty,staff'],
            'designation' => ['required_if:role,faculty,staff'],
            'department' => ['required_if:role,faculty,staff'],

            // Faculty specific subject allocation
            'subject_ids' => ['nullable', 'array'],
            'subject_ids.*' => ['exists:subjects,id'],
            'session_id' => ['required_if:role,student', 'nullable', 'exists:academic_sessions,id'],
            'batch_id' => ['required_if:role,student', 'nullable', 'exists:batches,id'],

            // Device Info
            'device_id' => ['required', 'string'],
            'device_name' => ['nullable', 'string'],
            'platform' => ['nullable', 'string', 'in:android,ios,web'],
            'fcm_token' => ['required', 'string'],
        ]);

        return DB::transaction(function () use ($request) {
            // 1. Create User
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'is_profile_complete' => true,
            ]);

            // 2. Handle Photo Upload
            $photoPath = null;
            if ($request->hasFile('profile_photo')) {
                $photoPath = $this->handlePhotoUpload($request->file('profile_photo'), $user->id);
                $user->update(['profile_photo' => $photoPath]);
            }

            // 3. Create Profile based on Role
            if ($request->role === 'student') {
                $user->studentProfile()->create([
                    'batch_id' => $request->batch_id,
                    'session_id' => $request->session_id,
                    'course_id' => $request->course_id,
                    'semester_id' => $request->semester_id,
                    'section_id' => $request->section_id,
                    'enrollment_no' => $request->enrollment_no,
                    'roll_no' => $request->roll_no,
                    'phone_no' => $request->phone_no,
                    'dob' => $request->dob,
                    'gender' => $request->gender,
                    'is_complete' => true
                ]);
            } elseif ($request->role === 'faculty') {
                $user->facultyProfile()->create([
                    'employee_id' => $request->employee_id,
                    'designation' => $request->designation,
                    'department' => $request->department,
                    'phone_no' => $request->phone_no,
                    'dob' => $request->dob,
                    'gender' => $request->gender,
                    'is_complete' => true
                ]);

                // 4. Handle Subject Allocation for Faculty
                if ($request->has('subject_ids') && is_array($request->subject_ids)) {
                    foreach ($request->subject_ids as $subjectId) {
                        $subject = Subject::find($subjectId);
                        if ($subject) {
                            FacultySubjectAllocation::create([
                                'faculty_id' => $user->id,
                                'subject_id' => $subjectId,
                                'semester_id' => $subject->semester_id
                            ]);
                        }
                    }
                }
            } elseif ($request->role === 'staff') {
                $user->staffProfile()->create([
                    'employee_id' => $request->employee_id,
                    'designation' => $request->designation,
                    'department' => $request->department,
                    'phone_no' => $request->phone_no,
                    'dob' => $request->dob,
                    'gender' => $request->gender,
                    'is_complete' => true
                ]);
            }

            // Register Device
            $user->devices()->updateOrCreate(
                ['device_id' => $request->device_id],
                [
                    'device_name' => $request->device_name,
                    'platform' => $request->platform,
                    'fcm_token' => $request->fcm_token,
                    'last_active_at' => now(),
                ]
            );

            $token = $user->createToken($request->device_id)->plainTextToken;

            return response()->json([
                'message' => 'User and profile registered successfully.',
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user->toFlatProfileArray(),
            ]);
        });
    }

    /**
     * Handle user login.
     */
    public function login(Request $request)
    {
        $request->merge([
            'device_id' => $request->header('X-DEVICE-ID'),
            'device_name' => $request->header('X-DEVICE-NAME'),
            'platform' => $request->header('X-PLATFORM'),
            'fcm_token' => $request->header('X-FCM-TOKEN'),
        ]);

        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'device_id' => ['required', 'string'],
            'device_name' => ['nullable', 'string'],
            'platform' => ['nullable', 'string', 'in:android,ios,web'],
            'fcm_token' => ['required', 'string'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (! $user->is_active) {
            return response()->json([
                'status' => 'error',
                'message' => 'Your account has been blocked.',
            ], 403);
        }

        // Register/Update Device
        $user->devices()->updateOrCreate(
            ['device_id' => $request->device_id],
            [
                'device_name' => $request->device_name,
                'platform' => $request->platform,
                'fcm_token' => $request->fcm_token,
                'last_active_at' => now(),
            ]
        );

        $token = $user->createToken($request->device_id)->plainTextToken;

        AuditLogger::log('login', $user, null, null, 'API login', [
            'device_id' => $request->device_id,
        ]);

        return response()->json([
            'message' => 'Login successful.',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user->toFlatProfileArray(),
        ]);
    }

    /**
     * Handle student login using roll number, phone number, and DOB.
     */
    public function studentLogin(Request $request)
    {
        $request->merge([
            'device_id' => $request->header('X-DEVICE-ID'),
            'device_name' => $request->header('X-DEVICE-NAME'),
            'platform' => $request->header('X-PLATFORM'),
            'fcm_token' => $request->header('X-FCM-TOKEN'),
        ]);

        $request->validate([
            'roll_no' => ['required', 'string'],
            'phone_no' => ['required', 'string'],
            'dob' => ['required', 'date'],
            'device_id' => ['required', 'string'],
            'device_name' => ['nullable', 'string'],
            'platform' => ['nullable', 'string', 'in:android,ios,web'],
            'fcm_token' => ['required', 'string'],
        ]);

        // Find student profile matching the credentials
        $studentProfile = \App\Models\StudentProfile::where('roll_no', $request->roll_no)
            ->where('phone_no', $request->phone_no)
            ->whereDate('dob', $request->dob)
            ->first();

        if (!$studentProfile || !$studentProfile->user) {
            throw ValidationException::withMessages([
                'roll_no' => ['The provided student credentials do not match our records.'],
            ]);
        }

        $user = $studentProfile->user;

        if ($user->role !== 'student') {
            throw ValidationException::withMessages([
                'roll_no' => ['The provided student credentials do not match our records.'],
            ]);
        }

        if (! $user->is_active) {
            return response()->json([
                'status' => 'error',
                'message' => 'Your account has been blocked.',
            ], 403);
        }

        // Register/Update Device
        $user->devices()->updateOrCreate(
            ['device_id' => $request->device_id],
            [
                'device_name' => $request->device_name,
                'platform' => $request->platform,
                'fcm_token' => $request->fcm_token,
                'last_active_at' => now(),
            ]
        );

        $token = $user->createToken($request->device_id)->plainTextToken;

        AuditLogger::log('login', $user, null, null, 'API student login', [
            'device_id' => $request->device_id,
        ]);

        return response()->json([
            'message' => 'Login successful.',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user->toFlatProfileArray(),
        ]);
    }

    /**
     * Handle forgot password request.
     */
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json(['message' => __($status)]);
        }

        throw ValidationException::withMessages([
            'email' => [__($status)],
        ]);
    }

    /**
     * Handle password reset request.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['message' => __($status)]);
        }

        throw ValidationException::withMessages([
            'email' => [__($status)],
        ]);
    }

    /**
     * Handle user logout.
     */
    public function logout(Request $request)
    {
        AuditLogger::log('logout', $request->user(), null, null, 'API logout');

        if ($request->hasHeader('X-DEVICE-ID')) {
            $request->user()->devices()->where('device_id', $request->header('X-DEVICE-ID'))->delete();
        }

        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }
}
