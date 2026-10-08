<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicSession extends Model
{
    use LogsActivity;

    protected $fillable = ['batch_id', 'name', 'session_year', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function studentProfiles(): HasMany
    {
        return $this->hasMany(StudentProfile::class, 'session_id');
    }
}
