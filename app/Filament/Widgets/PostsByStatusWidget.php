<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Post;
use Filament\Widgets\ChartWidget;

final class PostsByStatusWidget extends ChartWidget
{
    protected ?string $heading = 'Posts by Status';

    protected function getData(): array
    {
        $statuses = ['draft', 'published', 'archived'];
        $data = [];
        $labels = [];

        foreach ($statuses as $status) {
            $count = Post::where('status', $status)->count();
            $data[] = $count;
            $labels[] = __('posts.status.'.$status);
        }

        return [
            'datasets' => [
                [
                    'label' => __('posts.widgets.posts_by_status'),
                    'data' => $data,
                    'backgroundColor' => [
                        'rgb(251, 191, 36)',  // warning - draft
                        'rgb(34, 197, 94)',  // success - published
                        'rgb(239, 68, 68)',  // danger - archived
                    ],
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
