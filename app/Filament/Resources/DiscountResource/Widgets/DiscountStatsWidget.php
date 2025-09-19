<?php declare(strict_types=1);

namespace App\Filament\Resources\DiscountResource\Widgets;

use App\Models\Discount;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

final class DiscountStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalDiscounts = Discount::count();
        $activeDiscounts = Discount::where('is_active', true)->count();
        $totalUsage = Discount::sum('usage_count');
        $totalSavings = Discount::sum('value');

        return [
            Stat::make('Total Discounts', $totalDiscounts)
                ->description('All discount campaigns')
                ->descriptionIcon('heroicon-m-tag')
                ->color('primary'),
            Stat::make('Active Discounts', $activeDiscounts)
                ->description('Currently active campaigns')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make('Total Usage', number_format($totalUsage))
                ->description('Times discounts have been used')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('info'),
            Stat::make('Total Value', '€' . number_format($totalSavings, 2))
                ->description('Combined discount value')
                ->descriptionIcon('heroicon-m-currency-euro')
                ->color('warning'),
        ];
    }
}


