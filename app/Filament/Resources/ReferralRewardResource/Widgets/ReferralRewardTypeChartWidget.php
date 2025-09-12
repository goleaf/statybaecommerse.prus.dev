<?php declare(strict_types=1);

namespace App\Filament\Resources\ReferralRewardResource\Widgets;

use App\Models\ReferralReward;
use Filament\Widgets\ChartWidget;

final class ReferralRewardTypeChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Reward Types Distribution';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $referrerBonuses = ReferralReward::referrerBonus()->count();
        $referredDiscounts = ReferralReward::referredDiscount()->count();

        return [
            'datasets' => [
                [
                    'data' => [$referrerBonuses, $referredDiscounts],
                    'backgroundColor' => [
                        'rgb(16, 185, 129)', // Green for referrer bonuses
                        'rgb(59, 130, 246)', // Blue for referred discounts
                    ],
                    'borderWidth' => 0,
                ],
            ],
            'labels' => [
                __('referrals.types.referrer_bonus'),
                __('referrals.types.referred_discount'),
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
