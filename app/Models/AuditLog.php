<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * AuditLog
 *
 * @property int $id
 * @property string $entity_type
 * @property string $entity_id
 * @property string $action
 * @property array<string, mixed>|null $diff
 * @property int|null $user_id
 */
final class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'entity_type',
        'entity_id',
        'action',
        'user_id',
        'diff',
    ];

    protected $casts = [
        'diff' => 'array',
    ];

    /**
     * @return MorphTo<Model, self>
     */
    public function entity(): MorphTo
    {
        return $this->morphTo(name: 'entity');
    }

    /**
     * @return BelongsTo<User, self>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
