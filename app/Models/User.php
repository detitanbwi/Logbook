<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable([
    'nama', 'npp', 'password', 'foto', 'tempat_lahir', 'tanggal_lahir', 
    'nik', 'npwp', 'alamat', 'status_perkawinan', 
    'riwayat_pendidikan', 'riwayat_karir', 'supervisor_id', 'role'
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
            'password' => 'hashed',
        ];
    }

    /**
     * Relasi ke atasan langsung
     */
    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function subordinates()
    {
        return $this->hasMany(User::class, 'supervisor_id');
    }

    public function kpis(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Kpi::class, 'employee_kpis', 'user_id', 'kpi_id');
    }

    public function logbooks()
    {
        return $this->hasMany(Logbook::class, 'employee_id');
    }

    public function subordinateLogbooks()
    {
        return $this->hasMany(Logbook::class, 'supervisor_id');
    }

    public function reviews()
    {
        return $this->hasMany(LogbookReview::class, 'reviewer_id');
    }
}
