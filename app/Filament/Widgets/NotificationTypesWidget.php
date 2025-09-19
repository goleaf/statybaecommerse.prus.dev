<?php declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Notification;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Builder;

final class NotificationTypesWidget extends ChartWidget
{
    protected static ?string $heading = 'Notification Types Distribution';

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $types = ['order', 'product', 'user', 'system', 'payment', 'shipping', 'review', 'promotion', 'newsletter', 'support'];
        $data = [];
        $labels = [];
        $colors = [
            'rgb(59, 130, 246)',  // blue
            'rgb(34, 197, 94)',  // green
            'rgb(168, 85, 247)',  // purple
            'rgb(249, 115, 22)',  // orange
            'rgb(234, 179, 8)',  // yellow
            'rgb(99, 102, 241)',  // indigo
            'rgb(236, 72, 153)',  // pink
            'rgb(239, 68, 68)',  // red
            'rgb(6, 182, 212)',  // cyan
            'rgb(107, 114, 128)',  // gray
        ];

        foreach ($types as $index => $type) {
            $count = Notification::where('type', $type)->count();
            if ($count > 0) {
                $data[] = $count;
                $labels[] = __('notifications.types.' . $type);
            }
        }

        return [
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => array_slice($colors, 0, count($data)),
                    'borderWidth' => 0,
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

