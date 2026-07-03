<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;

trait Auditable
{
    public static function bootAuditable()
    {
        static::created(function ($model) {
            self::logAction($model, 'created');
        });

        static::updated(function ($model) {
            self::logAction($model, 'updated');
        });

        static::deleted(function ($model) {
            self::logAction($model, 'deleted');
        });
    }

    protected static function logAction($model, $action)
    {
        $oldValues = [];
        $newValues = [];

        // Exclude fields that do not need to be audited
        $ignoredFields = ['created_at', 'updated_at', 'deleted_at', 'password', 'remember_token'];

        if ($action === 'created') {
            $newValues = Arr::except($model->getAttributes(), $ignoredFields);
        } elseif ($action === 'updated') {
            $oldValues = Arr::except($model->getOriginal(), $ignoredFields);
            $newValues = Arr::except($model->getDirty(), $ignoredFields);
            
            // Further filter old values to only include changed keys
            $oldValues = array_intersect_key($oldValues, $newValues);
            
            // If nothing actually changed, don't log
            if (empty($newValues)) {
                return;
            }
        } elseif ($action === 'deleted') {
            $oldValues = Arr::except($model->getOriginal(), $ignoredFields);
        }

        AuditLog::create([
            'user_id' => Auth::id(),
            'class_id' => $model->class_id ?? null,
            'action' => $action,
            'table_name' => $model->getTable(),
            'row_id' => $model->getKey(),
            'old_values' => empty($oldValues) ? null : $oldValues,
            'new_values' => empty($newValues) ? null : $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => substr(request()->userAgent() ?? '', 0, 255),
        ]);
    }
}
