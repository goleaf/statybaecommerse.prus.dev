<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralRewardResource\Widgets;

use App\Models\ReferralReward;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

final class ReferralRewardStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalRewards = ReferralReward::count();
        $pendingRewards = ReferralReward::where('status', 'pending')->count();
        $approvedRewards = ReferralReward::where('status', 'approved')->count();
        $totalValue = ReferralReward::where('status', 'paid')->sum('amount');

        return [
            Stat::make(__('referral_rewards.stats.total_rewards'), $totalRewards)
                ->description(__('referral_rewards.stats.total_rewards_description'))
                ->descriptionIcon('heroicon-m-gift')
                ->color('primary'),

            Stat::make(__('referral_rewards.stats.pending_rewards'), $pendingRewards)
                ->description(__('referral_rewards.stats.pending_rewards_description'))
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make(__('referral_rewards.stats.approved_rewards'), $approvedRewards)
                ->description(__('referral_rewards.stats.approved_rewards_description'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make(__('referral_rewards.stats.total_paid'), '€'.number_format($totalValue, 2))
                ->description(__('referral_rewards.stats.total_paid_description'))
                ->descriptionIcon('heroicon-m-currency-euro')
                ->color('info'),
        ];
    }
}
