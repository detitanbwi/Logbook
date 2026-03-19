<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyStaffSummary extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'tanggal',
        'total_logbooks',
        'submitted_logbooks',
        'accepted_logbooks',
        'rejected_logbooks',
        'total_work_minutes',
        'total_kpi',
        'target_angka_total',
        'capaian_angka_total',
        'progress_percent',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'total_logbooks' => 'integer',
            'submitted_logbooks' => 'integer',
            'accepted_logbooks' => 'integer',
            'rejected_logbooks' => 'integer',
            'total_work_minutes' => 'integer',
            'total_kpi' => 'integer',
            'target_angka_total' => 'decimal:2',
            'capaian_angka_total' => 'decimal:2',
            'progress_percent' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
