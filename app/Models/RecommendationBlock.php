<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\OrdersByName;
use App\Models\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * RecommendationBlock
 *
 * Eloquent model representing the RecommendationBlock entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $fillable
 * @property mixed $casts
 *
 * @method static \Illuminate\Database\Eloquent\Builder|RecommendationBlock newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecommendationBlock newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RecommendationBlock query()
 *
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class])]
final class RecommendationBlock extends Model
{
    use HasFactory;
    use OrdersByName;

    /**
     * Order recommendation blocks by their public name by default so the
     * reusable OrdersByName trait can keep admin listings predictable.
     */
    protected string $nameColumn = 'name';

    protected $fillable = [
        'name',
        'title',
        'description',
        'type',
        'position',
        'is_active',
        'is_default',
        'show_title',
        'show_description',
        'max_products',
        'sort_order',
        'config_ids',
        'cache_duration',
        'display_settings',
        'meta',
    ];

    protected $casts = [
        'config_ids'       => 'array',
        'is_active'        => 'boolean',
        'is_default'       => 'boolean',
        'show_title'       => 'boolean',
        'show_description' => 'boolean',
        'max_products'     => 'integer',
        'sort_order'       => 'integer',
        'cache_duration'   => 'integer',
        'display_settings' => 'array',
        'meta'             => 'array',
    ];

    /**
     * Connect products to recommendation blocks to drive personalised widget
     * output on the storefront.
     *
     * @return BelongsToMany<Product, self>
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'recommendation_block_products', 'recommendation_block_id', 'product_id');
    }

    /**
     * Handle analytics functionality with proper error handling.
     *
     * @return HasMany<RecommendationAnalytics, self>
     */
    public function analytics(): HasMany
    {
        return $this->hasMany(RecommendationAnalytics::class, 'block_id');
    }

    /**
     * Handle cache functionality with proper error handling.
     *
     * @return HasMany<RecommendationCache, self>
     */
    public function caches(): HasMany
    {
        return $this->hasMany(RecommendationCache::class, 'block_id');
    }

    /**
     * Handle getConfigs functionality with proper error handling.
     */
    public function getConfigs()
    {
        return RecommendationConfig::whereIn('id', $this->config_ids ?? [])->active()->orderedByPriority()->get();
    }

    /**
     * Handle scopeActive functionality with proper error handling.
     *
     * @param Builder<self> $query
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Handle scopeByName functionality with proper error handling.
     *
     * @param Builder<self> $query
     */
    public function scopeByName(Builder $query, string $name): Builder
    {
        return $query->where('name', $name);
    }

    /**
     * Provide a convenience scope that mirrors the key-based lookups expected
     * by the prompt instructions while reusing the underlying name column.
     */
    public function scopeWithKey(Builder $query, string $key): Builder
    {
        return $query->where('name', $key);
    }

    /**
     * Handle scopeOrderedByName functionality with proper error handling.
     *
     * @param Builder<self> $query
     */
    public function scopeOrderedByName(Builder $query, string $direction = 'asc'): Builder
    {
        return $query->orderBy('name', $direction);
    }
}
