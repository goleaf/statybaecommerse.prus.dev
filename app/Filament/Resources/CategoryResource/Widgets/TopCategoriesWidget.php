<?php

declare(strict_types=1);

namespace App\Filament\Resources\CategoryResource\Widgets;

use App\Models\Category;
use Filament\Widgets\ChartWidget;

final /**
 * TopCategoriesWidget
 * 
 * Filament resource for admin panel management.
 */
class TopCategoriesWidget extends ChartWidget
{
    protected static ?string $heading = 'Top Categories by Product Count';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $topCategories = Category::withCount('products')
            ->orderBy('products_count', 'desc')
            ->limit(10)
            ->get();

        $labels = $topCategories->pluck('name')->toArray();
        $data = $topCategories->pluck('products_count')->toArray();

        // Generate colors for each category
        $colors = [];
        for ($i = 0; $i < count($labels); $i++) {
            $colors[] = 'hsl(' . ($i * 36) . ', 70%, 50%)'; // 36 degrees apart for good color distribution
        }

        return [
            'datasets' => [
                [
                    'label' => 'Products Count',
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderWidth' => 2,
                    'borderColor' => '#ffffff',
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
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) {
                            return context.label + ": " + context.parsed.y + " products";
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
