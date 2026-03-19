<?php

namespace App\Models;

use Carbon\Carbon;
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

    /**
     * Boot the model.
     *
     * Cascade soft-deletes to KPI details when logbook is deleted.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::deleting(function (Logbook $logbook) {
            $logbook->kpiDetails()->delete();
        });
    }

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

    public function grossWorkMinutes(): int
    {
        if (! $this->end_kerja) {
            return 0;
        }

        $start = Carbon::createFromFormat('H:i:s', $this->normalizeTime((string) $this->start_kerja));
        $end = Carbon::createFromFormat('H:i:s', $this->normalizeTime((string) $this->end_kerja));

        if ($end->lessThanOrEqualTo($start)) {
            return 0;
        }

        return $start->diffInMinutes($end);
    }

    public function breakOverlapMinutes(): int
    {
        if (! $this->end_kerja) {
            return 0;
        }

        $start = Carbon::createFromFormat('H:i:s', $this->normalizeTime((string) $this->start_kerja));
        $end = Carbon::createFromFormat('H:i:s', $this->normalizeTime((string) $this->end_kerja));
        if ($end->lessThanOrEqualTo($start)) {
            return 0;
        }

        $breakStart = Carbon::createFromFormat('H:i:s', '12:00:00');
        $breakEnd = Carbon::createFromFormat('H:i:s', '13:00:00');

        $overlapStart = $start->greaterThan($breakStart) ? $start : $breakStart;
        $overlapEnd = $end->lessThan($breakEnd) ? $end : $breakEnd;

        return $overlapEnd->greaterThan($overlapStart)
            ? $overlapStart->diffInMinutes($overlapEnd)
            : 0;
    }

    public function netWorkMinutes(): int
    {
        return max($this->grossWorkMinutes() - $this->breakOverlapMinutes(), 0);
    }

    private function normalizeTime(string $value): string
    {
        if (strlen($value) === 5) {
            return $value.':00';
        }

        return $value;
    }
}
