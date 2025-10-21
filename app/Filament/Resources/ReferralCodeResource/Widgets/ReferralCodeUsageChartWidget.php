<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralCodeResource\Widgets;

use App\Models\ReferralCodeUsageLog;
use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use Filament\Widgets\ChartWidget;

final class ReferralCodeUsageChartWidget extends ChartWidget
{
    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = null;

    public function getHeading(): ?string
    {
        return __('referral_codes.charts.usage_over_time');
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $endDate = Carbon::now();
        $startDate = $endDate->copy()->subDays(6)->startOfDay();

        $usageByDate = ReferralCodeUsageLog::query()
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date');

        $labels = [];
        $data = [];

        $period = new DatePeriod($startDate, new DateInterval('P1D'), $endDate->copy()->addDay());

        foreach ($period as $date) {
            $label = Carbon::instance($date)->format('Y-m-d');
            $labels[] = $label;
            $data[] = (int) ($usageByDate[$label] ?? 0);
        }

        return [
            'datasets' => [
                [
                    'label'           => __('referral_codes.charts.usage_label'),
                    'data'            => $data,
                    'backgroundColor' => '#6366F1',
                    'borderColor'     => '#4338CA',
                    'tension'         => 0.4,
                    'fill'            => false,
                ],
            ],
            'labels' => $labels,
        ];
    }
}
