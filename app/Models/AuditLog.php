<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'action',
        'entity',
        'entity_id',
        'ip_address',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function record(?User $actor, string $action, string $entity, ?int $entityId = null, ?string $ip = null): self
    {
        return static::create([
            'user_id'    => $actor?->id,
            'action'     => $action,
            'entity'     => $entity,
            'entity_id'  => $entityId,
            'ip_address' => $ip,
        ]);
    }
}
