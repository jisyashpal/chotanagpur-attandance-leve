<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subject extends Model
{
    use LogsActivity;

    protected $fillable = ['semester_id', 'name', 'code'];

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }
}
