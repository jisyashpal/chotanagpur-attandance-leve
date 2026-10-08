<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notice extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'file_path',
        'link',
        'target_role',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the full URL for the file.
     */
    public function getFileUrlAttribute()
    {
        return $this->file_path ? asset('public/' . $this->file_path) : null;
    }
}
