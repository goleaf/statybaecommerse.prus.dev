<?php

declare(strict_types=1);

namespace App\Filament\Resources\CountryResource\Widgets;

use App\Models\Country;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Builder;

final /**
 * CountriesWithVatWidget
 * 
 * Filament resource for admin panel management.
 */
class CountriesWithVatWidget extends ChartWidget
{
    protected static ?string $heading = 'admin.countries.widgets.countries_with_vat';

    protected static ?int $sort = 4;

    protected function getData(): array
    {
        $vatRates = Country::selectRaw('
                CASE 
                    WHEN vat_rate IS NULL THEN "No VAT"
                    WHEN vat_rate = 0 THEN "0%"
                    WHEN vat_rate <= 10 THEN "1-10%"
                    WHEN vat_rate <= 20 THEN "11-20%"
                    WHEN vat_rate <= 30 THEN "21-30%"
                    ELSE "30%+"
                END as vat_range,
                COUNT(*) as count
            ')
            ->groupBy('vat_range')
            ->orderByRaw('
                CASE 
                    WHEN vat_range = "No VAT" THEN 1
                    WHEN vat_range = "0%" THEN 2
                    WHEN vat_range = "1-10%" THEN 3
                    WHEN vat_range = "11-20%" THEN 4
                    WHEN vat_range = "21-30%" THEN 5
                    ELSE 6
                END
            ')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => __('admin.countries.widgets.countries_count'),
                    'data' => $vatRates->pluck('count')->toArray(),
                    'backgroundColor' => [
                        '#6B7280', // Gray for No VAT
                        '#10B981', // Green for 0%
                        '#3B82F6', // Blue for 1-10%
                        '#F59E0B', // Yellow for 11-20%
                        '#EF4444', // Red for 21-30%
                        '#8B5CF6', // Purple for 30%+
                    ],
                    'borderColor' => '#ffffff',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $vatRates->pluck('vat_range')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) {
                            return context.label + ": " + context.parsed.y + " " + "' . __('admin.countries.widgets.countries') . '";
                        }',
                    ],
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
            ],
        ];
    }
}
