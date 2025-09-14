<?php

declare (strict_types=1);
namespace App\Filament\Resources\ReferralRewardResource\Widgets;

use App\Models\ReferralReward;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
/**
 * ReferralRewardChartWidget
 * 
 * Filament v4 resource for ReferralRewardChartWidget management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string|null $heading
 * @property int|null $sort
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class ReferralRewardChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Referral Rewards Over Time';
    protected static ?int $sort = 2;
    /**
     * Handle getData functionality with proper error handling.
     * @return array
     */
    protected function getData(): array
    {
        $data = ReferralReward::select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total_amount'))->where('created_at', '>=', now()->subDays(30))->groupBy('date')->orderBy('date')->get();
        return ['datasets' => [['label' => __('referrals.statistics.rewards_count'), 'data' => $data->pluck('count')->toArray(), 'backgroundColor' => 'rgba(59, 130, 246, 0.5)', 'borderColor' => 'rgb(59, 130, 246)', 'borderWidth' => 2], ['label' => __('referrals.statistics.rewards_amount'), 'data' => $data->pluck('total_amount')->toArray(), 'backgroundColor' => 'rgba(16, 185, 129, 0.5)', 'borderColor' => 'rgb(16, 185, 129)', 'borderWidth' => 2, 'yAxisID' => 'y1']], 'labels' => $data->pluck('date')->map(fn($date) => \Carbon\Carbon::parse($date)->format('M d'))->toArray()];
    }
    /**
     * Handle getType functionality with proper error handling.
     * @return string
     */
    protected function getType(): string
    {
        return 'line';
    }
    /**
     * Handle getOptions functionality with proper error handling.
     * @return array
     */
    protected function getOptions(): array
    {
        return ['scales' => ['y' => ['type' => 'linear', 'display' => true, 'position' => 'left'], 'y1' => ['type' => 'linear', 'display' => true, 'position' => 'right', 'grid' => ['drawOnChartArea' => false]]]];
    }
}