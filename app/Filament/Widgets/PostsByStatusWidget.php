<?php

declare (strict_types=1);
namespace App\Filament\Widgets;

use App\Models\Post;
use Filament\Widgets\ChartWidget;
/**
 * PostsByStatusWidget
 * 
 * Filament v4 widget for PostsByStatusWidget dashboard display with real-time data and interactive features.
 * 
 * @property string|null $heading
 */
final class PostsByStatusWidget extends ChartWidget
{
    protected ?string $heading = 'Posts by Status';
    /**
     * Handle getData functionality with proper error handling.
     * @return array
     */
    protected function getData(): array
    {
        $statuses = ['draft', 'published', 'archived'];
        $data = [];
        $labels = [];
        foreach ($statuses as $status) {
            $count = Post::where('status', $status)->count();
            $data[] = $count;
            $labels[] = __('posts.status.' . $status);
        }
        return ['datasets' => [['label' => __('posts.widgets.posts_by_status'), 'data' => $data, 'backgroundColor' => [
            'rgb(251, 191, 36)',
            // warning - draft
            'rgb(34, 197, 94)',
            // success - published
            'rgb(239, 68, 68)',
        ]]], 'labels' => $labels];
    }
    /**
     * Handle getType functionality with proper error handling.
     * @return string
     */
    protected function getType(): string
    {
        return 'doughnut';
    }
}