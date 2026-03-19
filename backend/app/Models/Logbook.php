<?php

namespace App\Models;

use Database\Factories\LogbookFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Logbook extends Model
{
    /** @use HasFactory<LogbookFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'tanggal',
        'start_kerja',
        'end_kerja',
        'lokasi',
        'status',
        'rating',
        'reviewed_by',
        'reviewed_at',
        'reviewer_comment',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'start_kerja' => 'string',
            'end_kerja' => 'string',
            'reviewed_at' => 'datetime',
            'rating' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function kpiDetails(): HasMany
    {
        return $this->hasMany(LogbookKpiDetail::class);
    }
}
