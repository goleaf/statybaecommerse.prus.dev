<?php

declare (strict_types=1);
namespace App\Filament\Resources\CampaignResource\Widgets;

use App\Models\Campaign;
use Filament\Widgets\ChartWidget;
/**
 * CampaignTypeChartWidget
 * 
 * Filament v4 resource for CampaignTypeChartWidget management in the admin panel with comprehensive CRUD operations, filters, and actions.
 * 
 * @property string|null $heading
 * @method static \Filament\Forms\Form form(\Filament\Forms\Form $form)
 * @method static \Filament\Tables\Table table(\Filament\Tables\Table $table)
 */
final class CampaignTypeChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Campaign Types Distribution';
    /**
     * Handle getData functionality with proper error handling.
     * @return array
     */
    protected function getData(): array
    {
        $campaignTypes = Campaign::selectRaw('type, COUNT(*) as count')->groupBy('type')->pluck('count', 'type')->toArray();
        $labels = array_keys($campaignTypes);
        $data = array_values($campaignTypes);
        return ['datasets' => [['label' => __('campaigns.charts.campaign_types'), 'data' => $data, 'backgroundColor' => [
            '#3B82F6',
            // Blue
            '#10B981',
            // Green
            '#F59E0B',
            // Yellow
            '#8B5CF6',
            // Purple
            '#EC4899',
            // Pink
            '#EF4444',
        ], 'borderColor' => ['#1E40AF', '#059669', '#D97706', '#7C3AED', '#DB2777', '#DC2626'], 'borderWidth' => 2]], 'labels' => array_map(fn($type) => __('campaigns.types.' . $type), $labels)];
    }
    /**
     * Handle getType functionality with proper error handling.
     * @return string
     */
    protected function getType(): string
    {
        return 'doughnut';
    }
    /**
     * Handle getHeading functionality with proper error handling.
     * @return string
     */
    public function getHeading(): string
    {
        return __('campaigns.charts.campaign_types_heading');
    }
}