<?php

declare(strict_types=1);

namespace App\Filament\Resources\DiscountConditionResource\Widgets;

use App\Models\DiscountCondition;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

final class DiscountConditionStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalConditions = DiscountCondition::count();
        $activeConditions = DiscountCondition::where('is_active', true)->count();
        $currentConditions = DiscountCondition::where('valid_from', '<=', now())
            ->where(function ($query): void {
                $query->whereNull('valid_until')->orWhere('valid_until', '>=', now());
            })->count();
        $expiredConditions = DiscountCondition::where('valid_until', '<', now())->count();

        return [
            Stat::make(__('discount_conditions.stats.total_conditions'), $totalConditions)
                ->description(__('discount_conditions.stats.total_conditions_description'))
                ->descriptionIcon('heroicon-m-tag')
                ->color('primary'),

            Stat::make(__('discount_conditions.stats.active_conditions'), $activeConditions)
                ->description(__('discount_conditions.stats.active_conditions_description'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make(__('discount_conditions.stats.inactive_conditions'), $inactiveConditions)
                ->description(__('discount_conditions.stats.inactive_conditions_description'))
                ->descriptionIcon('heroicon-m-pause-circle')
                ->color('warning'),

            Stat::make(__('discount_conditions.stats.top_condition_type'), $topTypeLabel)
                ->description($topTypeCount > 0
                    ? trans_choice('discount_conditions.stats.type_usage', $topTypeCount, ['count' => $topTypeCount])
                    : __('discount_conditions.stats.top_condition_type_description'))
                ->descriptionIcon('heroicon-m-chart-pie')
                ->color('info'),
        ];
    }
}
