<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FacultyProfile extends Model
{
    protected $fillable = [
        'user_id',
        'employee_id',
        'designation',
        'department',
        'phone_no',
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

    public function subjectAllocations(): HasMany
    {
        return $this->hasMany(FacultySubjectAllocation::class, 'faculty_id', 'user_id');
    }
}
