<?php

declare (strict_types=1);
namespace App\Filament\Widgets;

use App\Models\Subscriber;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
/**
 * SubscriberDashboardWidget
 * 
 * Filament v4 widget for SubscriberDashboardWidget dashboard display with real-time data and interactive features.
 * 
 * @property string|null $pollingInterval
 */
final class SubscriberDashboardWidget extends BaseWidget
{
    protected ?string $pollingInterval = '30s';
    /**
     * Handle getStats functionality with proper error handling.
     * @return array
     */
    protected function getStats(): array
    {
        $totalSubscribers = Subscriber::count();
        $activeSubscribers = Subscriber::active()->count();
        $recentSubscribers = Subscriber::recent(7)->count();
        $unsubscribedToday = Subscriber::whereDate('unsubscribed_at', today())->count();
        // Growth rate calculation
        $lastWeekSubscribers = Subscriber::where('subscribed_at', '>=', now()->subDays(14))->where('subscribed_at', '<', now()->subDays(7))->count();
        $thisWeekSubscribers = Subscriber::recent(7)->count();
        $growthRate = $lastWeekSubscribers > 0 ? ($thisWeekSubscribers - $lastWeekSubscribers) / $lastWeekSubscribers * 100 : 0;
        // Source breakdown
        $sourceBreakdown = Subscriber::select('source', DB::raw('count(*) as count'))->groupBy('source')->orderBy('count', 'desc')->limit(3)->get();
        return [Stat::make('Total Subscribers', number_format($totalSubscribers))->description('All time subscribers')->descriptionIcon('heroicon-m-users')->color('primary')->chart($this->getSubscriberGrowthChart()), Stat::make('Active Subscribers', number_format($activeSubscribers))->description(sprintf('%.1f%% of total', $totalSubscribers > 0 ? $activeSubscribers / $totalSubscribers * 100 : 0))->descriptionIcon('heroicon-m-check-circle')->color('success')->chart($this->getActiveSubscribersChart()), Stat::make('Recent Subscribers', number_format($recentSubscribers))->description(sprintf('%+.1f%% from last week', $growthRate))->descriptionIcon($growthRate >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')->color($growthRate >= 0 ? 'info' : 'warning'), Stat::make('Top Source', $sourceBreakdown->first()?->source ?? 'N/A')->description($sourceBreakdown->first() ? number_format($sourceBreakdown->first()->count) . ' subscribers' : 'No data')->descriptionIcon('heroicon-m-arrow-up-right')->color('gray')];
    }
    /**
     * Handle getColumns functionality with proper error handling.
     * @return int
     */
    protected function getColumns(): int
    {
        return 4;
    }
    /**
     * Handle getSubscriberGrowthChart functionality with proper error handling.
     * @return array
     */
    private function getSubscriberGrowthChart(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = Subscriber::whereDate('subscribed_at', $date)->count();
            $data[] = $count;
        }
        return $data;
    }
    /**
     * Handle getActiveSubscribersChart functionality with proper error handling.
     * @return array
     */
    private function getActiveSubscribersChart(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = Subscriber::where('status', 'active')->whereDate('created_at', $date)->count();
            $data[] = $count;
        }
        return $data;
    }
}