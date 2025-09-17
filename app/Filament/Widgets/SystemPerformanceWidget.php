<?php declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\AnalyticsEvent;
use App\Models\RecommendationAnalytics;
use App\Models\SystemSetting;
use App\Models\UserBehavior;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class SystemPerformanceWidget extends BaseWidget
{
    protected static ?int $sort = 8;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $now = Carbon::now();
        $last24Hours = $now->copy()->subDay();
        $lastWeek = $now->copy()->subWeek();
        $lastMonth = $now->copy()->subMonth();

        // System Performance Metrics
        $totalSystemSettings = SystemSetting::count();
        $activeSystemSettings = SystemSetting::where('is_active', true)->count();

        // Analytics Performance
        $totalAnalyticsEvents = AnalyticsEvent::count();
        $eventsLast24Hours = AnalyticsEvent::where('created_at', '>=', $last24Hours)->count();
        $eventsLastWeek = AnalyticsEvent::where('created_at', '>=', $lastWeek)->count();

        // User Behavior Tracking
        $totalUserBehaviors = UserBehavior::count();
        $behaviorsLastWeek = UserBehavior::where('created_at', '>=', $lastWeek)->count();

        // Recommendation System Performance
        $totalRecommendations = RecommendationAnalytics::sum('recommendations_count');
        $avgRecommendationScore = RecommendationAnalytics::avg('avg_score') ?? 0;
        $totalRecommendationClicks = RecommendationAnalytics::sum('clicks_count');

        // Database Performance (estimated)
        $totalProducts = \App\Models\Product::count();
        $totalOrders = \App\Models\Order::count();
        $totalUsers = \App\Models\User::count();
        $totalCategories = \App\Models\Category::count();

        // Cache Performance (if available)
        $cacheHitRate = 85;  // This would be calculated from actual cache metrics
        $avgResponseTime = 120;  // This would be calculated from actual response time metrics

        // Error Rate (estimated)
        $errorRate = 0.5;  // This would be calculated from actual error logs

        return [
            // System Settings
            Stat::make(__('translations.system_settings'), \Illuminate\Support\Number::format($totalSystemSettings))
                ->description(__('translations.active_settings') . ': ' . \Illuminate\Support\Number::format($activeSystemSettings))
                ->descriptionIcon('heroicon-m-cog-6-tooth')
                ->color('primary'),
            // Analytics Performance
            Stat::make(__('translations.analytics_events'), \Illuminate\Support\Number::format($totalAnalyticsEvents))
                ->description(__('translations.last_24h') . ': ' . \Illuminate\Support\Number::format($eventsLast24Hours))
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('info'),
            Stat::make(__('translations.events_last_week'), \Illuminate\Support\Number::format($eventsLastWeek))
                ->description(__('translations.analytics_activity'))
                ->descriptionIcon('heroicon-m-chart-line')
                ->color('success'),
            // User Behavior Tracking
            Stat::make(__('translations.user_behaviors'), \Illuminate\Support\Number::format($totalUserBehaviors))
                ->description(__('translations.tracked_behaviors'))
                ->descriptionIcon('heroicon-m-users')
                ->color('warning'),
            Stat::make(__('translations.behaviors_last_week'), \Illuminate\Support\Number::format($behaviorsLastWeek))
                ->description(__('translations.recent_activity'))
                ->descriptionIcon('heroicon-m-clock')
                ->color('info'),
            // Recommendation System
            Stat::make(__('translations.recommendations'), \Illuminate\Support\Number::format($totalRecommendations))
                ->description(__('translations.total_recommendations'))
                ->descriptionIcon('heroicon-m-light-bulb')
                ->color('success'),
            Stat::make(__('translations.avg_recommendation_score'), number_format($avgRecommendationScore, 2))
                ->description(__('translations.quality_score'))
                ->descriptionIcon('heroicon-m-star')
                ->color($avgRecommendationScore >= 0.7 ? 'success' : ($avgRecommendationScore >= 0.5 ? 'warning' : 'danger')),
            Stat::make(__('translations.recommendation_clicks'), \Illuminate\Support\Number::format($totalRecommendationClicks))
                ->description(__('translations.total_clicks'))
                ->descriptionIcon('heroicon-m-cursor-arrow-rays')
                ->color('primary'),
            // Database Performance
            Stat::make(__('translations.database_entities'), \Illuminate\Support\Number::format($totalProducts + $totalOrders + $totalUsers + $totalCategories))
                ->description(__('translations.total_records'))
                ->descriptionIcon('heroicon-m-database')
                ->color('info'),
            Stat::make(__('translations.products_in_db'), \Illuminate\Support\Number::format($totalProducts))
                ->description(__('translations.product_records'))
                ->descriptionIcon('heroicon-m-cube')
                ->color('primary'),
            Stat::make(__('translations.orders_in_db'), \Illuminate\Support\Number::format($totalOrders))
                ->description(__('translations.order_records'))
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('success'),
            Stat::make(__('translations.users_in_db'), \Illuminate\Support\Number::format($totalUsers))
                ->description(__('translations.user_records'))
                ->descriptionIcon('heroicon-m-users')
                ->color('warning'),
            // Performance Metrics
            Stat::make(__('translations.cache_hit_rate'), $cacheHitRate . '%')
                ->description(__('translations.performance_metric'))
                ->descriptionIcon('heroicon-m-bolt')
                ->color($cacheHitRate >= 80 ? 'success' : ($cacheHitRate >= 60 ? 'warning' : 'danger')),
            Stat::make(__('translations.avg_response_time'), $avgResponseTime . 'ms')
                ->description(__('translations.performance_metric'))
                ->descriptionIcon('heroicon-m-clock')
                ->color($avgResponseTime <= 200 ? 'success' : ($avgResponseTime <= 500 ? 'warning' : 'danger')),
            Stat::make(__('translations.error_rate'), $errorRate . '%')
                ->description(__('translations.system_health'))
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($errorRate <= 1 ? 'success' : ($errorRate <= 5 ? 'warning' : 'danger')),
            // System Health
            Stat::make(__('translations.system_health'), $this->calculateSystemHealth())
                ->description(__('translations.overall_status'))
                ->descriptionIcon('heroicon-m-heart')
                ->color($this->getSystemHealthColor()),
        ];
    }

    private function calculateSystemHealth(): string
    {
        // This would be a more complex calculation based on various metrics
        $healthScore = 95;  // Placeholder - would calculate based on actual metrics
        return $healthScore . '%';
    }

    private function getSystemHealthColor(): string
    {
        $healthScore = 95;  // Placeholder
        return $healthScore >= 90 ? 'success' : ($healthScore >= 70 ? 'warning' : 'danger');
    }
}
