<?php declare(strict_types=1);

namespace App\Filament\Resources\SeoDataResource\Widgets;

use App\Models\SeoData;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

final class SeoDataByTypeWidget extends ChartWidget
{
    protected static ?string $heading = 'SEO Data by Object Type';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $data = SeoData::selectRaw('
            CASE 
                WHEN seoable_type = ? THEN "Products"
                WHEN seoable_type = ? THEN "Categories"
                WHEN seoable_type = ? THEN "Brands"
                ELSE "Other"
            END as object_type,
            COUNT(*) as count
        ', [Product::class, Category::class, Brand::class])
        ->groupBy('object_type')
        ->orderBy('count', 'desc')
        ->get();

        $labels = $data->pluck('object_type')->toArray();
        $counts = $data->pluck('count')->toArray();

        return [
            'datasets' => [
                [
                    'label' => __('admin.seo_data.widgets.count'),
                    'data' => $counts,
                    'backgroundColor' => [
                        '#10b981', // green for products
                        '#3b82f6', // blue for categories
                        '#f59e0b', // yellow for brands
                        '#6b7280', // gray for other
                    ],
                    'borderColor' => [
                        '#059669',
                        '#2563eb',
                        '#d97706',
                        '#4b5563',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels,
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
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => "function(context) {
                            return context.label + ': ' + context.parsed.y + ' ' + (context.parsed.y === 1 ? 'entry' : 'entries');
                        }",
                    ],
                ],
            ],
        ];
    }
}
