<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyKpiSummary extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'kpi_id',
        'tanggal',
        'kpi_nama',
        'satuan',
        'target_angka_total',
        'capaian_angka_total',
        'progress_percent',
        'total_lampiran',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'target_angka_total' => 'decimal:2',
            'capaian_angka_total' => 'decimal:2',
            'progress_percent' => 'decimal:2',
            'total_lampiran' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function kpi(): BelongsTo
    {
        return $this->belongsTo(KpiMaster::class, 'kpi_id');
    }
}
