<?php declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\CampaignConversion;
use App\Models\DiscountRedemption;
use App\Models\Order;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class RevenueAnalyticsWidget extends ChartWidget
{
    protected string $view = 'filament.widgets.revenue-analytics-widget';

    protected ?string $heading = 'Revenue Analytics';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 2;

    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $now = Carbon::now();
        $last12Months = $now->copy()->subMonths(12);

        // Get monthly revenue data
        $months = [];
        $revenueData = [];
        $discountSavingsData = [];
        $netRevenueData = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $months[] = $date->format('M Y');

            // Monthly Revenue
            $revenue = Order::where('status', '!=', 'cancelled')
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('total_amount');
            $revenueData[] = $revenue;

            // Monthly Discount Savings
            $discountSavings = DiscountRedemption::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('discount_amount');
            $discountSavingsData[] = $discountSavings;

            // Net Revenue (Revenue - Discount Savings)
            $netRevenueData[] = $revenue - $discountSavings;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Gross Revenue (€)',
                    'data' => $revenueData,
                    'borderColor' => 'rgb(34, 197, 94)',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.2)',
                    'fill' => true,
                ],
                [
                    'label' => 'Discount Savings (€)',
                    'data' => $discountSavingsData,
                    'borderColor' => 'rgb(245, 158, 11)',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.2)',
                    'fill' => true,
                ],
                [
                    'label' => 'Net Revenue (€)',
                    'data' => $netRevenueData,
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    'fill' => true,
                ],
            ],
            'labels' => $months,
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
                    'position' => 'top',
                ],
                'title' => [
                    'display' => true,
                    'text' => '12-Month Revenue Analysis',
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'grid' => [
                        'color' => 'rgba(0, 0, 0, 0.1)',
                    ],
                ],
                'x' => [
                    'grid' => [
                        'color' => 'rgba(0, 0, 0, 0.1)',
                    ],
                ],
            ],
        ];
    }
}
