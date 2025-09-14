<?php

declare (strict_types=1);
namespace App\Filament\Widgets;

use App\Services\SearchAnalyticsService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
/**
 * SearchAnalyticsWidget
 * 
 * Filament v4 widget for SearchAnalyticsWidget dashboard display with real-time data and interactive features.
 * 
 * @property int|null $sort
 */
final class SearchAnalyticsWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    /**
     * Handle getStats functionality with proper error handling.
     * @return array
     */
    protected function getStats(): array
    {
        $analyticsService = app(SearchAnalyticsService::class);
        // Get search statistics for the last 30 days
        $thirtyDaysAgo = now()->subDays(30);
        $totalSearches = $analyticsService->getTotalSearches($thirtyDaysAgo);
        $uniqueSearches = $analyticsService->getUniqueSearches($thirtyDaysAgo);
        $noResultSearches = $analyticsService->getNoResultSearchesCount($thirtyDaysAgo);
        $averageResults = $analyticsService->getAverageResultsPerSearch($thirtyDaysAgo);
        // Get popular searches
        $popularSearches = $analyticsService->getPopularSearchesForDateRange(5, $thirtyDaysAgo);
        // Get search success rate
        $successRate = $totalSearches > 0 ? round(($totalSearches - $noResultSearches) / $totalSearches * 100, 1) : 0;
        return [Stat::make('Total Searches (30d)', number_format($totalSearches))->description('Searches performed in the last 30 days')->descriptionIcon('heroicon-m-magnifying-glass')->color('primary'), Stat::make('Unique Searches (30d)', number_format($uniqueSearches))->description('Unique search queries in the last 30 days')->descriptionIcon('heroicon-m-sparkles')->color('success'), Stat::make('Success Rate', $successRate . '%')->description('Searches that returned results')->descriptionIcon('heroicon-m-check-circle')->color($successRate >= 80 ? 'success' : ($successRate >= 60 ? 'warning' : 'danger')), Stat::make('Avg Results/Search', number_format($averageResults, 1))->description('Average number of results per search')->descriptionIcon('heroicon-m-chart-bar')->color('info')];
    }
    /**
     * Handle getColumns functionality with proper error handling.
     * @return int
     */
    protected function getColumns(): int
    {
        return 4;
    }
}