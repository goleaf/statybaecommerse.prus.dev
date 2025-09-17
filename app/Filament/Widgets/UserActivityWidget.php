<?php declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\AnalyticsEvent;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\User;
use App\Models\UserBehavior;
use App\Models\WishlistItem;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class UserActivityWidget extends ChartWidget
{
    protected string $view = 'filament.widgets.user-activity-widget';

    protected ?string $heading = 'User Activity Analytics';

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 2;

    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $now = Carbon::now();
        $last7Days = $now->copy()->subDays(7);

        // Get daily user activity data
        $dates = [];
        $newUsersData = [];
        $activeUsersData = [];
        $pageViewsData = [];
        $cartAddsData = [];
        $wishlistAddsData = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i);
            $dates[] = $date->format('M d');

            // New Users
            $newUsers = User::whereDate('created_at', $date)->count();
            $newUsersData[] = $newUsers;

            // Active Users (users who logged in)
            $activeUsers = User::whereDate('last_login_at', $date)->count();
            $activeUsersData[] = $activeUsers;

            // Page Views
            $pageViews = AnalyticsEvent::where('event_type', 'page_view')
                ->whereDate('created_at', $date)
                ->count();
            $pageViewsData[] = $pageViews;

            // Cart Adds
            $cartAdds = AnalyticsEvent::where('event_type', 'add_to_cart')
                ->whereDate('created_at', $date)
                ->count();
            $cartAddsData[] = $cartAdds;

            // Wishlist Adds
            $wishlistAdds = WishlistItem::whereDate('created_at', $date)->count();
            $wishlistAddsData[] = $wishlistAdds;
        }

        return [
            'datasets' => [
                [
                    'label' => 'New Users',
                    'data' => $newUsersData,
                    'borderColor' => 'rgb(34, 197, 94)',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.2)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Active Users',
                    'data' => $activeUsersData,
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Page Views',
                    'data' => $pageViewsData,
                    'borderColor' => 'rgb(245, 158, 11)',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.2)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Cart Adds',
                    'data' => $cartAddsData,
                    'borderColor' => 'rgb(239, 68, 68)',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.2)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Wishlist Adds',
                    'data' => $wishlistAddsData,
                    'borderColor' => 'rgb(168, 85, 247)',
                    'backgroundColor' => 'rgba(168, 85, 247, 0.2)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $dates,
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
                    'position' => 'top',
                ],
                'title' => [
                    'display' => true,
                    'text' => '7-Day User Activity Overview',
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
}
