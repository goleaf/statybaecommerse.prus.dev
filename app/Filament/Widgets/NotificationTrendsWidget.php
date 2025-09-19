<?php declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Notification;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Builder;

final class NotificationTrendsWidget extends ChartWidget
{
    protected static ?string $heading = 'Notification Trends (Last 30 Days)';

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $data = [];
        $labels = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->format('M d');
            $data[] = Notification::whereDate('created_at', $date)->count();
        }

        return [
            'datasets' => [
                [
                    'label' => __('notifications.charts.notification_count'),
                    'data' => $data,
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}

