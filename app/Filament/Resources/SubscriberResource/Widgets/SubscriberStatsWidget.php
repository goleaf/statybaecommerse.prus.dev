<?php

declare (strict_types=1);
namespace App\Filament\Resources\SubscriberResource\Widgets;

use App\Models\Subscriber;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
/**
 * SubscriberStatsWidget
 * 
 * Filament v4 resource for SubscriberStatsWidget management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class SubscriberStatsWidget extends BaseWidget
{
    /**
     * Handle getStats functionality with proper error handling.
     * @return array
     */
    protected function getStats(): array
    {
        $totalSubscribers = Subscriber::count();
        $activeSubscribers = Subscriber::active()->count();
        $recentSubscribers = Subscriber::recent(30)->count();
        $unsubscribedToday = Subscriber::whereDate('unsubscribed_at', today())->count();
        return [Stat::make('Total Subscribers', number_format($totalSubscribers))->description('All time subscribers')->descriptionIcon('heroicon-m-users')->color('primary'), Stat::make('Active Subscribers', number_format($activeSubscribers))->description(sprintf('%.1f%% of total', $totalSubscribers > 0 ? $activeSubscribers / $totalSubscribers * 100 : 0))->descriptionIcon('heroicon-m-check-circle')->color('success'), Stat::make('Recent Subscribers', number_format($recentSubscribers))->description('Last 30 days')->descriptionIcon('heroicon-m-arrow-trending-up')->color('info'), Stat::make('Unsubscribed Today', number_format($unsubscribedToday))->description('Today\'s unsubscribes')->descriptionIcon('heroicon-m-arrow-trending-down')->color($unsubscribedToday > 0 ? 'warning' : 'success')];
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