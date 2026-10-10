<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogger
{
    protected static bool $enabled = true;

    public static function withoutLogging(callable $callback): mixed
    {
        $previous = static::$enabled;
        static::$enabled = false;

        try {
            return $callback();
        } finally {
            static::$enabled = $previous;
        }
    }

    public static function disable(): void
    {
        static::$enabled = false;
    }

    public static function enable(): void
    {
        static::$enabled = true;
    }

    public static function isEnabled(): bool
    {
        return static::$enabled;
    }

    public static function log(
        string $action,
        ?Model $subject = null,
        ?array $old = null,
        ?array $new = null,
        ?string $description = null,
        array $meta = []
    ): ?ActivityLog {
        if (! static::$enabled) {
            return null;
        }

        $actor = Auth::user();

        // Login happens before the token is attached, so Auth::user() is still empty.
        if (! $actor && $action === 'login' && $subject instanceof User) {
            $actor = $subject;
        }

        $properties = array_filter([
            'old' => $old,
            'new' => $new,
            'meta' => $meta ?: null,
        ], fn ($value) => $value !== null && $value !== []);

        return ActivityLog::create([
            'actor_id' => $actor?->id,
            'actor_role' => $actor?->role,
            'action' => $action,
            'subject_type' => $subject ? $subject->getMorphClass() : null,
            'subject_id' => $subject?->getKey(),
            'description' => $description,
            'properties' => $properties ?: null,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'created_at' => now(),
        ]);
    }
}
