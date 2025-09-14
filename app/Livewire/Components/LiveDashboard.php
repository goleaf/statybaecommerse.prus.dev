<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use App\Models\Product;
use App\Models\Order;
use App\Models\User;
use App\Models\Review;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

final class LiveDashboard extends Component
{
    public bool $autoRefresh = true;
    public int $refreshInterval = 30; // seconds
    public array $selectedMetrics = ['products', 'orders', 'users', 'reviews'];
    public string $timeRange = '24h'; // 1h, 24h, 7d, 30d

    public function mount(): void
    {
        if ($this->autoRefresh) {
            $this->dispatch('start-auto-refresh', interval: $this->refreshInterval * 1000);
        }
    }

    public function toggleAutoRefresh(): void
    {
        $this->autoRefresh = !$this->autoRefresh;
        
        if ($this->autoRefresh) {
            $this->dispatch('start-auto-refresh', interval: $this->refreshInterval * 1000);
        } else {
            $this->dispatch('stop-auto-refresh');
        }
    }

    public function updateTimeRange(string $range): void
    {
        $this->timeRange = $range;
        $this->clearCache();
    }

    public function toggleMetric(string $metric): void
    {
        if (in_array($metric, $this->selectedMetrics)) {
            $this->selectedMetrics = array_filter($this->selectedMetrics, fn($m) => $m !== $metric);
        } else {
            $this->selectedMetrics[] = $metric;
        }
    }

    #[Computed(persist: true, seconds: 60)]
    public function realTimeStats(): array
    {
        $cacheKey = "live_dashboard_stats_{$this->timeRange}";
        
        return Cache::remember($cacheKey, 60, function () {
            $timeCondition = $this->getTimeCondition();
            
            return [
                'products' => [
                    'total' => Product::where('is_visible', true)->count(),
                    'new_today' => Product::where('is_visible', true)->where($timeCondition)->count(),
                    'featured' => Product::where('is_featured', true)->where('is_visible', true)->count(),
                    'low_stock' => Product::where('stock_quantity', '<', 10)->where('is_visible', true)->count(),
                ],
                'orders' => [
                    'total' => Order::count(),
                    'today' => Order::where($timeCondition)->count(),
                    'pending' => Order::where('status', 'pending')->count(),
                    'completed' => Order::where('status', 'completed')->count(),
                    'revenue' => Order::where('status', 'completed')->where($timeCondition)->sum('total_amount'),
                ],
                'users' => [
                    'total' => User::count(),
                    'new_today' => User::where($timeCondition)->count(),
                    'active' => User::where('last_activity_at', '>=', now()->subHours(24))->count(),
                ],
                'reviews' => [
                    'total' => Review::where('is_approved', true)->count(),
                    'today' => Review::where('is_approved', true)->where($timeCondition)->count(),
                    'pending' => Review::where('is_approved', false)->count(),
                    'avg_rating' => Review::where('is_approved', true)->avg('rating') ?? 0,
                ],
            ];
        });
    }

    #[Computed(persist: true, seconds: 120)]
    public function liveActivity(): array
    {
        $cacheKey = "live_dashboard_activity_{$this->timeRange}";
        
        return Cache::remember($cacheKey, 120, function () {
            $timeCondition = $this->getTimeCondition();
            
            return [
                'recent_orders' => Order::with(['user'])
                    ->where($timeCondition)
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get()
                    ->map(fn($order) => [
                        'id' => $order->id,
                        'user_name' => $order->user?->name ?? 'Guest',
                        'total' => $order->total_amount,
                        'status' => $order->status,
                        'created_at' => $order->created_at->diffForHumans(),
                    ]),
                'recent_reviews' => Review::with(['product', 'user'])
                    ->where('is_approved', true)
                    ->where($timeCondition)
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get()
                    ->map(fn($review) => [
                        'id' => $review->id,
                        'product_name' => $review->product?->name ?? 'Unknown',
                        'user_name' => $review->user?->name ?? 'Anonymous',
                        'rating' => $review->rating,
                        'created_at' => $review->created_at->diffForHumans(),
                    ]),
                'popular_products' => Product::with(['brand'])
                    ->where('is_visible', true)
                    ->whereHas('reviews')
                    ->withCount('reviews')
                    ->orderBy('reviews_count', 'desc')
                    ->limit(5)
                    ->get()
                    ->map(fn($product) => [
                        'id' => $product->id,
                        'name' => $product->name,
                        'brand' => $product->brand?->name,
                        'reviews_count' => $product->reviews_count,
                        'price' => $product->price,
                    ]),
            ];
        });
    }

    #[Computed(persist: true, seconds: 300)]
    public function performanceMetrics(): array
    {
        $cacheKey = "live_dashboard_performance_{$this->timeRange}";
        
        return Cache::remember($cacheKey, 300, function () {
            return [
                'page_views' => rand(1000, 5000), // Mock data - replace with real analytics
                'bounce_rate' => rand(30, 70),
                'avg_session_duration' => rand(120, 600),
                'conversion_rate' => rand(2, 8),
                'top_pages' => [
                    ['page' => 'Home', 'views' => rand(500, 2000)],
                    ['page' => 'Products', 'views' => rand(300, 1500)],
                    ['page' => 'Categories', 'views' => rand(200, 1000)],
                ],
            ];
        });
    }

    #[On('refresh-dashboard')]
    public function refreshDashboard(): void
    {
        $this->clearCache();
        $this->dispatch('dashboard-refreshed');
    }

    private function getTimeCondition(): array
    {
        return match ($this->timeRange) {
            '1h' => ['created_at', '>=', now()->subHour()],
            '24h' => ['created_at', '>=', now()->subDay()],
            '7d' => ['created_at', '>=', now()->subWeek()],
            '30d' => ['created_at', '>=', now()->subMonth()],
            default => ['created_at', '>=', now()->subDay()],
        };
    }

    private function clearCache(): void
    {
        Cache::forget("live_dashboard_stats_{$this->timeRange}");
        Cache::forget("live_dashboard_activity_{$this->timeRange}");
        Cache::forget("live_dashboard_performance_{$this->timeRange}");
    }

    public function render(): View
    {
        return view('livewire.components.live-dashboard');
    }
}
