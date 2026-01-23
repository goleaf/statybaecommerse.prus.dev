<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Discount;
use App\Models\DiscountRedemption;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DiscountRedemptionStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalRedemptions = DiscountRedemption::count();
        $pendingRedemptions = DiscountRedemption::where('status', 'pending')->count();
        $redeemedRedemptions = DiscountRedemption::where('status', 'redeemed')->count();
        $totalAmountSaved = DiscountRedemption::where('status', 'redeemed')->sum('amount_saved');

        $recentRedemptions = DiscountRedemption::where('redeemed_at', '>=', now()->subDays(7))->count();
        $averageAmountSaved = DiscountRedemption::where('status', 'redeemed')->avg('amount_saved') ?? 0;

        $topDiscount = Discount::withCount('redemptions')
            ->orderBy('redemptions_count', 'desc')
            ->first();

        return [
            Stat::make(__('admin.discount_redemption_stats.total_redemptions'), $totalRedemptions)
                ->description(__('admin.discount_redemption_stats.all_time_redemptions'))
                ->descriptionIcon('heroicon-m-ticket')
                ->color('primary'),
            Stat::make(__('admin.discount_redemption_stats.pending_redemptions'), $pendingRedemptions)
                ->description(__('admin.discount_redemption_stats.awaiting_processing'))
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
            Stat::make(__('admin.discount_redemption_stats.redeemed'), $redeemedRedemptions)
                ->description(__('admin.discount_redemption_stats.successfully_redeemed'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make(__('admin.discount_redemption_stats.total_amount_saved'), '€' . number_format($totalAmountSaved, 2))
                ->description(__('admin.discount_redemption_stats.customer_savings'))
                ->descriptionIcon('heroicon-m-currency-euro')
                ->color('success'),
            Stat::make(__('admin.discount_redemption_stats.recent_redemptions'), $recentRedemptions)
                ->description(__('admin.discount_redemption_stats.last_7_days'))
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),
            Stat::make(__('admin.discount_redemption_stats.average_amount'), '€' . number_format($averageAmountSaved, 2))
                ->description(__('admin.discount_redemption_stats.per_redemption'))
                ->descriptionIcon('heroicon-m-calculator')
                ->color('info'),
        ];
    }

    protected function getColumns(): int|array|null
    {
        return 3;
    }
}
