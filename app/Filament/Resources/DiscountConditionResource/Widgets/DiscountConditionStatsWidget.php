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
            ->where(function ($query) {
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

            Stat::make(__('discount_conditions.stats.current_conditions'), $currentConditions)
                ->description(__('discount_conditions.stats.current_conditions_description'))
                ->descriptionIcon('heroicon-m-clock')
                ->color('info'),

            Stat::make(__('discount_conditions.stats.expired_conditions'), $expiredConditions)
                ->description(__('discount_conditions.stats.expired_conditions_description'))
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
        ];
    }
}
