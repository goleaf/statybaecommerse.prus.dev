<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * RecommendationConfigSimple
 *
 * Eloquent model representing the RecommendationConfigSimple entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 */
final class RecommendationConfigSimple extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'algorithm_type',
        'min_score',
        'max_results',
        'decay_factor',
        'exclude_out_of_stock',
        'exclude_inactive',
        'price_weight',
        'rating_weight',
        'popularity_weight',
        'recency_weight',
        'category_weight',
        'custom_weight',
        'cache_duration',
        'is_active',
        'is_default',
        'sort_order',
        'notes',
    ];

    protected $casts = [
        'exclude_out_of_stock' => 'boolean',
        'exclude_inactive' => 'boolean',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'min_score' => 'decimal:6',
        'decay_factor' => 'decimal:6',
        'price_weight' => 'decimal:6',
        'rating_weight' => 'decimal:6',
        'popularity_weight' => 'decimal:6',
        'recency_weight' => 'decimal:6',
        'category_weight' => 'decimal:6',
        'custom_weight' => 'decimal:6',
        'max_results' => 'integer',
        'cache_duration' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * Products relationship
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'recommendation_config_simple_products');
    }

    /**
     * Categories relationship
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'recommendation_config_simple_categories');
    }

    /**
     * Analytics relationship
     */
    public function analytics(): HasMany
    {
        return $this->hasMany(RecommendationAnalytics::class, 'config_simple_id');
    }

    /**
     * Scope for active configs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for default config
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope by algorithm type
     */
    public function scopeByAlgorithmType($query, string $algorithmType)
    {
        return $query->where('algorithm_type', $algorithmType);
    }

    /**
     * Scope ordered by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
