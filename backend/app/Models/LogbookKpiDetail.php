<?php

namespace App\Models;

use Database\Factories\LogbookKpiDetailFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LogbookKpiDetail extends Model
{
    /** @use HasFactory<LogbookKpiDetailFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'logbook_id',
        'kpi_id',
        'kpi_nama',
        'target_angka',
        'satuan',
        'capaian_angka',
        'lampiran_file',
        'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'target_angka' => 'decimal:2',
            'capaian_angka' => 'decimal:2',
            'finished_at' => 'datetime',
        ];
    }

    public function logbook(): BelongsTo
    {
        return $this->belongsTo(Logbook::class);
    }

    public function kpi(): BelongsTo
    {
        return $this->belongsTo(KpiMaster::class, 'kpi_id');
    }
}
