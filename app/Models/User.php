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

    public const ROLE_BISA_RESET = ['guru', 'siswa'];

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

    public function isGuru(): bool
    {
        return $this->role === 'guru';
    }

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

    public function bisaDiresetPassword(): bool
    {
        return in_array($this->role, self::ROLE_BISA_RESET, true);
    }

    public function resetPassword(string $passwordBaru): void
    {
        $this->forceFill([
            'password'       => $passwordBaru,
            'remember_token' => null,
        ])->save();

        $this->tokens()->delete();
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class, 'creator_id');
    }
}
