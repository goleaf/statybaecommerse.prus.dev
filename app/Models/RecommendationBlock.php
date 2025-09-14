<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final /**
 * RecommendationBlock
 * 
 * Defines recommendation blocks (related_products, you_might_also_like, etc.).
 */
class RecommendationBlock extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'title',
        'description',
        'config_ids',
        'is_active',
        'max_products',
        'cache_duration',
        'display_settings',
    ];

    protected $casts = [
        'config_ids' => 'array',
        'is_active' => 'boolean',
        'max_products' => 'integer',
        'cache_duration' => 'integer',
        'display_settings' => 'array',
    ];

    public function analytics(): HasMany
    {
        return $this->hasMany(RecommendationAnalytics::class, 'block_id');
    }

    public function cache(): HasMany
    {
        return $this->hasMany(RecommendationCache::class, 'block_id');
    }

    public function getConfigs()
    {
        return RecommendationConfig::whereIn('id', $this->config_ids ?? [])
            ->active()
            ->orderedByPriority()
            ->get();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByName($query, string $name)
    {
        return $query->where('name', $name);
    }
}
