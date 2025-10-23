<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Support\Cache\CacheKeys;
use App\Support\Cache\CacheTagHelper;
use App\Support\Cache\CacheTags;
use DateTimeInterface;
use Carbon\Carbon;
use DateInterval;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Cache\TaggableStore;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SimplifiedStatsWidget extends BaseWidget
{
    protected static ?int $sort = -1;

    protected int|string|array $columnSpan = 'full';

    /**
     * Cached chart payload for the current request lifecycle.
     *
     * @var array{revenue: array<int, float>, orders: array<int, int>}|null
     */
    protected ?array $chartData = null;

    protected ?Carbon $referenceTime = null;

    public function getStats(): array
    {
        $stats = $this->getSummaryStats();

        $totalRevenue = $stats['orders']['total_revenue'];
        $lastMonthRevenue = $stats['orders']['last_month_revenue'];
        $totalOrders = $stats['orders']['total_orders'];
        $lastMonthOrders = $stats['orders']['last_month_orders'];
        $totalUsers = $stats['users']['total_users'];
        $newUsersThisMonth = $stats['users']['new_users_this_month'];
        $totalProducts = $stats['products']['total_products'];
        $activeProducts = $stats['products']['active_products'];
        $totalCategories = $stats['catalog']['total_categories'];
        $totalBrands = $stats['catalog']['total_brands'];
        $totalReviews = $stats['reviews']['total_reviews'];
        $approvedReviews = $stats['reviews']['approved_reviews'];
        $avgRating = $stats['reviews']['avg_rating'];

        $revenueGrowth = $lastMonthRevenue > 0 ? (($totalRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100 : 0;
        $orderGrowth = $lastMonthOrders > 0 ? (($totalOrders - $lastMonthOrders) / $lastMonthOrders) * 100 : 0;
        $userGrowth = $newUsersThisMonth > 0 ? ($newUsersThisMonth / max($totalUsers - $newUsersThisMonth, 1)) * 100 : 0;
        $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

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
            Stat::make(__('translations.total_products'), \Illuminate\Support\Number::format($totalProducts)) // Viso produktų
                ->description(__('translations.active_products') . ': ' . \Illuminate\Support\Number::format($activeProducts))
                ->descriptionIcon('heroicon-m-cube')
                ->color('primary'),

            Stat::make(__('translations.categories'), \Illuminate\Support\Number::format($totalCategories))
                ->description(__('translations.total_categories'))
                ->descriptionIcon('heroicon-m-tag')
                ->color('info'),

            Stat::make(__('translations.brands'), \Illuminate\Support\Number::format($totalBrands))
                ->description(__('translations.total_brands'))
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color('primary'),

            // === REVIEWS & RATINGS ===
            Stat::make(__('translations.total_reviews'), \Illuminate\Support\Number::format($totalReviews))
                ->description(__('translations.approved_reviews') . ': ' . \Illuminate\Support\Number::format($approvedReviews))
                ->descriptionIcon('heroicon-m-star')
                ->color('warning'),

            Stat::make(__('translations.average_rating'), number_format((float) $avgRating, 1) . '/5')
                ->description(__('translations.customer_satisfaction'))
                ->descriptionIcon('heroicon-m-star')
                ->color($avgRating >= 4 ? 'success' : ($avgRating >= 3 ? 'warning' : 'danger')),
        ];
    }

    /**
     * @return array<int, float>
     */
    public function getRevenueChart(): array
    {
        return $this->getChartData()['revenue'];
    }

    /**
     * @return array<int, int>
     */
    public function getOrdersChart(): array
    {
        return $this->getChartData()['orders'];
    }

    /**
     * @return array{revenue: array<int, float>, orders: array<int, int>}
     */
    protected function getChartData(): array
    {
        if ($this->chartData !== null) {
            return $this->chartData;
        }

        $now = $this->getReferenceTime();
        $startDate = $now->copy()->subDays(6)->startOfDay();
        $endDate = $now->copy()->endOfDay();

        $cacheKey = CacheKeys::dashboardSimplifiedChart(
            $startDate->toDateString(),
            $endDate->toDateString()
        );

        // Tagging keeps the cache easy to purge from Filament's maintenance tools.
        $chartData = $this->rememberDashboardCache(
            [CacheTags::dashboard(), CacheTags::orders()],
            $cacheKey,
            now()->addSeconds(180),
            function () use ($startDate, $endDate, $now): array {
            $dateKeys = [];
            for ($i = 6; $i >= 0; $i--) {
                $dateKeys[] = $now->copy()->subDays($i)->toDateString();
            }

            $orderStats = Order::query()
                ->createdBetween($startDate, $endDate)
                ->selectRaw('DATE(created_at) as date, SUM(CASE WHEN status != ? THEN total ELSE 0 END) as revenue, COUNT(*) as total_orders', ['cancelled'])
                ->groupBy('date')
                ->toBase()
                ->get()
                ->mapWithKeys(static function (object $row): array {
                    $data = (array) $row;
                    $date = isset($data['date']) ? (string) $data['date'] : '';

                    return [
                        $date => [
                            'revenue' => isset($data['revenue']) ? (float) $data['revenue'] : 0.0,
                            'orders' => isset($data['total_orders']) ? (int) $data['total_orders'] : 0,
                        ],
                    ];
                })
                ->all();

                        return [
                            $date => [
                                'revenue' => isset($data['revenue']) ? (float) $data['revenue'] : 0.0,
                                'orders'  => isset($data['total_orders']) ? (int) $data['total_orders'] : 0,
                            ],
                        ];
                    })
                    ->all();

                $revenueChart = [];
                $ordersChart = [];

            return [
                'revenue' => $revenueChart,
                'orders' => $ordersChart,
            ];
        }, [CacheKeys::dashboardTag()]);

        return $this->chartData = $chartData;
    }

    /**
     * @return array{
     *     orders: array<string, float|int>,
     *     users: array<string, int>,
     *     products: array<string, int>,
     *     catalog: array<string, int>,
     *     reviews: array<string, int|float>
     * }
     */
    protected function getSummaryStats(): array
    {
        $now = $this->getReferenceTime();
        $lastMonth = $now->copy()->subMonth();

        return $this->rememberDashboardCache(
            [
                CacheTags::dashboard(),
                CacheTags::orders(),
                CacheTags::users(),
                CacheTags::products(),
                CacheTags::categories(),
                CacheTags::brands(),
                CacheTags::reviews(),
            ],
            CacheKeys::dashboardSimplifiedSummary(),
            now()->addSeconds(300),
            function () use ($lastMonth): array {
            $orderStats = Order::query()
                ->selectRaw('
                    SUM(CASE WHEN status != ? THEN total ELSE 0 END) as total_revenue,
                    SUM(CASE WHEN status != ? AND created_at >= ? THEN total ELSE 0 END) as last_month_revenue,
                    COUNT(*) as total_orders,
                    SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as last_month_orders
                ', ['cancelled', 'cancelled', $lastMonth, $lastMonth])
                ->toBase()
                ->first();

            $totalRevenue = (float) ($nonCancelledOrders()->sum('total') ?? 0.0);
            $lastMonthRevenue = (float) ($nonCancelledOrders()->createdSince($lastMonth)->sum('total') ?? 0.0);
            $totalOrders = (int) Order::count();
            $lastMonthOrders = (int) Order::query()->createdSince($lastMonth)->count();

            $totalUsers = (int) User::query()->count();
            $newUsersThisMonth = (int) User::query()->where('created_at', '>=', $lastMonth)->count();

            $totalUsers = (int) User::query()->count();
            $newUsersThisMonth = (int) User::query()->where('created_at', '>=', $lastMonth)->count();

                $productStats = Product::query()
                    ->selectRaw('
                    COUNT(*) as total_products,
                    SUM(CASE WHEN is_visible = 1 THEN 1 ELSE 0 END) as active_products
                ')
                    ->toBase()
                    ->first();

                $reviewStats = Review::query()
                    ->selectRaw('
                    COUNT(*) as total_reviews,
                    SUM(CASE WHEN is_approved = 1 THEN 1 ELSE 0 END) as approved_reviews,
                    AVG(CASE WHEN is_approved = 1 THEN rating END) as avg_rating
                ')
                    ->toBase()
                    ->first();

            return [
                'orders' => [
                    'total_revenue'      => (float) ($orderStats->total_revenue ?? 0),
                    'last_month_revenue' => (float) ($orderStats->last_month_revenue ?? 0),
                    'total_orders'       => (int) ($orderStats->total_orders ?? 0),
                    'last_month_orders'  => (int) ($orderStats->last_month_orders ?? 0),
                ],
                'users' => [
                    'total_users'          => (int) ($userStats->total_users ?? 0),
                    'new_users_this_month' => (int) ($userStats->new_users_this_month ?? 0),
                ],
                'products' => [
                    'total_products'  => (int) ($productStats->total_products ?? 0),
                    'active_products' => (int) ($productStats->active_products ?? 0),
                ],
                'catalog' => [
                    'total_categories' => (int) DB::table('categories')->count(),
                    'total_brands'     => (int) DB::table('brands')->count(),
                ],
                'reviews' => [
                    'total_reviews'    => (int) ($reviewStats->total_reviews ?? 0),
                    'approved_reviews' => (int) ($reviewStats->approved_reviews ?? 0),
                    'avg_rating'       => (float) ($reviewStats->avg_rating ?? 0),
                ],
            ];
        }, [CacheKeys::dashboardTag()]);
    }

    private function rememberDashboard(string $key, int $ttl, callable $callback): array
    {
        $store = Cache::getStore();

        if ($store instanceof TaggableStore) {
            return Cache::tags(CacheTagHelper::dashboards())->remember($key, $ttl, $callback);
        }

        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Remember dashboard cache entries while applying dashboard tags when supported.
     *
     * @return array<string, mixed>
     */
    protected function getReferenceTime(): Carbon
    {
        return $this->referenceTime ??= Carbon::now();
    }

    /**
     * Remember dashboard fragments while gracefully falling back when tags are unsupported.
     *
     * @template TValue
     *
     * @param  array<int, string>     $tags
     * @param  callable(): TValue     $callback
     * @param  DateTimeInterface|int  $ttl
     * @return TValue
     */
    private function rememberDashboardCache(array $tags, string $key, DateTimeInterface|int $ttl, callable $callback): mixed
    {
        // Bail out quickly when the cache store cannot work with tags (array, file, etc.).
        if ($tags !== [] && CacheTagHelper::supportsTags()) {
            /** @var TaggableStore $store */
            $store = Cache::getStore();

            // Double-check the store implements the contract before tagging.
            if ($store instanceof TaggableStore) {
                return Cache::tags(CacheTagHelper::merge($tags, CacheTagHelper::dashboards()))->remember($key, $ttl, $callback);
            }
        }

        // Fallback path keeps tests and array stores functional without tag support.
        return Cache::remember($key, $ttl, $callback);
    }
}
