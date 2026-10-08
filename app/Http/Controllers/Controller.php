<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;

abstract class Controller
{
    /**
     * Handle photo upload, delete old photo if exists.
     */
    protected function handlePhotoUpload($file, $userId, $dir = 'upload/profile', $oldPhotoPath = null): string
    {
        if ($oldPhotoPath) {
            $this->deleteOldPhoto($oldPhotoPath);
        }

        $filename = time() . '_' . $userId . '.' . $file->getClientOriginalExtension();
        $file->move(public_path($dir), $filename);
        
        return $dir . '/' . $filename;
    }

    /**
     * Delete old photo from disk.
     */
    protected function deleteOldPhoto($photoPath): void
    {
        if (!$photoPath) return;

        $fullPath = public_path($photoPath);
        if (File::exists($fullPath)) {
            File::delete($fullPath);
        }
    }

    /**
     * Get the user's profile model based on role.
     */
    protected function getUserProfile($user)
    {
        return match ($user->role) {
            'student' => $user->studentProfile,
            'faculty' => $user->facultyProfile,
            'staff' => $user->staffProfile,
            default => null,
        };
    }
}
