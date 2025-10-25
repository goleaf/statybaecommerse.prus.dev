<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\OrdersByName;
use App\Models\Scopes\DateRangeScope;
use App\Models\Scopes\UserOwnedScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * RecommendationCache
 *
 * Eloquent model representing the RecommendationCache entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $fillable
 * @property mixed $casts
 *
 * @method static \Illuminate\Database\Eloquent\Builder|RecommendationCache newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecommendationCache newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecommendationCache query()
 *
 * @mixin \Eloquent
 */
#[ScopedBy([DateRangeScope::class, UserOwnedScope::class])]
final class RecommendationCache extends Model
{
    use HasFactory;
    use OrdersByName;

    /**
     * Configure the reusable ordering helper to rely on the cache_key column so
     * repeated lookups within admin tables remain alphabetically sorted.
     */
    protected string $nameColumn = 'cache_key';

    protected $table = 'recommendation_cache';

    protected $fillable = ['cache_key', 'block_id', 'user_id', 'product_id', 'context_type', 'context_data', 'recommendations', 'hit_count', 'expires_at', 'meta'];

    protected $casts = ['context_data' => 'array', 'recommendations' => 'array', 'expires_at' => 'datetime', 'hit_count' => 'integer', 'meta' => 'array'];

    /**
     * Handle block functionality with proper error handling.
     */
    public function block(): BelongsTo
    {
        return $this->belongsTo(RecommendationBlock::class, 'block_id');
    }

    /**
     * Handle user functionality with proper error handling.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Handle product functionality with proper error handling.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Handle scopeValid functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopeValid(Builder $query): Builder
    {
        return $query->where('expires_at', '>', now());
    }

    /**
     * Handle scopeExpired functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('expires_at', '<=', now());
    }

    /**
     * Handle scopeByKey functionality with proper error handling.
     *
     * @param mixed $query
     */
    public function scopeByKey(Builder $query, string $cacheKey): Builder
    {
        return $query->where('cache_key', $cacheKey);
    }

    /**
     * Handle incrementHitCount functionality with proper error handling.
     */
    public function incrementHitCount(): void
    {
        $this->increment('hit_count');
    }

    /**
     * Handle isExpired functionality with proper error handling.
     */
    public function isExpired(): bool
    {
        return $this->expires_at <= now();
    }

    /**
     * Handle generateCacheKey functionality with proper error handling.
     */
    public static function generateCacheKey(string $blockName, ?int $userId = null, ?int $productId = null, ?string $contextType = null, ?array $contextData = null): string
    {
        $parts = [$blockName];
        if ($userId) {
            $parts[] = "user:{$userId}";
        }
        if ($productId) {
            $parts[] = "product:{$productId}";
        }
        if ($contextType) {
            $parts[] = "context:{$contextType}";
        }
        if ($contextData) {
            $parts[] = 'data:' . md5(serialize($contextData));
        }

        return implode('|', $parts);
    }
}
