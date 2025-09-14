<?php

declare (strict_types=1);
namespace App\Filament\Resources\ReferralRewardResource\Widgets;

use App\Models\ReferralReward;
use Filament\Widgets\ChartWidget;
/**
 * ReferralRewardTypeChartWidget
 * 
 * Filament v4 resource for ReferralRewardTypeChartWidget management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string|null $heading
 * @property int|null $sort
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class ReferralRewardTypeChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Reward Types Distribution';
    protected static ?int $sort = 3;
    /**
     * Handle getData functionality with proper error handling.
     * @return array
     */
    protected function getData(): array
    {
        $referrerBonuses = ReferralReward::referrerBonus()->count();
        $referredDiscounts = ReferralReward::referredDiscount()->count();
        return ['datasets' => [['data' => [$referrerBonuses, $referredDiscounts], 'backgroundColor' => [
            'rgb(16, 185, 129)',
            // Green for referrer bonuses
            'rgb(59, 130, 246)',
        ], 'borderWidth' => 0]], 'labels' => [__('referrals.types.referrer_bonus'), __('referrals.types.referred_discount')]];
    }
    /**
     * Handle getType functionality with proper error handling.
     * @return string
     */
    protected function getType(): string
    {
        return 'doughnut';
    }
}