<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffProfile extends Model
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
}
