<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogbookReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'logbook_id',
        'reviewer_id',
        'rating',
        'comment',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function logbook()
    {
        return $this->belongsTo(Logbook::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
