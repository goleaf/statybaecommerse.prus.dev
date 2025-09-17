<?php declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Inventory;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\VariantAnalytics;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ProductPerformanceWidget extends ChartWidget
{
    protected string $view = 'filament.widgets.product-performance-widget';

    protected ?string $heading = 'Product Performance Analytics';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 2;

    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        // Get top 10 selling products
        $topProducts = OrderItem::select('product_id', DB::raw('SUM(quantity) as total_sold'))
            ->with('product:id,name')
            ->groupBy('product_id')
            ->orderBy('total_sold', 'desc')
            ->limit(10)
            ->get();

        $productNames = [];
        $salesData = [];
        $revenueData = [];
        $ratingData = [];

        foreach ($topProducts as $item) {
            if ($item->product) {
                $productNames[] = \Str::limit($item->product->name, 20);
                $salesData[] = $item->total_sold;

                // Get revenue for this product
                $revenue = OrderItem::where('product_id', $item->product_id)
                    ->sum(DB::raw('quantity * price'));
                $revenueData[] = $revenue;

                // Get average rating
                $avgRating = Review::where('product_id', $item->product_id)
                    ->where('is_approved', true)
                    ->avg('rating') ?? 0;
                $ratingData[] = $avgRating;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Units Sold',
                    'data' => $salesData,
                    'borderColor' => 'rgb(34, 197, 94)',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.2)',
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Revenue (€)',
                    'data' => $revenueData,
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    'yAxisID' => 'y1',
                ],
                [
                    'label' => 'Avg Rating',
                    'data' => $ratingData,
                    'borderColor' => 'rgb(245, 158, 11)',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.2)',
                    'yAxisID' => 'y2',
                ],
            ],
            'labels' => $productNames,
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
                    'text' => 'Top 10 Products Performance',
                ],
            ],
            'scales' => [
                'y' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Units Sold',
                    ],
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Revenue (€)',
                    ],
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ],
                'y2' => [
                    'type' => 'linear',
                    'display' => false,
                    'beginAtZero' => true,
                    'max' => 5,
                ],
            ],
        ];
    }
}

