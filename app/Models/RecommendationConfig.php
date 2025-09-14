<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final /**
 * RecommendationConfig
 * 
 * Configuration for recommendation algorithms and their parameters.
 */
class RecommendationConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'config',
        'is_active',
        'priority',
        'filters',
        'max_results',
        'min_score',
        'description',
    ];

    protected $casts = [
        'config' => 'array',
        'filters' => 'array',
        'is_active' => 'boolean',
        'priority' => 'integer',
        'max_results' => 'integer',
        'min_score' => 'decimal:6',
    ];

    public function analytics(): HasMany
    {
        return $this->hasMany(RecommendationAnalytics::class, 'config_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeOrderedByPriority($query)
    {
        return $query->orderByDesc('priority');
    }

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
