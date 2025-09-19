<?php declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Discount;
use App\Models\DiscountRedemption;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

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
            Stat::make('Total Redemptions', $totalRedemptions)
                ->description('All time redemptions')
                ->descriptionIcon('heroicon-m-ticket')
                ->color('primary'),
            Stat::make('Pending Redemptions', $pendingRedemptions)
                ->description('Awaiting processing')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
            Stat::make('Redeemed', $redeemedRedemptions)
                ->description('Successfully redeemed')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make('Total Amount Saved', '€' . number_format($totalAmountSaved, 2))
                ->description('Customer savings')
                ->descriptionIcon('heroicon-m-currency-euro')
                ->color('success'),
            Stat::make('Recent Redemptions', $recentRedemptions)
                ->description('Last 7 days')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),
            Stat::make('Average Amount', '€' . number_format($averageAmountSaved, 2))
                ->description('Per redemption')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('info'),
        ];
    }

    protected function getColumns(): int
    {
        return 3;
    }
}

