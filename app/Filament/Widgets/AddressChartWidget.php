<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Address;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

/**
 * AddressChartWidget
 *
 * Widget displaying address statistics charts for the admin dashboard
 */
final class AddressChartWidget extends ChartWidget
{
    protected ?string $heading = 'Address Statistics';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    /**
     * Get chart data
     */
    protected function getData(): array
    {
        $data = $this->getAddressesPerMonth();

        return [
            'datasets' => [
                [
                    'label' => __('translations.addresses_created'),
                    'data' => $data['addresses'],
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgba(59, 130, 246, 1)',
                    'borderWidth' => 2,
                ],
                [
                    'label' => __('translations.active_addresses'),
                    'data' => $data['active_addresses'],
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'borderColor' => 'rgba(34, 197, 94, 1)',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $data['labels'],
        ];
    }

    /**
     * Get chart type
     */
    protected function getType(): string
    {
        return 'line';
    }

    /**
     * Get addresses per month data
     */
    private function getAddressesPerMonth(): array
    {
        $months = [];
        $addresses = [];
        $activeAddresses = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = $date->format('M Y');

            $addresses[] = Address::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();

            $activeAddresses[] = Address::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->where('is_active', true)
                ->count();
        }

        return [
            'labels' => $months,
            'addresses' => $addresses,
            'active_addresses' => $activeAddresses,
        ];
    }
}
