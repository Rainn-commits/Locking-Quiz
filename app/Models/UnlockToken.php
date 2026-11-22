<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class UnlockToken extends Model
{
    public const TTL_MINUTES = 30;

    const UPDATED_AT = null;

    protected $fillable = [
        'student_id',
        'issued_by',
        'token',
        'expires_at',
        'used_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at'    => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function isValid(): bool
    {
        return $this->used_at === null && $this->expires_at->isFuture();
    }

    public function consume(): void
    {
        $this->update(['used_at' => now()]);
    }

    public static function issueFor(User $student, User $issuer): self
    {
        static::revokeActiveFor($student);

        do {
            $code = strtoupper(Str::random(6));
        } while (static::where('token', $code)->exists());

        return static::create([
            'student_id' => $student->id,
            'issued_by'  => $issuer->id,
            'token'      => $code,
            'expires_at' => now()->addMinutes(self::TTL_MINUTES),
        ]);
    }

    public static function revokeActiveFor(User $student): void
    {
        static::where('student_id', $student->id)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->update(['expires_at' => now()]);
    }
}
