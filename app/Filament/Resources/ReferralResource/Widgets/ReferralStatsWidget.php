<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralResource\Widgets;

use App\Models\Referral;
use App\Models\ReferralReward;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

final class ReferralStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalReferrals = Referral::count();
        $completedReferrals = Referral::completed()->count();
        $pendingReferrals = Referral::where('status', 'pending')->count();
        $expiredReferrals = Referral::expired()->count();

        $totalRewards = ReferralReward::sum('amount');
        $pendingRewards = ReferralReward::pending()->sum('amount');
        $appliedRewards = ReferralReward::applied()->sum('amount');

        $conversionRate = $totalReferrals > 0 ? round(($completedReferrals / $totalReferrals) * 100, 1) : 0;

        $thisMonthReferrals = Referral::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $lastMonthReferrals = Referral::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();

        $monthlyGrowth = $lastMonthReferrals > 0
            ? round((($thisMonthReferrals - $lastMonthReferrals) / $lastMonthReferrals) * 100, 1)
            : 0;

        // Additional statistics
        $activeReferrers = Referral::distinct('referrer_id')->count();
        $averageRewardPerReferral = $completedReferrals > 0 ? round($totalRewards / $completedReferrals, 2) : 0;
        $referralsWithRewards = Referral::withRewards()->count();
        $referralRewardRate = $totalReferrals > 0 ? round(($referralsWithRewards / $totalReferrals) * 100, 1) : 0;

        $thisWeekReferrals = Referral::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $lastWeekReferrals = Referral::whereBetween('created_at', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()])->count();
        $weeklyGrowth = $lastWeekReferrals > 0
            ? round((($thisWeekReferrals - $lastWeekReferrals) / $lastWeekReferrals) * 100, 1)
            : 0;

        return [
            Stat::make(__('referrals.total_referrals'), $totalReferrals)
                ->description(__('referrals.all_time'))
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make(__('referrals.completed_referrals'), $completedReferrals)
                ->description(__('referrals.conversion_rate').': '.$conversionRate.'%')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make(__('referrals.pending_referrals'), $pendingReferrals)
                ->description(__('referrals.awaiting_completion'))
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make(__('referrals.total_rewards'), '€'.number_format($totalRewards, 2))
                ->description(__('referrals.all_time_rewards'))
                ->descriptionIcon('heroicon-m-currency-euro')
                ->color('success'),

            Stat::make(__('referrals.monthly_referrals'), $thisMonthReferrals)
                ->description($monthlyGrowth >= 0 ? '+'.$monthlyGrowth.'% '.__('referrals.vs_last_month') : $monthlyGrowth.'% '.__('referrals.vs_last_month'))
                ->descriptionIcon($monthlyGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($monthlyGrowth >= 0 ? 'success' : 'danger'),

            Stat::make(__('referrals.pending_rewards'), '€'.number_format($pendingRewards, 2))
                ->description(__('referrals.awaiting_application'))
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make(__('referrals.active_referrers'), $activeReferrers)
                ->description(__('referrals.unique_referrers'))
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),

            Stat::make(__('referrals.average_reward'), '€'.number_format($averageRewardPerReferral, 2))
                ->description(__('referrals.per_completed_referral'))
                ->descriptionIcon('heroicon-m-calculator')
                ->color('success'),

            Stat::make(__('referrals.weekly_referrals'), $thisWeekReferrals)
                ->description($weeklyGrowth >= 0 ? '+'.$weeklyGrowth.'% '.__('referrals.vs_last_week') : $weeklyGrowth.'% '.__('referrals.vs_last_week'))
                ->descriptionIcon($weeklyGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($weeklyGrowth >= 0 ? 'success' : 'danger'),

            Stat::make(__('referrals.reward_rate'), $referralRewardRate.'%')
                ->description(__('referrals.referrals_with_rewards'))
                ->descriptionIcon('heroicon-m-gift')
                ->color('success'),
        ];
    }

    protected function getColumns(): int
    {
        return 3;
    }
}
