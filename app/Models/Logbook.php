<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Logbook extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'supervisor_id',
        'latitude',
        'longitude',
        'start_time',
        'end_time',
        'main_photo_path',
        'daily_report',
        'status',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function items()
    {
        return $this->hasMany(LogbookItem::class);
    }

    public function reviews()
    {
        return $this->hasMany(LogbookReview::class);
    }

    public function latestReview()
    {
        return $this->hasOne(LogbookReview::class)->latestOfMany();
    }

    public function attachments()
    {
        return $this->hasMany(LogbookAttachment::class);
    }

    public function getTotalHoursAttribute()
    {
        if (!$this->start_time || !$this->end_time) return 0;
        return $this->start_time->diffInMinutes($this->end_time) / 60;
    }
}
