<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReferralResource\Widgets;

use App\Models\Referral;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

final class ReferralTrendsWidget extends ChartWidget
{
    protected static ?string $heading = null;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $maxHeight = '300px';

    public function getHeading(): string
    {
        return __('referrals.referral_trends');
    }

    protected function getData(): array
    {
        $data = Referral::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as total'),
            DB::raw('SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed'),
            DB::raw('SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending'),
            DB::raw('SUM(CASE WHEN status = "expired" THEN 1 ELSE 0 END) as expired')
        )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => __('referrals.total_referrals'),
                    'data' => $data->pluck('total')->toArray(),
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                ],
                [
                    'label' => __('referrals.completed_referrals'),
                    'data' => $data->pluck('completed')->toArray(),
                    'borderColor' => 'rgb(34, 197, 94)',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'fill' => true,
                ],
                [
                    'label' => __('referrals.pending_referrals'),
                    'data' => $data->pluck('pending')->toArray(),
                    'borderColor' => 'rgb(251, 191, 36)',
                    'backgroundColor' => 'rgba(251, 191, 36, 0.1)',
                    'fill' => true,
                ],
                [
                    'label' => __('referrals.expired_referrals'),
                    'data' => $data->pluck('expired')->toArray(),
                    'borderColor' => 'rgb(239, 68, 68)',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $data->pluck('date')->map(fn ($date) => now()->parse($date)->format('M d'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                ],
            ],
            'scales' => [
                'x' => [
                    'display' => true,
                    'title' => [
                        'display' => true,
                        'text' => __('referrals.date'),
                    ],
                ],
                'y' => [
                    'display' => true,
                    'title' => [
                        'display' => true,
                        'text' => __('referrals.count'),
                    ],
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}
