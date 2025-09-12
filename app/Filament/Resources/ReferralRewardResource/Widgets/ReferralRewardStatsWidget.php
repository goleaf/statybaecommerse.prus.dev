<?php declare(strict_types=1);

namespace App\Filament\Resources\ReferralRewardResource\Widgets;

use App\Models\ReferralReward;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

final class ReferralRewardStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalRewards = ReferralReward::count();
        $pendingRewards = ReferralReward::pending()->count();
        $appliedRewards = ReferralReward::applied()->count();
        $expiredRewards = ReferralReward::expired()->count();
        
        $totalAmount = ReferralReward::sum('amount');
        $pendingAmount = ReferralReward::pending()->sum('amount');
        $appliedAmount = ReferralReward::applied()->sum('amount');
        
        $referrerBonuses = ReferralReward::referrerBonus()->count();
        $referredDiscounts = ReferralReward::referredDiscount()->count();

        return [
            Stat::make(__('referrals.statistics.total_rewards'), $totalRewards)
                ->description(__('referrals.statistics.all_rewards'))
                ->descriptionIcon('heroicon-m-gift')
                ->color('primary'),

            Stat::make(__('referrals.status.pending'), $pendingRewards)
                ->description(__('referrals.statistics.pending_rewards'))
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make(__('referrals.status.applied'), $appliedRewards)
                ->description(__('referrals.statistics.applied_rewards'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make(__('referrals.status.expired'), $expiredRewards)
                ->description(__('referrals.statistics.expired_rewards'))
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),

            Stat::make(__('referrals.statistics.total_amount'), '€' . number_format($totalAmount, 2))
                ->description(__('referrals.statistics.all_rewards_amount'))
                ->descriptionIcon('heroicon-m-currency-euro')
                ->color('info'),

            Stat::make(__('referrals.statistics.pending_amount'), '€' . number_format($pendingAmount, 2))
                ->description(__('referrals.statistics.pending_rewards_amount'))
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make(__('referrals.statistics.applied_amount'), '€' . number_format($appliedAmount, 2))
                ->description(__('referrals.statistics.applied_rewards_amount'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make(__('referrals.types.referrer_bonus'), $referrerBonuses)
                ->description(__('referrals.statistics.referrer_bonuses_count'))
                ->descriptionIcon('heroicon-m-star')
                ->color('success'),

            Stat::make(__('referrals.types.referred_discount'), $referredDiscounts)
                ->description(__('referrals.statistics.referred_discounts_count'))
                ->descriptionIcon('heroicon-m-tag')
                ->color('info'),
        ];
    }
}
