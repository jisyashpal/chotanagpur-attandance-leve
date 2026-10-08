<?php

namespace App\Traits;

use App\Services\AuditLogger;

trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        static::created(function ($model) {
            if (! AuditLogger::isEnabled()) {
                return;
            }

            AuditLogger::log(
                'created',
                $model,
                null,
                static::sanitizeAttributes($model->getAttributes()),
                class_basename($model).' created'
            );
        });

        static::updated(function ($model) {
            if (! AuditLogger::isEnabled()) {
                return;
            }

            $changes = $model->getChanges();
            unset($changes['updated_at'], $changes['remember_token']);

            if ($changes === []) {
                return;
            }

            $old = [];
            $new = [];
            foreach ($changes as $key => $value) {
                if (in_array($key, static::auditHidden(), true)) {
                    $old[$key] = '[hidden]';
                    $new[$key] = '[changed]';
                    continue;
                }
                $old[$key] = $model->getOriginal($key);
                $new[$key] = $value;
            }

            AuditLogger::log(
                'updated',
                $model,
                $old,
                $new,
                class_basename($model).' updated'
            );
        });

        static::deleted(function ($model) {
            if (! AuditLogger::isEnabled()) {
                return;
            }

            AuditLogger::log(
                'deleted',
                $model,
                static::sanitizeAttributes($model->getAttributes()),
                null,
                class_basename($model).' deleted'
            );
        });
    }

    protected static function auditHidden(): array
    {
        return ['password', 'remember_token'];
    }

    protected static function sanitizeAttributes(array $attributes): array
    {
        foreach (static::auditHidden() as $hidden) {
            if (array_key_exists($hidden, $attributes)) {
                $attributes[$hidden] = '[hidden]';
            }
        }
        unset($attributes['remember_token']);

        return $attributes;
    }
}
