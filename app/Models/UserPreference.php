<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\OrdersByName;
use App\Models\Scopes\UserOwnedScope;
use Database\Factories\UserPreferenceFactory;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * UserPreference
 *
 * Eloquent model representing the UserPreference entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property int                             $id
 * @property int                             $user_id
 * @property string                          $preference_type
 * @property string                          $preference_key
 * @property float|null                      $preference_score
 * @property array<string, mixed>|null       $metadata
 * @property \Illuminate\Support\Carbon      $last_updated
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|UserPreference newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserPreference newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserPreference query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserPreference byType(string $type)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPreference withMinScore(float $minScore)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPreference orderedByScore()
 * @method static \Illuminate\Database\Eloquent\Builder|UserPreference recent(int $days = 30)
 *
 * @mixin \Eloquent
 */

/**
 * @use HasFactory<UserPreferenceFactory>
 */
#[ScopedBy([UserOwnedScope::class])]
final class UserPreference extends Model
{
    /** @use HasFactory<UserPreferenceFactory> */
    use HasFactory;

    use OrdersByName; // Keep preference listings sorted by their key identifiers.

    protected $fillable = ['user_id', 'preference_type', 'preference_key', 'preference_score', 'metadata', 'last_updated'];

    protected $casts = ['metadata' => 'array', 'last_updated' => 'datetime'];

    /**
     * Provide a float-based accessor/mutator for preference scores while keeping six-decimal precision.
     *
     * @return Attribute<float|null, float|null>
     */
    protected function preferenceScore(): Attribute
    {
        return Attribute::make(
            get: static fn (mixed $value): ?float => is_numeric($value) ? round((float) $value, 6) : null,
            set: static fn (mixed $value): ?float => is_numeric($value) ? round((float) $value, 6) : null,
        );
    }

    /**
     * Handle user functionality with proper error handling.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Handle scopeByType functionality with proper error handling.
     *
     * @param  Builder<UserPreference> $query
     * @return Builder<UserPreference>
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('preference_type', $type);
    }

    /**
     * Handle scopeWithMinScore functionality with proper error handling.
     *
     * @param  Builder<UserPreference> $query
     * @return Builder<UserPreference>
     */
    public function scopeWithMinScore(Builder $query, float $minScore): Builder
    {
        return $query->where('preference_score', '>=', $minScore);
    }

    /**
     * Handle scopeOrderedByScore functionality with proper error handling.
     *
     * @param  Builder<UserPreference> $query
     * @return Builder<UserPreference>
     */
    public function scopeOrderedByScore(Builder $query): Builder
    {
        return $query->orderByDesc('preference_score');
    }

    /**
     * Handle scopeRecent functionality with proper error handling.
     *
     * @param  Builder<UserPreference> $query
     * @return Builder<UserPreference>
     */
    public function scopeRecent(Builder $query, int $days = 30): Builder
    {
        return $query->where('last_updated', '>=', now()->subDays($days));
    }

    /**
     * Column mapped to the OrdersByName scope.
     */
    protected string $nameColumn = 'preference_key';
}
