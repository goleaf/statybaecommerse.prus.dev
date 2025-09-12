<?php declare(strict_types=1);

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
        $inactiveConditions = DiscountCondition::where('is_active', false)->count();
        $highPriorityConditions = DiscountCondition::where('priority', '>', 5)->count();

        return [
            Stat::make(__('discount_conditions.stats.total_conditions'), $totalConditions)
                ->description(__('discount_conditions.stats.total_conditions_description'))
                ->descriptionIcon('heroicon-m-cog-6-tooth')
                ->color('primary'),

            Stat::make(__('discount_conditions.stats.active_conditions'), $activeConditions)
                ->description(__('discount_conditions.stats.active_conditions_description'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make(__('discount_conditions.stats.inactive_conditions'), $inactiveConditions)
                ->description(__('discount_conditions.stats.inactive_conditions_description'))
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('warning'),

            Stat::make(__('discount_conditions.stats.high_priority_conditions'), $highPriorityConditions)
                ->description(__('discount_conditions.stats.high_priority_conditions_description'))
                ->descriptionIcon('heroicon-m-arrow-up')
                ->color('danger'),
        ];
    }
}
