<?php

declare (strict_types=1);
namespace App\Livewire\Components;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
/**
 * ProductAnalytics
 * 
 * Livewire component for ProductAnalytics with reactive frontend functionality, real-time updates, and user interaction handling.
 * 
 * @property int $productId
 */
class ProductAnalytics extends Component
{
    public int $productId;
    /**
     * Initialize the Livewire component with parameters.
     * @param int $productId
     * @return void
     */
    public function mount(int $productId): void
    {
        $this->productId = $productId;
    }
    /**
     * Handle product functionality with proper error handling.
     * @return Product|null
     */
    #[Computed]
    public function product(): ?Product
    {
        return Product::with(['brand', 'categories', 'media', 'reviews'])->find($this->productId);
    }
    /**
     * Handle reviewStats functionality with proper error handling.
     * @return array
     */
    #[Computed]
    public function reviewStats(): array
    {
        if (!$this->product) {
            return ['total_reviews' => 0, 'average_rating' => 0.0, 'rating_distribution' => []];
        }
        $reviews = $this->product->reviews()->where('is_approved', true)->get();
        return ['total_reviews' => $reviews->count(), 'average_rating' => $reviews->avg('rating') ?? 0.0, 'rating_distribution' => $reviews->groupBy('rating')->map->count()->toArray()];
    }
    /**
     * Handle productPerformance functionality with proper error handling.
     * @return array
     */
    #[Computed(persist: true)]
    public function productPerformance(): array
    {
        if (!$this->product) {
            return ['views_count' => 0, 'cart_additions' => 0, 'conversion_rate' => 0.0];
        }
        // This expensive calculation will be cached across requests
        $viewsCount = \App\Models\ProductHistory::where('product_id', $this->productId)->where('action', 'viewed')->count();
        $cartAdditions = \App\Models\ProductHistory::where('product_id', $this->productId)->where('action', 'added_to_cart')->count();
        $conversionRate = $viewsCount > 0 ? $cartAdditions / $viewsCount * 100 : 0.0;
        return ['views_count' => $viewsCount, 'cart_additions' => $cartAdditions, 'conversion_rate' => round($conversionRate, 2)];
    }
    /**
     * Handle topSellingProducts functionality with proper error handling.
     * @return array
     */
    #[Computed(cache: true, key: 'top-selling-products')]
    public function topSellingProducts(): array
    {
        // This will be cached globally across all instances
        return Product::query()->where('is_visible', true)->whereHas('histories', function ($query) {
            $query->where('action', 'added_to_cart');
        })->withCount(['histories as cart_count' => function ($query) {
            $query->where('action', 'added_to_cart');
        }])->orderByDesc('cart_count')->limit(5)->get()->map(function ($product) {
            return ['id' => $product->id, 'name' => $product->name, 'cart_count' => $product->cart_count, 'image' => $product->getFirstMediaUrl('images')];
        })->toArray();
    }
    /**
     * Handle relatedProducts functionality with proper error handling.
     * @return array
     */
    #[Computed]
    public function relatedProducts(): array
    {
        if (!$this->product) {
            return [];
        }
        // Get products from the same categories
        $categoryIds = $this->product->categories->pluck('id');
        return Product::query()->where('is_visible', true)->where('id', '!=', $this->productId)->whereHas('categories', function ($query) use ($categoryIds) {
            $query->whereIn('categories.id', $categoryIds);
        })->with(['media'])->limit(4)->get()->map(function ($product) {
            return ['id' => $product->id, 'name' => $product->name, 'price' => $product->price, 'image' => $product->getFirstMediaUrl('images'), 'slug' => $product->slug];
        })->toArray();
    }
    /**
     * Render the Livewire component view with current state.
     * @return View
     */
    public function render(): View
    {
        return view('livewire.components.product-analytics', ['product' => $this->product, 'reviewStats' => $this->reviewStats, 'productPerformance' => $this->productPerformance, 'topSellingProducts' => $this->topSellingProducts, 'relatedProducts' => $this->relatedProducts]);
    }
}