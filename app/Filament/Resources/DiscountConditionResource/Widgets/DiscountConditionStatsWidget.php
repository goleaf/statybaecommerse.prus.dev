<?php

declare(strict_types=1);

namespace App\Filament\Resources\DiscountConditionResource\Widgets;

use App\Models\DiscountCondition;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class DiscountConditionStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalConditions = DiscountCondition::count();
        $activeConditions = DiscountCondition::where('is_active', true)->count();
        $inactiveConditions = DiscountCondition::where('is_active', false)->count();

        $topType = DiscountCondition::select('type', DB::raw('count(*) as aggregate'))
            ->groupBy('type')
            ->orderByDesc('aggregate')
            ->first();

        $topTypeLabel = $topType?->type
            ? __('discount_conditions.types.' . Str::slug((string) $topType->type, '_'))
            : __('discount_conditions.stats.no_data');

        $topTypeCount = (int) ($topType->aggregate ?? 0);

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
