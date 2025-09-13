<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Address;
use App\Enums\AddressType;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

final class AddressTypeChartWidget extends ChartWidget
{
    protected static ?int $sort = 2;

    public function getHeading(): string
    {
        return 'Address Types Distribution';
    }

    protected function getData(): array
    {
        $addressTypes = Address::select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->get()
            ->pluck('count', 'type')
            ->toArray();

        $labels = [];
        $data = [];
        $colors = [];

        foreach (AddressType::cases() as $type) {
            $labels[] = $type->label();
            $data[] = $addressTypes[$type->value] ?? 0;
            $colors[] = $this->getColorForType($type);
        }

        return [
            'datasets' => [
                [
                    'label' => __('translations.addresses_count'),
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderColor' => $colors,
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => "function(context) {
                            const label = context.label || '';
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                            return label + ': ' + value + ' (' + percentage + '%)';
                        }",
                    ],
                ],
            ],
        ];
    }

    private function getColorForType(AddressType $type): string
    {
        return match ($type) {
            AddressType::SHIPPING => '#3B82F6', // blue
            AddressType::BILLING => '#10B981',  // green
            AddressType::HOME => '#8B5CF6',     // purple
            AddressType::WORK => '#F59E0B',     // orange
            AddressType::OTHER => '#6B7280',    // gray
        };
    }
}
