<?php

declare (strict_types=1);
namespace App\Filament\Widgets;

use App\Models\Address;
use App\Enums\AddressType;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
/**
 * AddressTypeChartWidget
 * 
 * Filament v4 widget for AddressTypeChartWidget dashboard display with real-time data and interactive features.
 * 
 * @property int|null $sort
 */
final class AddressTypeChartWidget extends ChartWidget
{
    protected static ?int $sort = 2;
    /**
     * Handle getHeading functionality with proper error handling.
     * @return string
     */
    public function getHeading(): string
    {
        return 'Address Types Distribution';
    }
    /**
     * Handle getData functionality with proper error handling.
     * @return array
     */
    protected function getData(): array
    {
        $addressTypes = Address::select('type', DB::raw('count(*) as count'))->groupBy('type')->get()->pluck('count', 'type')->toArray();
        $labels = [];
        $data = [];
        $colors = [];
        foreach (AddressType::cases() as $type) {
            $labels[] = $type->label();
            $data[] = $addressTypes[$type->value] ?? 0;
            $colors[] = $this->getColorForType($type);
        }
        return ['datasets' => [['label' => __('translations.addresses_count'), 'data' => $data, 'backgroundColor' => $colors, 'borderColor' => $colors, 'borderWidth' => 1]], 'labels' => $labels];
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
     * Handle getOptions functionality with proper error handling.
     * @return array
     */
    protected function getOptions(): array
    {
        return ['responsive' => true, 'maintainAspectRatio' => false, 'plugins' => ['legend' => ['position' => 'bottom'], 'tooltip' => ['callbacks' => ['label' => "function(context) {\n                            const label = context.label || '';\n                            const value = context.parsed;\n                            const total = context.dataset.data.reduce((a, b) => a + b, 0);\n                            const percentage = total > 0 ? Math.round((value / total) * 100) : 0;\n                            return label + ': ' + value + ' (' + percentage + '%)';\n                        }"]]]];
    }
    /**
     * Handle getColorForType functionality with proper error handling.
     * @param AddressType $type
     * @return string
     */
    private function getColorForType(AddressType $type): string
    {
        return match ($type) {
            AddressType::SHIPPING => '#3B82F6',
            // blue
            AddressType::BILLING => '#10B981',
            // green
            AddressType::HOME => '#8B5CF6',
            // purple
            AddressType::WORK => '#F59E0B',
            // orange
            AddressType::OTHER => '#6B7280',
        };
    }
}