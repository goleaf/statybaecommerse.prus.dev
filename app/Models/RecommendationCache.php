<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final /**
 * RecommendationCache
 * 
 * Caches recommendation results for performance optimization.
 */
class RecommendationCache extends Model
{
    use HasFactory;

    protected $fillable = [
        'cache_key',
        'block_id',
        'user_id',
        'product_id',
        'context_type',
        'context_data',
        'recommendations',
        'hit_count',
        'expires_at',
    ];

    protected $casts = [
        'context_data' => 'array',
        'recommendations' => 'array',
        'expires_at' => 'datetime',
        'hit_count' => 'integer',
    ];

    public function block(): BelongsTo
    {
        return $this->belongsTo(RecommendationBlock::class, 'block_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeValid($query)
    {
        return $query->where('expires_at', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    public function scopeByKey($query, string $cacheKey)
    {
        return $query->where('cache_key', $cacheKey);
    }

    public function incrementHitCount(): void
    {
        $this->increment('hit_count');
    }

    public function isExpired(): bool
    {
        return $this->expires_at <= now();
    }

    public static function generateCacheKey(
        string $blockName,
        ?int $userId = null,
        ?int $productId = null,
        ?string $contextType = null,
        ?array $contextData = null
    ): string {
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
