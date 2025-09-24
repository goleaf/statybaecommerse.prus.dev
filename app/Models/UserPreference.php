<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\UserOwnedScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * UserPreference
 *
 * Eloquent model representing the UserPreference entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $fillable
 * @property mixed $casts
 *
 * @method static \Illuminate\Database\Eloquent\Builder|UserPreference newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserPreference newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserPreference query()
 *
 * @mixin \Eloquent
 */
#[ScopedBy([UserOwnedScope::class])]
final class UserPreference extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'preference_type', 'preference_key', 'preference_score', 'metadata', 'last_updated'];

    protected $casts = ['preference_score' => 'decimal:6', 'metadata' => 'array', 'last_updated' => 'datetime'];

    /**
     * Handle user functionality with proper error handling.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Handle scopeByType functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('preference_type', $type);
    }

    /**
     * Handle scopeWithMinScore functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeWithMinScore($query, float $minScore)
    {
        return $query->where('preference_score', '>=', $minScore);
    }

    /**
     * Handle scopeOrderedByScore functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeOrderedByScore($query)
    {
        return $query->orderByDesc('preference_score');
    }

    /**
     * Handle scopeRecent functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('last_updated', '>=', now()->subDays($days));
    }
}
