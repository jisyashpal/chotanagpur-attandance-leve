<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->user();
        
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id),
            ],
            'profile_photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ];

        // Roles that have a separate profile table
        if (in_array($user->role, ['student', 'faculty', 'staff'])) {
            $rules = array_merge($rules, [
                'phone_no' => ['sometimes', 'required', 'string', 'max:15'],
                'dob' => ['sometimes', 'required', 'date'],
                'gender' => ['sometimes', 'required', 'string', 'in:Male,Female,Other'],
            ]);
        }

        if ($user->role === 'student') {
            $rules = array_merge($rules, [
                'university_registration_number' => ['sometimes', 'required', 'string', Rule::unique('student_profiles', 'university_registration_number')->ignore($user->studentProfile->id ?? null)],
                'batch_id' => ['sometimes', 'required', 'exists:batches,id'],
                'session_id' => ['sometimes', 'required', 'exists:academic_sessions,id'],
                'course_id' => ['sometimes', 'required', 'exists:courses,id'],
                'semester_id' => ['sometimes', 'required', 'exists:semesters,id'],
                'section_id' => ['sometimes', 'required', 'exists:sections,id'],
                'roll_no' => ['nullable', 'string'],
                'father_name' => ['nullable', 'string', 'max:255'],
                'mother_name' => ['nullable', 'string', 'max:255'],
                'category' => ['nullable', 'string', 'max:100'],
                'caste' => ['nullable', 'string', 'max:100'],
                'religion' => ['nullable', 'string', 'max:100'],
                'address' => ['nullable', 'string', 'max:1000'],
                'local_address' => ['nullable', 'string', 'max:1000'],
                'aadhar_no' => ['nullable', 'string', 'max:20'],
                'nationality' => ['nullable', 'string', 'max:100'],
                'whatsapp_no' => ['nullable', 'string', 'max:20'],
                'blood_group' => ['nullable', 'string', 'max:20'],
                'degree_obtained_college' => ['nullable', 'string', 'max:255'],
            ]);
        } elseif (in_array($user->role, ['faculty', 'staff'])) {
            $profileTable = $user->role . '_profiles';
            $profile = $user->role === 'faculty' ? $user->facultyProfile : $user->staffProfile;
            
            $roleRules = [
                'employee_id' => ['sometimes', 'required', 'string', Rule::unique($profileTable)->ignore($profile->id ?? null)],
                'designation' => ['sometimes', 'required', 'string', 'max:255'],
                'department' => ['sometimes', 'required', 'string', 'max:255'],
            ];

            if ($user->role === 'faculty') {
                $roleRules = array_merge($roleRules, [
                    'subject_ids' => ['nullable', 'array'],
                    'subject_ids.*' => ['exists:subjects,id'],
                ]);
            }

            $rules = array_merge($rules, $roleRules);
        }

        return $rules;
    }
}
