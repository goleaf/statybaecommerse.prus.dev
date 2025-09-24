<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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

    protected $fillable = ['name', 'title', 'description', 'config_ids', 'is_active', 'max_products', 'cache_duration', 'display_settings'];

    protected $casts = ['config_ids' => 'array', 'is_active' => 'boolean', 'max_products' => 'integer', 'cache_duration' => 'integer', 'display_settings' => 'array'];

    /**
     * Handle analytics functionality with proper error handling.
     */
    public function analytics(): HasMany
    {
        return $this->hasMany(RecommendationAnalytics::class, 'block_id');
    }

    /**
     * Handle cache functionality with proper error handling.
     */
    public function cache(): HasMany
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
     * @param  mixed  $query
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Handle scopeByName functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeByName($query, string $name)
    {
        return $query->where('name', $name);
    }
}
