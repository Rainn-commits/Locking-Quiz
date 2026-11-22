<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'is_banned',
        'banned_at',
        'jumlah_pelanggaran',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_banned' => 'boolean',
            'banned_at' => 'datetime',
        ];
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function unlockTokens(): HasMany
    {
        return $this->hasMany(UnlockToken::class, 'student_id');
    }

    public function issuedUnlockTokens(): HasMany
    {
        return $this->hasMany(UnlockToken::class, 'issued_by');
    }

    public function ban(): void
    {
        $this->update(['is_banned' => true, 'banned_at' => now()]);
    }

    public function unban(): void
    {
        $this->update(['is_banned' => false, 'banned_at' => null]);
    }
}
