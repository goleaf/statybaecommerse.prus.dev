<?php declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Address;
use App\Models\AnalyticsEvent;
use App\Models\Brand;
use App\Models\Campaign;
use App\Models\CampaignClick;
use App\Models\CampaignConversion;
use App\Models\CampaignView;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Country;
use App\Models\Coupon;
use App\Models\Currency;
use App\Models\Discount;
use App\Models\DiscountRedemption;
use App\Models\Inventory;
use App\Models\Legal;
use App\Models\Location;
use App\Models\News;
use App\Models\Order;
use App\Models\Product;
use App\Models\RecommendationAnalytics;
use App\Models\Review;
use App\Models\StockMovement;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\UserBehavior;
use App\Models\VariantAnalytics;
use App\Models\WishlistItem;
use App\Models\Zone;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class DashboardOverviewWidget extends BaseWidget
{
    protected string $view = 'filament.widgets.dashboard-overview-widget';

    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $now = Carbon::now();
        $lastMonth = $now->copy()->subMonth();
        $lastWeek = $now->copy()->subWeek();
        $yesterday = $now->copy()->subDay();

        // Core Business Metrics
        $totalRevenue = Order::where('status', '!=', 'cancelled')->sum('total_amount');
        $lastMonthRevenue = Order::where('status', '!=', 'cancelled')
            ->where('created_at', '>=', $lastMonth)
            ->sum('total_amount');
        $revenueGrowth = $lastMonthRevenue > 0 ? (($totalRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100 : 0;

        $totalOrders = Order::count();
        $lastMonthOrders = Order::where('created_at', '>=', $lastMonth)->count();
        $orderGrowth = $lastMonthOrders > 0 ? (($totalOrders - $lastMonthOrders) / $lastMonthOrders) * 100 : 0;

        $totalUsers = User::count();
        $newUsersThisMonth = User::where('created_at', '>=', $lastMonth)->count();
        $userGrowth = $newUsersThisMonth > 0 ? ($newUsersThisMonth / max($totalUsers - $newUsersThisMonth, 1)) * 100 : 0;

        $totalProducts = Product::count();
        $activeProducts = Product::where('is_visible', true)->count();
        $lowStockProducts = Inventory::where('stock_quantity', '<=', DB::raw('low_stock_threshold'))->count();

        // Advanced Metrics
        $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
        $totalReviews = Review::where('is_approved', true)->count();
        $avgRating = Review::where('is_approved', true)->avg('rating') ?? 0;
        $activeCampaigns = Campaign::where('status', 'active')->count();
        $totalCampaignViews = CampaignView::sum('views_count');
        $totalCampaignClicks = CampaignClick::sum('clicks_count');
        $totalConversions = CampaignConversion::sum('conversions_count');

        // Performance Metrics
        $totalPageViews = AnalyticsEvent::where('event_type', 'page_view')->count();
        $totalSearches = AnalyticsEvent::where('event_type', 'search')->count();
        $totalCartAdds = AnalyticsEvent::where('event_type', 'add_to_cart')->count();
        $totalWishlistAdds = WishlistItem::count();

        // Geographic & System Metrics
        $totalCountries = Country::count();
        $totalZones = 0;
        $totalLocations = Location::count();
        $totalAddresses = Address::count();
        $totalSystemSettings = SystemSetting::count();

        // Recommendation System
        $totalRecommendations = RecommendationAnalytics::sum('recommendations_count');
        $totalUserBehaviors = UserBehavior::count();
        $totalVariantAnalytics = VariantAnalytics::count();

        return [
            // Primary Business Metrics
            Stat::make(__('translations.total_revenue'), \Illuminate\Support\Number::currency($totalRevenue, 'EUR'))
                ->description(__('translations.from_last_month') . ': ' . \Illuminate\Support\Number::currency($lastMonthRevenue, 'EUR'))
                ->descriptionIcon($revenueGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($revenueGrowth >= 0 ? 'success' : 'danger')
                ->chart($this->getRevenueChart()),
            Stat::make(__('translations.total_orders'), \Illuminate\Support\Number::format($totalOrders))
                ->description(__('translations.from_last_month') . ': ' . \Illuminate\Support\Number::format($lastMonthOrders))
                ->descriptionIcon($orderGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($orderGrowth >= 0 ? 'success' : 'danger')
                ->chart($this->getOrdersChart()),
            Stat::make(__('translations.total_customers'), \Illuminate\Support\Number::format($totalUsers))
                ->description(__('translations.new_customers_today') . ': ' . \Illuminate\Support\Number::format($newUsersThisMonth))
                ->descriptionIcon($userGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($userGrowth >= 0 ? 'success' : 'danger'),
            Stat::make(__('translations.total_products'), \Illuminate\Support\Number::format($totalProducts))
                ->description(__('translations.active_products') . ': ' . \Illuminate\Support\Number::format($activeProducts))
                ->descriptionIcon('heroicon-m-cube')
                ->color('primary'),
            // Performance Metrics
            Stat::make(__('translations.average_order_value'), \Illuminate\Support\Number::currency($avgOrderValue, 'EUR'))
                ->description(__('translations.per_order'))
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('info'),
            Stat::make(__('translations.average_rating'), number_format($avgRating, 1) . '/5')
                ->description(__('translations.customer_satisfaction'))
                ->descriptionIcon('heroicon-m-star')
                ->color($avgRating >= 4 ? 'success' : ($avgRating >= 3 ? 'warning' : 'danger')),
            Stat::make(__('translations.low_stock'), \Illuminate\Support\Number::format($lowStockProducts))
                ->description(__('translations.products_need_restocking'))
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($lowStockProducts > 0 ? 'warning' : 'success'),
            Stat::make(__('translations.active_campaigns'), \Illuminate\Support\Number::format($activeCampaigns))
                ->description(__('translations.running_campaigns'))
                ->descriptionIcon('heroicon-m-megaphone')
                ->color('primary'),
            // Analytics Metrics
            Stat::make(__('translations.page_views'), \Illuminate\Support\Number::format($totalPageViews))
                ->description(__('translations.total_views'))
                ->descriptionIcon('heroicon-m-eye')
                ->color('info'),
            Stat::make(__('translations.campaign_views'), \Illuminate\Support\Number::format($totalCampaignViews))
                ->description(__('translations.total_impressions'))
                ->descriptionIcon('heroicon-m-eye')
                ->color('info'),
            Stat::make(__('translations.campaign_clicks'), \Illuminate\Support\Number::format($totalCampaignClicks))
                ->description(__('translations.total_clicks'))
                ->descriptionIcon('heroicon-m-cursor-arrow-rays')
                ->color('success'),
            Stat::make(__('translations.conversions'), \Illuminate\Support\Number::format($totalConversions))
                ->description(__('translations.total_conversions'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            // System Metrics
            Stat::make(__('translations.countries'), \Illuminate\Support\Number::format($totalCountries))
                ->description(__('translations.supported_countries'))
                ->descriptionIcon('heroicon-m-globe-alt')
                ->color('info'),
            Stat::make(__('translations.zones'), \Illuminate\Support\Number::format($totalZones))
                ->description(__('translations.shipping_zones'))
                ->descriptionIcon('heroicon-m-map')
                ->color('primary'),
            Stat::make(__('translations.locations'), \Illuminate\Support\Number::format($totalLocations))
                ->description(__('translations.warehouse_locations'))
                ->descriptionIcon('heroicon-m-building-office')
                ->color('warning'),
            Stat::make(__('translations.addresses'), \Illuminate\Support\Number::format($totalAddresses))
                ->description(__('translations.customer_addresses'))
                ->descriptionIcon('heroicon-m-home')
                ->color('info'),
            // Advanced System Metrics
            Stat::make(__('translations.recommendations'), \Illuminate\Support\Number::format($totalRecommendations))
                ->description(__('translations.total_recommendations'))
                ->descriptionIcon('heroicon-m-light-bulb')
                ->color('success'),
            Stat::make(__('translations.user_behaviors'), \Illuminate\Support\Number::format($totalUserBehaviors))
                ->description(__('translations.tracked_behaviors'))
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('info'),
            Stat::make(__('translations.variant_analytics'), \Illuminate\Support\Number::format($totalVariantAnalytics))
                ->description(__('translations.variant_insights'))
                ->descriptionIcon('heroicon-m-chart-pie')
                ->color('primary'),
            Stat::make(__('translations.system_settings'), \Illuminate\Support\Number::format($totalSystemSettings))
                ->description(__('translations.configuration_items'))
                ->descriptionIcon('heroicon-m-cog-6-tooth')
                ->color('warning'),
        ];
    }

    private function getRevenueChart(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $revenue = Order::where('status', '!=', 'cancelled')
                ->whereDate('created_at', $date)
                ->sum('total_amount');
            $data[] = $revenue;
        }
        return $data;
    }

    private function getOrdersChart(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $orders = Order::whereDate('created_at', $date)->count();
            $data[] = $orders;
        }
        return $data;
    }
}
