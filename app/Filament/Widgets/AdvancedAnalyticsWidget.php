<?php declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\AnalyticsEvent;
use App\Models\CampaignClick;
use App\Models\CampaignConversion;
use App\Models\CampaignView;
use App\Models\Order;
use App\Models\Product;
use App\Models\RecommendationAnalytics;
use App\Models\User;
use App\Models\UserBehavior;
use App\Models\VariantAnalytics;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class AdvancedAnalyticsWidget extends ChartWidget
{
    protected ?string $heading = 'Advanced Analytics Dashboard';
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 'full';
    protected ?string $maxHeight = '400px';

    public function getData(): array
    {
        $now = Carbon::now();
        $last30Days = $now->copy()->subDays(30);

        // Get data for the last 30 days
        $dates = [];
        $revenueData = [];
        $ordersData = [];
        $usersData = [];
        $pageViewsData = [];
        $conversionsData = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i);
            $dates[] = $date->format('M d');

            // Revenue
            $revenue = Order::where('status', '!=', 'cancelled')
                ->whereDate('created_at', $date)
                ->sum('total');
            $revenueData[] = $revenue;

            // Orders
            $orders = Order::whereDate('created_at', $date)->count();
            $ordersData[] = $orders;

            // New Users
            $users = User::whereDate('created_at', $date)->count();
            $usersData[] = $users;

            // Page Views
            $pageViews = AnalyticsEvent::where('event_type', 'page_view')
                ->whereDate('created_at', $date)
                ->count();
            $pageViewsData[] = $pageViews;

            // Conversions
            $conversions = CampaignConversion::whereDate('created_at', $date)
                ->sum('conversions_count');
            $conversionsData[] = $conversions;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Revenue (€)',
                    'data' => $revenueData,
                    'borderColor' => 'rgb(34, 197, 94)',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Orders',
                    'data' => $ordersData,
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'New Users',
                    'data' => $usersData,
                    'borderColor' => 'rgb(168, 85, 247)',
                    'backgroundColor' => 'rgba(168, 85, 247, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Page Views',
                    'data' => $pageViewsData,
                    'borderColor' => 'rgb(245, 158, 11)',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Conversions',
                    'data' => $conversionsData,
                    'borderColor' => 'rgb(239, 68, 68)',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $dates,
        ];
    }

    public function getType(): string
    {
        return 'line';
    }

    public function getOptions(): array
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
                    'text' => '30-Day Analytics Overview',
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'grid' => [
                        'color' => 'rgba(0, 0, 0, 0.1)',
                    ],
                ],
                'x' => [
                    'grid' => [
                        'color' => 'rgba(0, 0, 0, 0.1)',
                    ],
                ],
            ],
            'interaction' => [
                'intersect' => false,
                'mode' => 'index',
            ],
        ];
    }

    public function getMaxHeight(): ?string
    {
        return $this->maxHeight;
    }
}
