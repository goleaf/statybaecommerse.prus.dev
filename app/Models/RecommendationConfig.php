<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

/**
 * RecommendationConfig
 *
 * Eloquent model representing the RecommendationConfig entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $fillable
 * @property mixed $casts
 *
 * @method static \Illuminate\Database\Eloquent\Builder|RecommendationConfig newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecommendationConfig newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecommendationConfig query()
 *
 * @mixin \Eloquent
 */
final class RecommendationConfig extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'type', 'config', 'is_active', 'is_default', 'priority', 'sort_order', 'filters', 'max_results', 'min_score', 'decay_factor', 'description', 'cache_ttl', 'enable_caching', 'enable_analytics', 'batch_size', 'timeout_seconds', 'conditions', 'notes', 'metadata'];

    protected $casts = ['config' => 'array', 'filters' => 'array', 'conditions' => 'array', 'metadata' => 'array', 'is_active' => 'boolean', 'enable_caching' => 'boolean', 'enable_analytics' => 'boolean', 'priority' => 'integer', 'max_results' => 'integer', 'min_score' => 'decimal:6', 'cache_ttl' => 'integer', 'batch_size' => 'integer', 'timeout_seconds' => 'integer'];

    /**
     * Handle analytics functionality with proper error handling.
     */
    public function analytics(): HasMany
    {
        return $this->hasMany(RecommendationAnalytics::class, 'config_id');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'recommendation_config_products');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'recommendation_config_categories');
    }

    /**
     * Handle scopeActive functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Handle scopeByType functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Handle scopeOrderedByPriority functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeOrderedByPriority($query)
    {
        return $query->orderByDesc('priority');
    }

    /**
     * Handle getAlgorithmClass functionality with proper error handling.
     */
    public function getAlgorithmClass(): string
    {
        return match ($this->type) {
            'content_based' => \App\Services\Recommendations\ContentBasedRecommendation::class,
            'collaborative' => \App\Services\Recommendations\CollaborativeFilteringRecommendation::class,
            'hybrid' => \App\Services\Recommendations\HybridRecommendation::class,
            'popularity' => \App\Services\Recommendations\PopularityRecommendation::class,
            'trending' => \App\Services\Recommendations\TrendingRecommendation::class,
            'cross_sell' => \App\Services\Recommendations\CrossSellRecommendation::class,
            'up_sell' => \App\Services\Recommendations\UpSellRecommendation::class,
            default => \App\Services\Recommendations\ContentBasedRecommendation::class,
        };
    }
}
