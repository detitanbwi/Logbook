<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\Auditable;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use Auditable, HasApiTokens, HasUuids, SoftDeletes;

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nama',
        'email',
        'password',
        'npp',
        'role',
        'manager_id',
        'last_password_change',
        'foto',
        'tempat_lahir',
        'tanggal_lahir',
        'nik',
        'npwp',
        'alamat',
        'status_kawin',
        'riwayat_pendidikan',
        'riwayat_karir',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'tanggal_lahir' => 'date',
            'last_password_change' => 'datetime',
            'riwayat_pendidikan' => 'array',
            'riwayat_karir' => 'array',
            'password' => 'hashed',
        ];
    }

    public function setRoleAttribute(?string $value): void
    {
        if ($value === null) {
            $this->attributes['role'] = 'Staff';

            return;
        }

        $normalized = strtoupper($value);

        $this->attributes['role'] = match ($normalized) {
            'SUPERADMIN' => 'SuperAdmin',
            'ADMIN' => 'Admin',
            'MANAGER' => 'Staff',
            'STAFF' => 'Staff',
            default => $value,
        };
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(self::class, 'manager_id');
    }

    public function subordinates(): HasMany
    {
        return $this->hasMany(self::class, 'manager_id');
    }

    public function isSuperAdmin(): bool
    {
        return strcasecmp((string) $this->role, 'SuperAdmin') === 0;
    }

    public function isAdmin(): bool
    {
        return strcasecmp((string) $this->role, 'Admin') === 0;
    }

    public function isStaff(): bool
    {
        return strcasecmp((string) $this->role, 'Staff') === 0;
    }

    public function isManager(): bool
    {
        return $this->isStaff() && $this->hasSubordinates();
    }

    public function hasSubordinates(): bool
    {
        return $this->subordinates()->exists();
    }

    public function isPrivileged(): bool
    {
        return $this->isSuperAdmin() || $this->isAdmin();
    }

    public function canManageUser(self $user): bool
    {
        if ($this->isPrivileged()) {
            return true;
        }

        return $this->hasSubordinates() && $user->manager_id === $this->id;
    }
}
