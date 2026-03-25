<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogbookItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'logbook_id',
        'kpi_id',
        'work_description',
        'score',
    ];

    public function logbook()
    {
        return $this->belongsTo(Logbook::class);
    }

    public function kpi()
    {
        return $this->belongsTo(Kpi::class);
    }
}
