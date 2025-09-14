<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\RecommendationBlock;
use App\Models\RecommendationCache;
use App\Services\RecommendationService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

final class RecommendationAnalyticsWidget extends BaseWidget
{
    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $service = app(RecommendationService::class);
        
        return [
            Stat::make(__('translations.total_recommendation_blocks'), RecommendationBlock::active()->count())
                ->description(__('translations.active_blocks'))
                ->descriptionIcon('heroicon-m-squares-2x2')
                ->color('success'),

            Stat::make(__('translations.cache_hit_rate'), $this->getCacheHitRate())
                ->description(__('translations.last_24_hours'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('info'),

            Stat::make(__('translations.total_recommendations'), $this->getTotalRecommendations())
                ->description(__('translations.last_24_hours'))
                ->descriptionIcon('heroicon-m-cube')
                ->color('primary'),

            Stat::make(__('translations.avg_response_time'), $this->getAverageResponseTime())
                ->description(__('translations.milliseconds'))
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
        ];
    }

    private function getCacheHitRate(): string
    {
        $totalRequests = RecommendationCache::where('created_at', '>=', now()->subDay())
            ->sum('hit_count');

        $cacheHits = RecommendationCache::where('created_at', '>=', now()->subDay())
            ->where('hit_count', '>', 0)
            ->sum('hit_count');

        if ($totalRequests === 0) {
            return '0%';
        }

        $hitRate = ($cacheHits / $totalRequests) * 100;
        return number_format($hitRate, 1) . '%';
    }

    private function getTotalRecommendations(): string
    {
        $total = RecommendationCache::where('created_at', '>=', now()->subDay())
            ->sum(DB::raw('JSON_LENGTH(recommendations)'));

        return number_format($total);
    }

    private function getAverageResponseTime(): string
    {
        // This would need to be implemented with proper logging
        // For now, return a placeholder
        return '150ms';
    }
}
