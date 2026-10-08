<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentProfile extends Model
{
    use LogsActivity;

    protected $fillable = [
        'user_id',
        'batch_id',
        'session_id',
        'course_id',
        'semester_id',
        'section_id',
        'roll_no',
        'university_registration_number',
        'father_name',
        'mother_name',
        'category',
        'caste',
        'religion',
        'address',
        'local_address',
        'aadhar_no',
        'nationality',
        'phone_no',
        'whatsapp_no',
        'blood_group',
        'degree_obtained_college',
        'dob',
        'gender',
        'is_complete'
    ];

    protected $casts = [
        'dob' => 'date:Y-m-d',
        'is_complete' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class, 'session_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    // Backward-compatible alias for legacy code paths still using enrollment_no.
    public function getEnrollmentNoAttribute(): ?string
    {
        return $this->attributes['university_registration_number'] ?? null;
    }

    public function setEnrollmentNoAttribute(?string $value): void
    {
        $this->attributes['university_registration_number'] = $value;
    }
}
