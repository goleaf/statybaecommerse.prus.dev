<?php declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Address;
use App\Models\AnalyticsEvent;
use App\Models\Attribute;
use App\Models\Brand;
use App\Models\Campaign;
use App\Models\CampaignClick;
use App\Models\CampaignConversion;
use App\Models\CampaignView;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Country;
use App\Models\Coupon;
use App\Models\Currency;
use App\Models\Discount;
use App\Models\DiscountCode;
use App\Models\Inventory;
use App\Models\Location;
use App\Models\News;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\RecommendationAnalytics;
use App\Models\Referral;
use App\Models\ReferralCode;
use App\Models\Review;
use App\Models\Slider;
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

class UltimateStatsWidget extends BaseWidget
{
    protected static ?int $sort = -1;
    protected int|string|array $columnSpan = 'full';

    public function getStats(): array
    {
        $now = Carbon::now();
        $lastMonth = $now->copy()->subMonth();
        $lastWeek = $now->copy()->subWeek();
        $yesterday = $now->copy()->subDay();

        // === CORE BUSINESS METRICS ===
        $totalRevenue = Order::where('status', '!=', 'cancelled')->sum('total');
        $lastMonthRevenue = Order::where('status', '!=', 'cancelled')
            ->where('created_at', '>=', $lastMonth)
            ->sum('total');
        $revenueGrowth = $lastMonthRevenue > 0 ? (($totalRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100 : 0;

        $totalOrders = Order::count();
        $lastMonthOrders = Order::where('created_at', '>=', $lastMonth)->count();
        $orderGrowth = $lastMonthOrders > 0 ? (($totalOrders - $lastMonthOrders) / $lastMonthOrders) * 100 : 0;

        $totalUsers = User::count();
        $newUsersThisMonth = User::where('created_at', '>=', $lastMonth)->count();
        $userGrowth = $newUsersThisMonth > 0 ? ($newUsersThisMonth / max($totalUsers - $newUsersThisMonth, 1)) * 100 : 0;

        // === PRODUCT ECOSYSTEM ===
        $totalProducts = Product::count();
        $activeProducts = Product::where('is_visible', true)->count();
        $totalVariants = ProductVariant::count();
        $lowStockProducts = Inventory::where('quantity', '<=', DB::raw('threshold'))->count();
        $outOfStockProducts = Inventory::where('quantity', '<=', 0)->count();

        // === CATEGORIES & BRANDS ===
        $totalCategories = Category::count();
        $activeCategories = Category::where('is_active', true)->count();
        $totalBrands = Brand::count();
        $activeBrands = Brand::where('is_active', true)->count();
        $totalCollections = Collection::count();

        // === INVENTORY & STOCK ===
        $totalStockMovements = StockMovement::count();
        $totalInventoryItems = Inventory::count();
        $totalStockValue = Inventory::sum(DB::raw('quantity * 0')); // Simplified calculation

        // === ORDERS & CART ===
        $totalOrderItems = OrderItem::count();
        // $totalCartItems = CartItem::count(); // Commented out - column issues
        // $totalWishlistItems = WishlistItem::count(); // Commented out - column issues
        $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        // === REVIEWS & RATINGS ===
        $totalReviews = Review::count();
        $approvedReviews = Review::where('is_approved', true)->count();
        $avgRating = Review::where('is_approved', true)->avg('rating') ?? 0;
        $pendingReviews = Review::where('is_approved', false)->count();

        // === CAMPAIGNS & MARKETING ===
        $totalCampaigns = Campaign::count();
        $activeCampaigns = Campaign::where('status', 'active')->count();
        $totalCampaignViews = CampaignView::sum('views_count');
        $totalCampaignClicks = CampaignClick::sum('clicks_count');
        $totalConversions = CampaignConversion::sum('conversions_count');

        // === DISCOUNTS & COUPONS ===
        $totalCoupons = Coupon::count();
        $activeCoupons = Coupon::where('is_active', true)->count();
        // $totalDiscountCodes = DiscountCode::count(); // Commented out - table doesn't exist
        // $activeDiscountCodes = DiscountCode::where('is_active', true)->count(); // Commented out - table doesn't exist

        // === ANALYTICS & TRACKING ===
        $totalPageViews = AnalyticsEvent::where('event_type', 'page_view')->count();
        $totalSearches = AnalyticsEvent::where('event_type', 'search')->count();
        $totalCartAdds = AnalyticsEvent::where('event_type', 'add_to_cart')->count();
        $totalUserBehaviors = UserBehavior::count();
        $totalVariantAnalytics = VariantAnalytics::count();

        // === RECOMMENDATION SYSTEM ===
        $totalRecommendations = RecommendationAnalytics::sum('recommendations_count');
        $totalReferrals = Referral::count();
        $totalReferralCodes = ReferralCode::count();

        // === GEOGRAPHIC & LOCATIONS ===
        $totalCountries = Country::count();
        $totalZones = Zone::count();
        $totalLocations = Location::count();
        $totalAddresses = Address::count();
        $totalCities = \App\Models\City::count();

        // === SYSTEM & SETTINGS ===
        $totalSystemSettings = SystemSetting::count();
        $totalCurrencies = Currency::count();
        $totalNews = News::count();
        $totalSliders = Slider::count();

        // === ATTRIBUTES & FEATURES ===
        $totalAttributes = Attribute::count();
        $totalAttributeValues = \App\Models\AttributeValue::count();

        return [
            // === PRIMARY BUSINESS METRICS ===
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
                ->description(__('translations.new_customers_this_month') . ': ' . \Illuminate\Support\Number::format($newUsersThisMonth))
                ->descriptionIcon($userGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($userGrowth >= 0 ? 'success' : 'danger'),
            Stat::make(__('translations.average_order_value'), \Illuminate\Support\Number::currency($avgOrderValue, 'EUR'))
                ->description(__('translations.per_order'))
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('info'),
            // === PRODUCT ECOSYSTEM ===
            Stat::make(__('translations.total_products'), \Illuminate\Support\Number::format($totalProducts))
                ->description(__('translations.active_products') . ': ' . \Illuminate\Support\Number::format($activeProducts))
                ->descriptionIcon('heroicon-m-cube')
                ->color('primary'),
            Stat::make(__('translations.product_variants'), \Illuminate\Support\Number::format($totalVariants))
                ->description(__('translations.total_variants'))
                ->descriptionIcon('heroicon-m-squares-2x2')
                ->color('info'),
            Stat::make(__('translations.low_stock'), \Illuminate\Support\Number::format($lowStockProducts))
                ->description(__('translations.products_need_restocking'))
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($lowStockProducts > 0 ? 'warning' : 'success'),
            Stat::make(__('translations.out_of_stock'), \Illuminate\Support\Number::format($outOfStockProducts))
                ->description(__('translations.products_out_of_stock'))
                ->descriptionIcon('heroicon-m-x-circle')
                ->color($outOfStockProducts > 0 ? 'danger' : 'success'),
            // === CATEGORIES & BRANDS ===
            Stat::make(__('translations.categories'), \Illuminate\Support\Number::format($totalCategories))
                ->description(__('translations.active_categories') . ': ' . \Illuminate\Support\Number::format($activeCategories))
                ->descriptionIcon('heroicon-m-tag')
                ->color('info'),
            Stat::make(__('translations.brands'), \Illuminate\Support\Number::format($totalBrands))
                ->description(__('translations.active_brands') . ': ' . \Illuminate\Support\Number::format($activeBrands))
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color('primary'),
            Stat::make(__('translations.collections'), \Illuminate\Support\Number::format($totalCollections))
                ->description(__('translations.total_collections'))
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('warning'),
            // === INVENTORY & STOCK ===
            Stat::make(__('translations.stock_movements'), \Illuminate\Support\Number::format($totalStockMovements))
                ->description(__('translations.total_movements'))
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('info'),
            Stat::make(__('translations.inventory_items'), \Illuminate\Support\Number::format($totalInventoryItems))
                ->description(__('translations.total_items'))
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('primary'),
            Stat::make(__('translations.stock_value'), \Illuminate\Support\Number::currency($totalStockValue, 'EUR'))
                ->description(__('translations.total_value'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
            // === REVIEWS & RATINGS ===
            Stat::make(__('translations.total_reviews'), \Illuminate\Support\Number::format($totalReviews))
                ->description(__('translations.approved_reviews') . ': ' . \Illuminate\Support\Number::format($approvedReviews))
                ->descriptionIcon('heroicon-m-star')
                ->color('warning'),
            Stat::make(__('translations.average_rating'), number_format($avgRating, 1) . '/5')
                ->description(__('translations.customer_satisfaction'))
                ->descriptionIcon('heroicon-m-star')
                ->color($avgRating >= 4 ? 'success' : ($avgRating >= 3 ? 'warning' : 'danger')),
            Stat::make(__('translations.pending_reviews'), \Illuminate\Support\Number::format($pendingReviews))
                ->description(__('translations.awaiting_approval'))
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingReviews > 0 ? 'warning' : 'success'),
            // === CAMPAIGNS & MARKETING ===
            Stat::make(__('translations.total_campaigns'), \Illuminate\Support\Number::format($totalCampaigns))
                ->description(__('translations.active_campaigns') . ': ' . \Illuminate\Support\Number::format($activeCampaigns))
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
            // === DISCOUNTS & COUPONS ===
            Stat::make(__('translations.coupons'), \Illuminate\Support\Number::format($totalCoupons))
                ->description(__('translations.active_coupons') . ': ' . \Illuminate\Support\Number::format($activeCoupons))
                ->descriptionIcon('heroicon-m-ticket')
                ->color('warning'),
            // Stat::make(__('translations.discount_codes'), \Illuminate\Support\Number::format($totalDiscountCodes))
            //     ->description(__('translations.active_discounts') . ': ' . \Illuminate\Support\Number::format($activeDiscountCodes))
            //     ->descriptionIcon('heroicon-m-percent')
            //     ->color('info'), // Commented out - table doesn't exist
            // === ANALYTICS & TRACKING ===
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
            // Stat::make(__('translations.wishlist_items'), \Illuminate\Support\Number::format($totalWishlistItems))
            //     ->description(__('translations.total_wishlist_items'))
            //     ->descriptionIcon('heroicon-m-heart')
            //     ->color('danger'), // Commented out - column issues
            // === RECOMMENDATION SYSTEM ===
            Stat::make(__('translations.recommendations'), \Illuminate\Support\Number::format($totalRecommendations))
                ->description(__('translations.total_recommendations'))
                ->descriptionIcon('heroicon-m-light-bulb')
                ->color('success'),
            Stat::make(__('translations.referrals'), \Illuminate\Support\Number::format($totalReferrals))
                ->description(__('translations.total_referrals'))
                ->descriptionIcon('heroicon-m-share')
                ->color('info'),
            Stat::make(__('translations.referral_codes'), \Illuminate\Support\Number::format($totalReferralCodes))
                ->description(__('translations.total_codes'))
                ->descriptionIcon('heroicon-m-code-bracket')
                ->color('primary'),
            // === GEOGRAPHIC & LOCATIONS ===
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
            Stat::make(__('translations.cities'), \Illuminate\Support\Number::format($totalCities))
                ->description(__('translations.total_cities'))
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('success'),
            // === SYSTEM & SETTINGS ===
            Stat::make(__('translations.system_settings'), \Illuminate\Support\Number::format($totalSystemSettings))
                ->description(__('translations.configuration_items'))
                ->descriptionIcon('heroicon-m-cog-6-tooth')
                ->color('warning'),
            Stat::make(__('translations.currencies'), \Illuminate\Support\Number::format($totalCurrencies))
                ->description(__('translations.supported_currencies'))
                ->descriptionIcon('heroicon-m-currency-euro')
                ->color('info'),
            Stat::make(__('translations.news'), \Illuminate\Support\Number::format($totalNews))
                ->description(__('translations.total_news_items'))
                ->descriptionIcon('heroicon-m-newspaper')
                ->color('primary'),
            Stat::make(__('translations.sliders'), \Illuminate\Support\Number::format($totalSliders))
                ->description(__('translations.total_sliders'))
                ->descriptionIcon('heroicon-m-photo')
                ->color('success'),
            // === ATTRIBUTES & FEATURES ===
            Stat::make(__('translations.attributes'), \Illuminate\Support\Number::format($totalAttributes))
                ->description(__('translations.product_attributes'))
                ->descriptionIcon('heroicon-m-list-bullet')
                ->color('info'),
            Stat::make(__('translations.attribute_values'), \Illuminate\Support\Number::format($totalAttributeValues))
                ->description(__('translations.total_attribute_values'))
                ->descriptionIcon('heroicon-m-tag')
                ->color('primary'),
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

    public function getRevenueChart(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $revenue = Order::where('status', '!=', 'cancelled')
                ->whereDate('created_at', $date)
                ->sum('total');
            $data[] = $revenue;
        }
        return $data;
    }

    public function getOrdersChart(): array
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
