<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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
     * Scope logs to a particular audited entity using either the model instance or morph alias.
     *
     * @param  Builder<self>        $query
     * @param  Model|string         $entity   The audited model instance or morph alias.
     * @param  int|string|null      $entityId Optional identifier when resolving by alias.
     * @return Builder<self>
     */
    public function scopeForEntity(Builder $query, Model|string $entity, int|string|null $entityId = null): Builder
    {
        // Normalise the morph type so both model instances and string aliases resolve identically.
        $morphType = $entity instanceof Model ? $entity->getMorphClass() : $entity;

        // Determine the identifier, preferring the model key when an instance is supplied.
        $resolvedId = $entity instanceof Model ? (string) $entity->getKey() : $entityId;

        // Filter by the morph type and, when available, the specific entity identifier for precision.
        return $query
            ->where('entity_type', $morphType)
            ->when(
                $resolvedId !== null,
                fn (Builder $builder): Builder => $builder->where('entity_id', (string) $resolvedId),
            );
    }

    /**
     * Restrict logs to a given audit action such as "created" or "updated".
     *
     * @param  Builder<self> $query
     * @param  string        $action
     * @return Builder<self>
     */
    public function scopeForAction(Builder $query, string $action): Builder
    {
        // Apply a straightforward where clause so consumers avoid repeating boilerplate logic.
        return $query->where('action', $action);
    }

    /**
     * Filter logs to those triggered by a specific user identifier.
     *
     * @param  Builder<self>   $query
     * @param  int|string|null $userId
     * @return Builder<self>
     */
    public function scopeForUser(Builder $query, int|string|null $userId): Builder
    {
        // Ensure nullable identifiers are handled gracefully to support anonymous audit records.
        return $query->where('user_id', $userId);
    }

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
