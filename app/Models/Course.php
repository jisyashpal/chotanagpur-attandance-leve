<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    use LogsActivity;

    protected $fillable = ['name', 'code'];

    public function semesters(): HasMany
    {
        return $this->hasMany(Semester::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }
}
