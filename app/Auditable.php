<?php

namespace App;

use App\Models\AuditLog;

trait Auditable
{
    public static function bootAuditable()
    {
        static::created(function ($model) {
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'created',
                'auditable_type' => $model::class,
                'auditable_id' => $model->id,
                'new_values' => $model->getAttributes(),
            ]);
        });

    }

    public function createCustomAuditLog(string $action, array $oldValues, array $newValues, $user_id): void
    {
        AuditLog::create([
            'user_id' => $user_id,
            'action' => $action,
            'auditable_type' => static::class,
            'auditable_id' => $this->id,
            'old_values' => $oldValues,
            'new_values' => $newValues,
        ]);
    }
}
