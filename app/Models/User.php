<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Auth\Notifications\ResetPassword;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_profile_complete',
        'is_active',
        'profile_photo',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_profile_complete' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function studentProfile(): HasOne
    {
        return $this->hasOne(StudentProfile::class);
    }

    public function studentProfiles(): HasMany
    {
        return $this->hasMany(StudentProfile::class);
    }

    public function facultyProfile(): HasOne
    {
        return $this->hasOne(FacultyProfile::class);
    }

    public function staffProfile(): HasOne
    {
        return $this->hasOne(StaffProfile::class);
    }

    public function getProfilePhotoUrl(): ?string
    {
        $photoPath = $this->profile_photo ?: 'images/user.png';
        if (file_exists(public_path($photoPath))) {
            return asset('public/' . $photoPath);
        }
        return $photoPath;
    }

    public function subjectAllocations(): HasMany
    {
        return $this->hasMany(FacultySubjectAllocation::class, 'faculty_id');
    }

    public function devices(): HasMany
    {
        return $this->hasMany(UserDevice::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function approvedLeaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'approved_by');
    }

    public function isProfileComplete(): bool
    {
        return (bool) $this->is_profile_complete;
    }

    public function loadCompleteProfile(): self
    {
        return $this->load([
            'studentProfile.batch',
            'studentProfile.session',
            'studentProfile.course',
            'studentProfile.semester',
            'studentProfile.section',
            'facultyProfile.subjectAllocations.subject',
            'facultyProfile.subjectAllocations.semester',
            'staffProfile'
        ]);
    }

    public function toFlatProfileArray(): array
    {
        $this->loadCompleteProfile();
        $userData = $this->toArray();

        $profile = match ($this->role) {
            'student' => $this->studentProfile,
            'faculty' => $this->facultyProfile,
            'staff' => $this->staffProfile,
            default => null,
        };

        if ($profile) {
            $profileData = $profile->toArray();
            // Remove redundant fields from profile data
            unset($profileData['id'], $profileData['user_id'], $profileData['created_at'], $profileData['updated_at']);

            // Keep DOB as date-only (Y-m-d) so timezone does not shift the day in API responses.
            if (array_key_exists('dob', $profileData)) {
                $profileData['dob'] = $profile->dob
                    ? $profile->dob->format('Y-m-d')
                    : null;
            }

            // Merge profile data into user data
            $userData = array_merge($userData, $profileData);
        }

        // Add correct profile photo URL
        $userData['profile_photo_url'] = $this->getProfilePhotoUrl();

        // Remove the nested profile relations from result
        unset($userData['student_profile'], $userData['faculty_profile'], $userData['staff_profile']);

        return $userData;
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    // public function sendPasswordResetNotification($token)
    // {
    //     $url = route('password.reset', [
    //         'token' => $token,
    //         'email' => $this->getEmailForPasswordReset(),
    //     ]);

    //     $this->notify(new ResetPassword($url));
    // }
}
