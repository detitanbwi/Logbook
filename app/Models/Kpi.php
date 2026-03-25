<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Kpi extends Model
{
    /** @use HasFactory<\Database\Factories\KpiFactory> */
    use HasFactory;

    protected $fillable = [
        'description',
        'target',
        'unit',
    ];

    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'employee_kpis', 'kpi_id', 'user_id');
    }
}
