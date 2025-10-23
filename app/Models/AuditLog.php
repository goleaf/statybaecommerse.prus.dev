<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * AuditLog model encapsulates the persisted history of model mutations so that
 * the admin can review a concise trail of who changed what and when.
 *
 * @property int                       $id
 * @property string                    $entity_type
 * @property string                    $entity_id
 * @property string                    $action
 * @property array<string, mixed>|null $diff
 * @property int|null                  $user_id
 */
final class AuditLog extends Model
{
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'entity_type',
        'entity_id',
        'action',
        'user_id',
        'diff',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'diff' => 'array', // Cast diff payload to an array for reliable comparisons.
    ];

    /**
     * The audited entity is configured as a morph so any model can attach logs.
     *
     * @return MorphTo<Model, self>
     */
    public function entity(): MorphTo
    {
        return $this->morphTo(name: 'entity');
    }

    /**
     * Track which user triggered the state change for accountability.
     *
     * @return BelongsTo<User, self>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
