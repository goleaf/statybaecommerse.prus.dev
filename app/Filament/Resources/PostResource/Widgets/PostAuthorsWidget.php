<?php

declare(strict_types=1);

namespace App\Filament\Resources\PostResource\Widgets;

use App\Models\Post;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

final /**
 * PostAuthorsWidget
 * 
 * Filament resource for admin panel management.
 */
class PostAuthorsWidget extends ChartWidget
{
    protected static ?string $heading = 'Posts by Author';

    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $authorData = Post::join('users', 'posts.user_id', '=', 'users.id')
            ->select('users.name', DB::raw('COUNT(posts.id) as count'))
            ->groupBy('users.id', 'users.name')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        $labels = $authorData->pluck('name')->toArray();
        $data = $authorData->pluck('count')->toArray();

        // Generate colors for each author
        $colors = [];
        for ($i = 0; $i < count($labels); $i++) {
            $colors[] = 'hsl(' . ($i * 360 / count($labels)) . ', 70%, 50%)';
        }

        return [
            'datasets' => [
                [
                    'label' => __('posts.authors.posts_count'),
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
                            return context.label + ": " + context.parsed.y + " " + "' . __('posts.authors.posts') . '";
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
