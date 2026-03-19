<?php

namespace App\Models;

use Database\Factories\NotificationFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Notification extends Model
{
    /** @use HasFactory<NotificationFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'reference_id',
        'is_read',
    ];

    protected $appends = [
        'preview_message',
        'target_path',
        'target_params',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getPreviewMessageAttribute(): string
    {
        return Str::limit((string) $this->message, 120);
    }

    public function getTargetPathAttribute(): ?string
    {
        return match ($this->type) {
            'KPI_ASSIGNMENT' => '/staff/logbook',
            'LOGBOOK_SUBMITTED' => '/manager/reviews',
            'LOGBOOK_REJECTED', 'LOGBOOK_ACCEPTED' => '/staff/history',
            default => null,
        };
    }

    public function getTargetParamsAttribute(): ?array
    {
        return match ($this->type) {
            'KPI_ASSIGNMENT' => ['assignment_id' => $this->reference_id],
            'LOGBOOK_SUBMITTED', 'LOGBOOK_REJECTED', 'LOGBOOK_ACCEPTED' => ['logbook_id' => $this->reference_id],
            default => null,
        };
    }
}
