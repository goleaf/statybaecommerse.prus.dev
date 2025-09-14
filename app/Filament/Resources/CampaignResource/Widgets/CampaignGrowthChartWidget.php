<?php

declare (strict_types=1);
namespace App\Filament\Resources\CampaignResource\Widgets;

use App\Models\Campaign;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
/**
 * CampaignGrowthChartWidget
 * 
 * Filament v4 resource for CampaignGrowthChartWidget management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string|null $heading
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class CampaignGrowthChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Campaign Growth Over Time';
    /**
     * Handle getData functionality with proper error handling.
     * @return array
     */
    protected function getData(): array
    {
        $startDate = Carbon::now()->subMonths(12);
        $endDate = Carbon::now();
        $campaigns = Campaign::selectRaw('DATE(created_at) as date, COUNT(*) as count')->whereBetween('created_at', [$startDate, $endDate])->groupBy('date')->orderBy('date')->get();
        $labels = [];
        $data = [];
        // Fill in missing dates with 0
        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            $dateString = $currentDate->format('Y-m-d');
            $labels[] = $currentDate->format('M d');
            $campaign = $campaigns->firstWhere('date', $dateString);
            $data[] = $campaign ? $campaign->count : 0;
            $currentDate->addDay();
        }
        return ['datasets' => [['label' => __('campaigns.charts.campaigns_created'), 'data' => $data, 'backgroundColor' => 'rgba(59, 130, 246, 0.1)', 'borderColor' => '#3B82F6', 'borderWidth' => 2, 'fill' => true, 'tension' => 0.4]], 'labels' => $labels];
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
     * Handle getHeading functionality with proper error handling.
     * @return string
     */
    public function getHeading(): string
    {
        return __('campaigns.charts.campaign_growth_heading');
    }
}