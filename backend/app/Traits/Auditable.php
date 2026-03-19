<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    public static function bootAuditable()
    {
        static::created(function ($model) {
            self::logAudit('created', $model);
        });

        static::updated(function ($model) {
            self::logAudit('updated', $model);
        });

        static::deleted(function ($model) {
            self::logAudit('deleted', $model);
        });
    }

    protected static function logAudit($action, $model)
    {
        $oldData = $action === 'updated' ? $model->getOriginal() : null;
        if ($action === 'deleted') {
            $oldData = $model->toArray();
        }
        $newData = $action !== 'deleted' ? $model->getChanges() : null;
        if ($action === 'created') {
            $newData = $model->toArray();
        }

        AuditLog::create([
            'table_name' => $model->getTable(),
            'record_id' => $model->id,
            'action' => $action,
            'old_data' => $oldData ? json_encode($oldData) : null,
            'new_data' => $newData ? json_encode($newData) : null,
            'performed_by' => Auth::id(),
            'performed_at' => now(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
