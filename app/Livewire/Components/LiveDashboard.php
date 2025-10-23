<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Services\CacheInvalidationService;
use App\Support\Cache\CacheKeys;
use App\Support\Cache\CacheTagHelper;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Cache\TaggableStore;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * LiveDashboard
 *
 * Livewire component for LiveDashboard with reactive frontend functionality, real-time updates, and user interaction handling.
 *
 * @property bool   $autoRefresh
 * @property int    $refreshInterval
 * @property array  $selectedMetrics
 * @property string $timeRange
 */
final class LiveDashboard extends Component
{
    public bool $autoRefresh = true;

    public int $refreshInterval = 30;

    // seconds
    public array $selectedMetrics = ['products', 'orders', 'users', 'reviews'];

    public string $timeRange = '24h';

    // 1h, 24h, 7d, 30d
    /**
     * Initialize the Livewire component with parameters.
     */
    public function mount(): void
    {
        if ($this->autoRefresh) {
            $this->dispatch('start-auto-refresh', interval: $this->refreshInterval * 1000);
        }
    }

    /**
     * Handle toggleAutoRefresh functionality with proper error handling.
     */
    public function toggleAutoRefresh(): void
    {
        $this->autoRefresh = ! $this->autoRefresh;
        if ($this->autoRefresh) {
            $this->dispatch('start-auto-refresh', interval: $this->refreshInterval * 1000);
        } else {
            $this->dispatch('stop-auto-refresh');
        }
    }

    /**
     * Handle updateTimeRange functionality with proper error handling.
     */
    public function updateTimeRange(string $range): void
    {
        $this->timeRange = $range;
        $this->clearCache();
    }

    /**
     * Handle toggleMetric functionality with proper error handling.
     */
    public function toggleMetric(string $metric): void
    {
        if (in_array($metric, $this->selectedMetrics)) {
            $this->selectedMetrics = array_filter($this->selectedMetrics, fn ($m) => $m !== $metric);
        } else {
            $this->selectedMetrics[] = $metric;
        }
    }

    /**
     * Handle realTimeStats functionality with proper error handling.
     */
    #[Computed(persist: true, seconds: 60)]
    public function realTimeStats(): array
    {
        return $this->rememberDashboard(CacheKeys::dashboardStats($this->timeRange), CacheKeys::TTL_MINUTE, function () {
            $since = $this->getSinceTimestamp();

            return [
                'products' => [
                    'total'     => Product::where('is_visible', true)->count(),
                    'new_today' => Product::where('is_visible', true)->where('created_at', '>=', $since)->count(),
                    'featured'  => Product::where('is_featured', true)->where('is_visible', true)->count(),
                    'low_stock' => Product::where('stock_quantity', '<', 10)->where('is_visible', true)->count(),
                ],
                'orders' => [
                    'total'     => Order::count(),
                    'today'     => Order::createdSince($since)->count(),
                    'pending'   => Order::where('status', 'pending')->count(),
                    'completed' => Order::where('status', 'completed')->count(),
                    'revenue'   => Order::where('status', 'completed')->createdSince($since)->sum('total_amount'),
                ],
                'users' => [
                    'total'     => User::count(),
                    'new_today' => User::where('created_at', '>=', $since)->count(),
                    'active'    => User::where('last_activity_at', '>=', Carbon::now()->subHours(24))->count(),
                ],
                'reviews' => [
                    'total'      => Review::where('is_approved', true)->count(),
                    'today'      => Review::where('is_approved', true)->where('created_at', '>=', $since)->count(),
                    'pending'    => Review::where('is_approved', false)->count(),
                    'avg_rating' => Review::where('is_approved', true)->avg('rating') ?? 0,
                ],
            ];
        });
    }

    /**
     * Handle liveActivity functionality with proper error handling.
     */
    #[Computed(persist: true, seconds: 120)]
    public function liveActivity(): array
    {
        return $this->rememberDashboard(CacheKeys::dashboardActivity($this->timeRange), CacheKeys::TTL_TWO_MINUTES, function () {
            $since = $this->getSinceTimestamp();

            return [
                'recent_orders' => Order::with(['user'])
                    ->createdSince($since)
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get()
                    ->map(fn ($order) => [
                        'id'         => $order->id,
                        'user_name'  => $order->user?->name ?? 'Guest',
                        'total'      => $order->total_amount,
                        'status'     => $order->status,
                        'created_at' => $order->created_at->diffForHumans(),
                    ]),
                'recent_reviews' => Review::with(['product', 'user'])
                    ->where('is_approved', true)
                    ->where('created_at', '>=', $since)
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get()
                    ->map(fn ($review) => [
                        'id'           => $review->id,
                        'product_name' => $review->product?->name ?? 'Unknown',
                        'user_name'    => $review->user?->name ?? 'Anonymous',
                        'rating'       => $review->rating,
                        'created_at'   => $review->created_at->diffForHumans(),
                    ]),
                'popular_products' => Product::with(['brand'])
                    ->where('is_visible', true)
                    ->whereHas('reviews')
                    ->withCount('reviews')
                    ->orderBy('reviews_count', 'desc')
                    ->limit(5)
                    ->get()
                    ->map(fn ($product) => [
                        'id'            => $product->id,
                        'name'          => $product->name,
                        'brand'         => $product->brand?->name,
                        'reviews_count' => $product->reviews_count,
                        'price'         => $product->price,
                    ]),
            ];
        });
    }

    /**
     * Handle performanceMetrics functionality with proper error handling.
     */
    #[Computed(persist: true, seconds: 300)]
    public function performanceMetrics(): array
    {
        return $this->rememberDashboard(CacheKeys::dashboardPerformance($this->timeRange), CacheKeys::TTL_FIVE_MINUTES, function () {
            return [
                'page_views' => rand(1000, 5000),
                // Mock data - replace with real analytics
                'bounce_rate'          => rand(30, 70),
                'avg_session_duration' => rand(120, 600),
                'conversion_rate'      => rand(2, 8),
                'top_pages'            => [['page' => 'Home', 'views' => rand(500, 2000)], ['page' => 'Products', 'views' => rand(300, 1500)], ['page' => 'Categories', 'views' => rand(200, 1000)]],
            ];
        }, [CacheKeys::dashboardTag()]);
    }

    /**
     * Handle refreshDashboard functionality with proper error handling.
     */
    #[On('refresh-dashboard')]
    public function refreshDashboard(): void
    {
        $this->clearCache();
        $this->dispatch('dashboard-refreshed');
    }

    /**
     * Handle getTimeCondition functionality with proper error handling.
     */
    private function getSinceTimestamp(): Carbon
    {
        $now = Carbon::now();

        return match ($this->timeRange) {
            '1h'    => $now->copy()->subHour(),
            '24h'   => $now->copy()->subDay(),
            '7d'    => $now->copy()->subWeek(),
            '30d'   => $now->copy()->subMonth(),
            default => $now->copy()->subDay(),
        };
    }

    /**
     * Remember dashboard cache entries while applying dashboard tags when supported.
     *
     * @return array<string, mixed>
     */
    private function rememberDashboard(string $key, int|DateInterval $ttl, callable $callback): array
    {
        $store = Cache::getStore();

        if ($store instanceof TaggableStore) {
            return Cache::tags(CacheTagHelper::dashboards())->remember($key, $ttl, $callback);
        }

        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Handle clearCache functionality with proper error handling.
     */
    private function clearCache(): void
    {
        app(CacheInvalidationService::class)->flushDashboards();
    }

    /**
     * Cache dashboard datasets under the shared dashboard tag when available.
     */
    private function rememberDashboard(string $key, int $ttl, callable $callback): array
    {
        if (Cache::supportsTags()) {
            return Cache::tags(CacheTagHelper::dashboards())->remember($key, $ttl, $callback);
        }

        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Render the Livewire component view with current state.
     */
    public function render(): View
    {
        return view('livewire.components.live-dashboard');
    }
}
