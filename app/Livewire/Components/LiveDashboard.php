<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\CacheInvalidationService;
use App\Support\Cache\CacheKeys;
use App\Support\Cache\CacheTagHelper;
use Illuminate\Cache\TaggableStore;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
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
    public array $selectedMetrics = ['products', 'orders', 'users'];

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
                    'total'      => 0,
                    'today'      => 0,
                    'pending'    => 0,
                    'avg_rating' => 0,
                ],
            ];
        });
    }

    /**
     * Expose a property-style accessor for Blade templates while delegating to
     * the method above so cached values can be re-evaluated after manual cache
     * invalidation.
     */
    public function getRealTimeStatsProperty(): array
    {
        return $this->realTimeStats();
    }

    /**
     * Handle liveActivity functionality with proper error handling.
     */
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
                'recent_reviews'   => collect(),
                'popular_products' => Product::with(['brand'])
                    ->where('is_visible', true)
                    ->latest('published_at')
                    ->limit(5)
                    ->get()
                    ->map(fn ($product) => [
                        'id'    => $product->id,
                        'name'  => $product->name,
                        'brand' => $product->brand?->name,
                        'price' => $product->price,
                    ]),
            ];
        });
    }

    /**
     * Provide a computed property bridge for Livewire views without locking the
     * underlying cached payload, ensuring refreshes honour cache invalidation.
     */
    public function getLiveActivityProperty(): array
    {
        return $this->liveActivity();
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
     * Remember dashboard fragments while honouring cache tag support.
     *
     * @template TValue
     *
     * @param  callable(): TValue $callback
     * @return TValue
     */
    /**
     * Render the Livewire component view with current state.
     */
    public function render(): View
    {
        return view('livewire.components.live-dashboard');
    }
}
