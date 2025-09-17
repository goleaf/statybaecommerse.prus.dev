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

class ComprehensiveStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $now = Carbon::now();
        $lastMonth = $now->copy()->subMonth();
        $lastWeek = $now->copy()->subWeek();
        $yesterday = $now->copy()->subDay();

        // Revenue Analytics
        $totalRevenue = Order::where('status', '!=', 'cancelled')->sum('total_amount');
        $lastMonthRevenue = Order::where('status', '!=', 'cancelled')
            ->where('created_at', '>=', $lastMonth)
            ->sum('total_amount');
        $revenueGrowth = $lastMonthRevenue > 0 ? (($totalRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100 : 0;

        // Orders Analytics
        $totalOrders = Order::count();
        $lastMonthOrders = Order::where('created_at', '>=', $lastMonth)->count();
        $orderGrowth = $lastMonthOrders > 0 ? (($totalOrders - $lastMonthOrders) / $lastMonthOrders) * 100 : 0;
        $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        // Products Analytics
        $totalProducts = Product::count();
        $activeProducts = Product::where('is_visible', true)->count();
        $lowStockProducts = Inventory::where('stock_quantity', '<=', DB::raw('low_stock_threshold'))->count();
        $outOfStockProducts = Inventory::where('stock_quantity', '<=', 0)->count();

        // Users Analytics
        $totalUsers = User::count();
        $newUsersThisMonth = User::where('created_at', '>=', $lastMonth)->count();
        $activeUsers = User::where('last_login_at', '>=', $lastWeek)->count();
        $userGrowth = $newUsersThisMonth > 0 ? ($newUsersThisMonth / max($totalUsers - $newUsersThisMonth, 1)) * 100 : 0;

        // Categories & Brands
        $totalCategories = Category::where('is_visible', true)->count();
        $totalBrands = Brand::count();

        // Reviews & Ratings
        $totalReviews = Review::where('is_approved', true)->count();
        $avgRating = Review::where('is_approved', true)->avg('rating') ?? 0;

        // Campaigns & Marketing
        $activeCampaigns = Campaign::where('status', 'active')->count();
        $totalCampaignViews = CampaignView::sum('views_count');
        $totalCampaignClicks = CampaignClick::sum('clicks_count');
        $totalConversions = CampaignConversion::sum('conversions_count');

        // Discounts & Coupons
        $activeDiscounts = Discount::where('is_active', true)->count();
        $activeCoupons = Coupon::where('is_active', true)->count();
        $totalDiscountSavings = DiscountRedemption::sum('discount_amount');

        // Inventory Analytics
        $totalInventoryValue = Inventory::sum(DB::raw('stock_quantity * cost_price'));
        $totalStockMovements = StockMovement::count();

        // Analytics Events
        $totalPageViews = AnalyticsEvent::where('event_type', 'page_view')->count();
        $totalSearches = AnalyticsEvent::where('event_type', 'search')->count();
        $totalCartAdds = AnalyticsEvent::where('event_type', 'add_to_cart')->count();

        // Geographic Analytics
        $totalCountries = Country::count();
        $totalZones = Zone::count();
        $totalLocations = Location::count();
        $totalAddresses = Address::count();

        // Content Analytics
        $totalNews = News::count();
        $totalLegalPages = Legal::count();
        $totalSystemSettings = SystemSetting::count();

        // Recommendation Analytics
        $totalRecommendations = RecommendationAnalytics::sum('recommendations_count');
        $totalUserBehaviors = UserBehavior::count();
        $totalVariantAnalytics = VariantAnalytics::count();

        return [
            // Financial Overview
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
            Stat::make(__('translations.average_order_value'), \Illuminate\Support\Number::currency($avgOrderValue, 'EUR'))
                ->description(__('translations.per_order'))
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('info'),
            // Products Overview
            Stat::make(__('translations.total_products'), \Illuminate\Support\Number::format($totalProducts))
                ->description(__('translations.active_products') . ': ' . \Illuminate\Support\Number::format($activeProducts))
                ->descriptionIcon('heroicon-m-cube')
                ->color('primary'),
            Stat::make(__('translations.low_stock'), \Illuminate\Support\Number::format($lowStockProducts))
                ->description(__('translations.products_need_restocking'))
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($lowStockProducts > 0 ? 'warning' : 'success'),
            Stat::make(__('translations.out_of_stock'), \Illuminate\Support\Number::format($outOfStockProducts))
                ->description(__('translations.products_unavailable'))
                ->descriptionIcon('heroicon-m-x-circle')
                ->color($outOfStockProducts > 0 ? 'danger' : 'success'),
            // Users Overview
            Stat::make(__('translations.total_customers'), \Illuminate\Support\Number::format($totalUsers))
                ->description(__('translations.new_customers_today') . ': ' . \Illuminate\Support\Number::format($newUsersThisMonth))
                ->descriptionIcon($userGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($userGrowth >= 0 ? 'success' : 'danger'),
            Stat::make(__('translations.active_users'), \Illuminate\Support\Number::format($activeUsers))
                ->description(__('translations.last_7_days'))
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
            // Categories & Brands
            Stat::make(__('translations.categories'), \Illuminate\Support\Number::format($totalCategories))
                ->description(__('translations.visible_categories'))
                ->descriptionIcon('heroicon-m-squares-2x2')
                ->color('primary'),
            Stat::make(__('translations.brands'), \Illuminate\Support\Number::format($totalBrands))
                ->description(__('translations.total_brands'))
                ->descriptionIcon('heroicon-m-tag')
                ->color('info'),
            // Reviews & Ratings
            Stat::make(__('translations.reviews_count'), \Illuminate\Support\Number::format($totalReviews))
                ->description(__('translations.approved_reviews'))
                ->descriptionIcon('heroicon-m-star')
                ->color('warning'),
            Stat::make(__('translations.average_rating'), number_format($avgRating, 1) . '/5')
                ->description(__('translations.customer_satisfaction'))
                ->descriptionIcon('heroicon-m-star')
                ->color($avgRating >= 4 ? 'success' : ($avgRating >= 3 ? 'warning' : 'danger')),
            // Marketing Analytics
            Stat::make(__('translations.active_campaigns'), \Illuminate\Support\Number::format($activeCampaigns))
                ->description(__('translations.running_campaigns'))
                ->descriptionIcon('heroicon-m-megaphone')
                ->color('primary'),
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
            // Discounts & Coupons
            Stat::make(__('translations.active_discounts'), \Illuminate\Support\Number::format($activeDiscounts))
                ->description(__('translations.running_discounts'))
                ->descriptionIcon('heroicon-m-percent')
                ->color('warning'),
            Stat::make(__('translations.active_coupons'), \Illuminate\Support\Number::format($activeCoupons))
                ->description(__('translations.available_coupons'))
                ->descriptionIcon('heroicon-m-ticket')
                ->color('info'),
            Stat::make(__('translations.discount_savings'), \Illuminate\Support\Number::currency($totalDiscountSavings, 'EUR'))
                ->description(__('translations.total_savings'))
                ->descriptionIcon('heroicon-m-currency-euro')
                ->color('success'),
            // Inventory Analytics
            Stat::make(__('translations.inventory_value'), \Illuminate\Support\Number::currency($totalInventoryValue, 'EUR'))
                ->description(__('translations.total_stock_value'))
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('primary'),
            Stat::make(__('translations.stock_movements'), \Illuminate\Support\Number::format($totalStockMovements))
                ->description(__('translations.total_movements'))
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('info'),
            // Analytics Events
            Stat::make(__('translations.page_views'), \Illuminate\Support\Number::format($totalPageViews))
                ->description(__('translations.total_views'))
                ->descriptionIcon('heroicon-m-eye')
                ->color('info'),
            Stat::make(__('translations.searches'), \Illuminate\Support\Number::format($totalSearches))
                ->description(__('translations.total_searches'))
                ->descriptionIcon('heroicon-m-magnifying-glass')
                ->color('primary'),
            Stat::make(__('translations.cart_adds'), \Illuminate\Support\Number::format($totalCartAdds))
                ->description(__('translations.add_to_cart_events'))
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('success'),
            // Geographic Analytics
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
            // Content Analytics
            Stat::make(__('translations.news_articles'), \Illuminate\Support\Number::format($totalNews))
                ->description(__('translations.total_articles'))
                ->descriptionIcon('heroicon-m-newspaper')
                ->color('primary'),
            Stat::make(__('translations.legal_pages'), \Illuminate\Support\Number::format($totalLegalPages))
                ->description(__('translations.legal_documents'))
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info'),
            Stat::make(__('translations.system_settings'), \Illuminate\Support\Number::format($totalSystemSettings))
                ->description(__('translations.configuration_items'))
                ->descriptionIcon('heroicon-m-cog-6-tooth')
                ->color('warning'),
            // Recommendation Analytics
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
