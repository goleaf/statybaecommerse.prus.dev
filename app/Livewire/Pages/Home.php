<?php

declare (strict_types=1);
namespace App\Livewire\Pages;

use App\Livewire\Concerns\WithCart;
use App\Livewire\Concerns\WithNotifications;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Component;
/**
 * Home
 * 
 * Livewire component for Home with reactive frontend functionality, real-time updates, and user interaction handling.
 * 
 */
final class Home extends Component
{
    use WithCart, WithNotifications;
    /**
     * Handle featuredProducts functionality with proper error handling.
     * @return Collection
     */
    #[Computed]
    public function featuredProducts(): Collection
    {
        return Product::query()->with(['brand', 'categories', 'media'])->where('is_visible', true)->where('is_featured', true)->whereNotNull('published_at')->where('published_at', '<=', now())->orderBy('created_at', 'desc')->limit(8)->get()->skipWhile(function ($product) {
            // Skip products that are not properly configured for homepage display
            return empty($product->name) || !$product->is_visible || $product->price <= 0 || empty($product->slug) || !$product->getFirstMediaUrl('images');
        });
    }
    /**
     * Handle latestProducts functionality with proper error handling.
     * @return Collection
     */
    #[Computed]
    public function latestProducts(): Collection
    {
        return Product::query()->with(['brand', 'categories', 'media'])->where('is_visible', true)->whereNotNull('published_at')->where('published_at', '<=', now())->orderBy('created_at', 'desc')->limit(8)->get()->skipWhile(function ($product) {
            // Skip products that are not properly configured for homepage display
            return empty($product->name) || !$product->is_visible || $product->price <= 0 || empty($product->slug) || !$product->getFirstMediaUrl('images');
        });
    }
    /**
     * Handle popularProducts functionality with proper error handling.
     * @return Collection
     */
    #[Computed]
    public function popularProducts(): Collection
    {
        return Product::query()->with(['brand', 'categories', 'media'])->where('is_visible', true)->whereNotNull('published_at')->where('published_at', '<=', now())->whereHas('reviews')->withCount('reviews')->orderBy('reviews_count', 'desc')->limit(8)->get()->skipWhile(function ($product) {
            // Skip products that are not properly configured for homepage display
            return empty($product->name) || !$product->is_visible || $product->price <= 0 || empty($product->slug) || !$product->getFirstMediaUrl('images') || $product->reviews_count <= 0;
        });
    }
    /**
     * Handle featuredCategories functionality with proper error handling.
     * @return Collection
     */
    #[Computed]
    public function featuredCategories(): Collection
    {
        return Category::query()->with(['media'])->where('is_visible', true)->where('is_featured', true)->whereHas('products', function ($query) {
            $query->where('is_visible', true);
        })->withCount(['products' => function ($query) {
            $query->where('is_visible', true);
        }])->orderBy('id')->limit(6)->get()->skipWhile(function ($category) {
            // Skip categories that are not properly configured for homepage display
            return empty($category->name) || !$category->is_visible || empty($category->slug) || $category->products_count <= 0 || !$category->getFirstMediaUrl('images');
        });
    }
    /**
     * Handle featuredBrands functionality with proper error handling.
     * @return Collection
     */
    #[Computed]
    public function featuredBrands(): Collection
    {
        return Brand::query()->with(['media'])->where('is_enabled', true)->where('is_featured', true)->whereHas('products', function ($query) {
            $query->where('is_visible', true);
        })->withCount(['products' => function ($query) {
            $query->where('is_visible', true);
        }])->orderBy('sort_order')->limit(8)->get()->skipWhile(function ($brand) {
            // Skip brands that are not properly configured for homepage display
            return empty($brand->name) || !$brand->is_enabled || empty($brand->slug) || $brand->products_count <= 0 || !$brand->getFirstMediaUrl('logo');
        });
    }
    /**
     * Handle latestReviews functionality with proper error handling.
     * @return Collection
     */
    #[Computed]
    public function latestReviews(): Collection
    {
        return Review::query()->with(['product', 'user'])->where('is_approved', true)->whereHas('product', function ($query) {
            $query->where('is_visible', true);
        })->orderBy('created_at', 'desc')->limit(6)->get()->skipWhile(function ($review) {
            // Skip reviews that are not properly configured for homepage display
            return empty($review->title) || empty($review->comment) || !$review->is_approved || $review->rating <= 0 || empty($review->product);
        });
    }
    /**
     * Handle stats functionality with proper error handling.
     * @return array
     */
    #[Computed]
    public function stats(): array
    {
        return ['products_count' => Product::where('is_visible', true)->count(), 'categories_count' => Category::where('is_visible', true)->count(), 'brands_count' => Brand::where('is_enabled', true)->count(), 'reviews_count' => Review::where('is_approved', true)->count(), 'avg_rating' => Review::where('is_approved', true)->avg('rating') ?? 0];
    }
    /**
     * Handle addToCart functionality with proper error handling.
     * @param int $productId
     * @return void
     */
    public function addToCart(int $productId): void
    {
        $product = Product::find($productId);
        if (!$product || !$product->is_visible) {
            $this->notifyError(__('Product not found or not available'));
            return;
        }
        if ($product->stock_quantity <= 0) {
            $this->notifyError(__('Product is out of stock'));
            return;
        }
        $cartItems = session()->get('cart', []);
        if (isset($cartItems[$productId])) {
            $cartItems[$productId]['quantity']++;
        } else {
            $cartItems[$productId] = ['name' => $product->name, 'price' => $product->price, 'quantity' => 1, 'image' => $product->getFirstMediaUrl('images'), 'sku' => $product->sku];
        }
        session()->put('cart', $cartItems);
        $this->dispatch('cart-updated');
        $this->notifySuccess(__('Product added to cart'));
    }
    /**
     * Handle liveAnalytics functionality with proper error handling.
     * @return array
     */
    #[Computed(persist: true, seconds: 300)]
    public function liveAnalytics(): array
    {
        return Cache::remember('home_live_analytics', 300, function () {
            return [
                'online_users' => rand(50, 200),
                // Mock data - replace with real analytics
                'page_views_today' => rand(1000, 5000),
                'conversion_rate' => rand(2, 8),
                'avg_session_duration' => rand(120, 600),
                'bounce_rate' => rand(30, 70),
                'top_products' => Product::where('is_visible', true)->whereHas('reviews')->withCount('reviews')->orderBy('reviews_count', 'desc')->limit(5)->get()->map(fn($product) => ['id' => $product->id, 'name' => $product->name, 'views' => rand(100, 1000), 'conversions' => rand(5, 50)]),
            ];
        });
    }
    /**
     * Handle realTimeActivity functionality with proper error handling.
     * @return array
     */
    #[Computed(persist: true, seconds: 180)]
    public function realTimeActivity(): array
    {
        return Cache::remember('home_real_time_activity', 180, function () {
            return ['recent_orders' => \App\Models\Order::with(['user'])->where('created_at', '>=', now()->subHours(24))->orderBy('created_at', 'desc')->limit(5)->get()->map(fn($order) => ['id' => $order->id, 'user_name' => $order->user?->name ?? 'Guest', 'total' => $order->total_amount, 'created_at' => $order->created_at->diffForHumans()]), 'recent_reviews' => Review::with(['product', 'user'])->where('is_approved', true)->where('created_at', '>=', now()->subHours(24))->orderBy('created_at', 'desc')->limit(5)->get()->map(fn($review) => ['id' => $review->id, 'product_name' => $review->product?->name ?? 'Unknown', 'user_name' => $review->user?->name ?? 'Anonymous', 'rating' => $review->rating, 'created_at' => $review->created_at->diffForHumans()])];
        });
    }
    /**
     * Render the Livewire component view with current state.
     */
    public function render()
    {
        return view('livewire.pages.home', ['featuredProducts' => $this->featuredProducts, 'latestProducts' => $this->latestProducts, 'popularProducts' => $this->popularProducts, 'featuredCategories' => $this->featuredCategories, 'featuredBrands' => $this->featuredBrands, 'latestReviews' => $this->latestReviews, 'stats' => $this->stats, 'liveAnalytics' => $this->liveAnalytics, 'realTimeActivity' => $this->realTimeActivity])->layout('components.layouts.base', ['title' => __('Home') . ' - ' . config('app.name')]);
    }
}