<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\AnalyticsEvent;
use App\Models\CampaignClick;
use App\Models\CampaignConversion;
use App\Models\CampaignView;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\RecommendationAnalytics;
use App\Models\Review;
use App\Models\User;
use App\Models\UserBehavior;
use App\Models\WishlistItem;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class ComprehensiveAnalyticsWidget extends ChartWidget
{
    protected ?string $heading = 'Comprehensive Analytics Dashboard';

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '500px';

    public function getData(): array
    {
        $now = Carbon::now();
        $last30Days = $now->copy()->subDays(30);

        // Get data for the last 30 days
        $dates = [];
        $revenueData = [];
        $ordersData = [];
        $usersData = [];
        $productsData = [];
        $variantsData = [];
        $reviewsData = [];
        $wishlistData = [];
        $pageViewsData = [];
        $searchesData = [];
        $cartAddsData = [];
        $campaignViewsData = [];
        $campaignClicksData = [];
        $conversionsData = [];
        $userBehaviorsData = [];
        $recommendationsData = [];

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

            // New Products
            $products = Product::whereDate('created_at', $date)->count();
            $productsData[] = $products;

            // New Variants
            $variants = ProductVariant::whereDate('created_at', $date)->count();
            $variantsData[] = $variants;

            // New Reviews
            $reviews = Review::whereDate('created_at', $date)->count();
            $reviewsData[] = $reviews;

            // Wishlist Items
            $wishlist = WishlistItem::whereDate('created_at', $date)->count();
            $wishlistData[] = $wishlist;

            // Page Views
            $pageViews = AnalyticsEvent::where('event_type', 'page_view')
                ->whereDate('created_at', $date)
                ->count();
            $pageViewsData[] = $pageViews;

            // Searches
            $searches = AnalyticsEvent::where('event_type', 'search')
                ->whereDate('created_at', $date)
                ->count();
            $searchesData[] = $searches;

            // Cart Adds
            $cartAdds = AnalyticsEvent::where('event_type', 'add_to_cart')
                ->whereDate('created_at', $date)
                ->count();
            $cartAddsData[] = $cartAdds;

            // Campaign Views
            $campaignViews = CampaignView::whereDate('created_at', $date)
                ->sum('views_count');
            $campaignViewsData[] = $campaignViews;

            // Campaign Clicks
            $campaignClicks = CampaignClick::whereDate('created_at', $date)
                ->sum('clicks_count');
            $campaignClicksData[] = $campaignClicks;

            // Conversions
            $conversions = CampaignConversion::whereDate('created_at', $date)
                ->sum('conversions_count');
            $conversionsData[] = $conversions;

            // User Behaviors
            $userBehaviors = UserBehavior::whereDate('created_at', $date)->count();
            $userBehaviorsData[] = $userBehaviors;

            // Recommendations
            $recommendations = RecommendationAnalytics::whereDate('created_at', $date)
                ->sum('recommendations_count');
            $recommendationsData[] = $recommendations;
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
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Orders',
                    'data' => $ordersData,
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                    'yAxisID' => 'y1',
                ],
                [
                    'label' => 'New Users',
                    'data' => $usersData,
                    'borderColor' => 'rgb(168, 85, 247)',
                    'backgroundColor' => 'rgba(168, 85, 247, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                    'yAxisID' => 'y1',
                ],
                [
                    'label' => 'New Products',
                    'data' => $productsData,
                    'borderColor' => 'rgb(245, 158, 11)',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                    'yAxisID' => 'y1',
                ],
                [
                    'label' => 'New Variants',
                    'data' => $variantsData,
                    'borderColor' => 'rgb(239, 68, 68)',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                    'yAxisID' => 'y1',
                ],
                [
                    'label' => 'Reviews',
                    'data' => $reviewsData,
                    'borderColor' => 'rgb(16, 185, 129)',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                    'yAxisID' => 'y1',
                ],
                [
                    'label' => 'Wishlist Items',
                    'data' => $wishlistData,
                    'borderColor' => 'rgb(236, 72, 153)',
                    'backgroundColor' => 'rgba(236, 72, 153, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                    'yAxisID' => 'y1',
                ],
                [
                    'label' => 'Page Views',
                    'data' => $pageViewsData,
                    'borderColor' => 'rgb(99, 102, 241)',
                    'backgroundColor' => 'rgba(99, 102, 241, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                    'yAxisID' => 'y1',
                ],
                [
                    'label' => 'Searches',
                    'data' => $searchesData,
                    'borderColor' => 'rgb(14, 165, 233)',
                    'backgroundColor' => 'rgba(14, 165, 233, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                    'yAxisID' => 'y1',
                ],
                [
                    'label' => 'Cart Adds',
                    'data' => $cartAddsData,
                    'borderColor' => 'rgb(34, 197, 94)',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                    'yAxisID' => 'y1',
                ],
                [
                    'label' => 'Campaign Views',
                    'data' => $campaignViewsData,
                    'borderColor' => 'rgb(251, 146, 60)',
                    'backgroundColor' => 'rgba(251, 146, 60, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                    'yAxisID' => 'y1',
                ],
                [
                    'label' => 'Campaign Clicks',
                    'data' => $campaignClicksData,
                    'borderColor' => 'rgb(220, 38, 127)',
                    'backgroundColor' => 'rgba(220, 38, 127, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                    'yAxisID' => 'y1',
                ],
                [
                    'label' => 'Conversions',
                    'data' => $conversionsData,
                    'borderColor' => 'rgb(132, 204, 22)',
                    'backgroundColor' => 'rgba(132, 204, 22, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                    'yAxisID' => 'y1',
                ],
                [
                    'label' => 'User Behaviors',
                    'data' => $userBehaviorsData,
                    'borderColor' => 'rgb(107, 114, 128)',
                    'backgroundColor' => 'rgba(107, 114, 128, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                    'yAxisID' => 'y1',
                ],
                [
                    'label' => 'Recommendations',
                    'data' => $recommendationsData,
                    'borderColor' => 'rgb(139, 69, 19)',
                    'backgroundColor' => 'rgba(139, 69, 19, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                    'yAxisID' => 'y1',
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
                    'labels' => [
                        'usePointStyle' => true,
                        'pointStyle' => 'circle',
                        'padding' => 20,
                    ],
                ],
                'title' => [
                    'display' => true,
                    'text' => '30-Day Comprehensive Analytics Overview',
                    'font' => [
                        'size' => 16,
                        'weight' => 'bold',
                    ],
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                    'title' => [
                        'display' => true,
                        'text' => 'Revenue (€)',
                    ],
                    'grid' => [
                        'color' => 'rgba(0, 0, 0, 0.1)',
                    ],
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'title' => [
                        'display' => true,
                        'text' => 'Count',
                    ],
                    'grid' => [
                        'drawOnChartArea' => false,
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

    public function getMaxHeight(): string
    {
        return $this->maxHeight;
    }
}
