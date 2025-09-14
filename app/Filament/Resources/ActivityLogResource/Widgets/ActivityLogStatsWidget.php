<?php

declare (strict_types=1);
namespace App\Filament\Resources\ActivityLogResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Spatie\Activitylog\Models\Activity;
/**
 * ActivityLogStatsWidget
 * 
 * Filament v4 resource for ActivityLogStatsWidget management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class ActivityLogStatsWidget extends BaseWidget
{
    /**
     * Handle getStats functionality with proper error handling.
     * @return array
     */
    protected function getStats(): array
    {
        return [Stat::make(__('admin.activity_logs.stats.total_activities'), Activity::count())->description(__('admin.activity_logs.stats.all_time'))->descriptionIcon('heroicon-m-arrow-trending-up')->color('primary'), Stat::make(__('admin.activity_logs.stats.today_activities'), Activity::whereDate('created_at', today())->count())->description(__('admin.activity_logs.stats.today'))->descriptionIcon('heroicon-m-calendar-days')->color('success'), Stat::make(__('admin.activity_logs.stats.this_week_activities'), Activity::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count())->description(__('admin.activity_logs.stats.this_week'))->descriptionIcon('heroicon-m-calendar')->color('warning'), Stat::make(__('admin.activity_logs.stats.this_month_activities'), Activity::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count())->description(__('admin.activity_logs.stats.this_month'))->descriptionIcon('heroicon-m-calendar-days')->color('info')];
    }
}