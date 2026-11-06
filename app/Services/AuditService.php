<?php
namespace App\Services;

use App\Models\AuditLog;

class AuditService
{
    public static function log(string $action, string $type, $model = null, array $details = []): void
    {
        if (!auth()->check()) return;
        AuditLog::create([
            'actor_id' => auth()->id(),
            'actor_role' => auth()->user()->role,
            'action' => $action,
            'action_type' => $type,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'details' => array_merge($details, $model ? ['model' => get_class($model), 'id' => $model->id ?? null] : []),
        ]);
    }
}
