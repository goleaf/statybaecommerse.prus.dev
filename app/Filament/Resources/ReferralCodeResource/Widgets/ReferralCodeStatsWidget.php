<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralCodeResource\Widgets;

use App\Models\ReferralCode;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

final class ReferralCodeStatsWidget extends StatsOverviewWidget
{
    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $query = ReferralCode::query()->withoutGlobalScopes();

        $totalCodes = (clone $query)->count();
        $activeCodes = (clone $query)->where('is_active', true)->count();
        $expiredCodes = (clone $query)
            ->where(function (Builder $builder): void {
                $builder
                    ->where('is_active', false)
                    ->orWhere(function (Builder $inner): void {
                        $inner
                            ->whereNotNull('expires_at')
                            ->where('expires_at', '<=', now());
                    });
            })
            ->count();
        $totalUsage = (clone $query)->sum('usage_count');

        return [
            Stat::make(__('referral_codes.stats.total_codes'), (string) $totalCodes)
                ->description(__('referral_codes.stats.total_codes_description')),
            Stat::make(__('referral_codes.stats.active_codes'), (string) $activeCodes)
                ->description(__('referral_codes.stats.active_codes_description')),
            Stat::make(__('referral_codes.stats.expired_codes'), (string) $expiredCodes)
                ->description(__('referral_codes.stats.expired_codes_description')),
            Stat::make(__('referral_codes.stats.total_usage'), (string) $totalUsage)
                ->description(__('referral_codes.stats.total_usage_description')),
        ];
    }
}
