<?php

declare (strict_types=1);
namespace App\Livewire\Components;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Livewire\Component;
/**
 * ProductCardDetailed
 * 
 * Livewire component for ProductCardDetailed with reactive frontend functionality, real-time updates, and user interaction handling.
 * 
 * @property Product $product
 * @property bool $showQuickView
 * @property bool $showCompare
 * @property bool $showWishlist
 * @property string $layout
 * @property bool $isInWishlist
 * @property bool $isInComparison
 */
final class ProductCardDetailed extends Component
{
    public Product $product;
    public bool $showQuickView = false;
    public bool $showCompare = true;
    public bool $showWishlist = true;
    public string $layout = 'grid';
    // grid, list, minimal
    public bool $isInWishlist = false;
    public bool $isInComparison = false;
    /**
     * Initialize the Livewire component with parameters.
     * @param Product $product
     * @return void
     */
    public function mount(Product $product): void
    {
        // Optimize relationship loading using Laravel 12.10 relationLoaded dot notation
        if (!$product->relationLoaded('brand') || !$product->relationLoaded('media') || !$product->relationLoaded('categories')) {
            $product->load(['brand', 'media', 'categories']);
        }
        $this->product = $product;
        $this->checkWishlistStatus();
        $this->checkComparisonStatus();
    }
    /**
     * Handle addToCart functionality with proper error handling.
     * @return void
     */
    public function addToCart(): void
    {
        $this->dispatch('add-to-cart', productId: $this->product->id, quantity: 1);
        // Track analytics
        if (class_exists(\App\Models\AnalyticsEvent::class)) {
            \App\Models\AnalyticsEvent::create(['event_type' => 'add_to_cart', 'user_id' => auth()->id(), 'session_id' => session()->getId(), 'properties' => ['product_id' => $this->product->id, 'product_name' => $this->product->name, 'product_price' => $this->product->price], 'created_at' => now()]);
        }
        $this->dispatch('notify', ['type' => 'success', 'message' => __('translations.product_added_to_cart', ['name' => $this->product->name])]);
    }
    /**
     * Handle viewProduct functionality with proper error handling.
     */
    public function viewProduct()
    {
        // Track analytics
        if (class_exists(\App\Models\AnalyticsEvent::class)) {
            \App\Models\AnalyticsEvent::create(['event_type' => 'product_view', 'user_id' => auth()->id(), 'session_id' => session()->getId(), 'properties' => ['product_id' => $this->product->id, 'product_name' => $this->product->name, 'view_type' => 'card_click'], 'created_at' => now()]);
        }
        return $this->redirect(route('product.show', $this->product));
    }
    /**
     * Handle toggleWishlist functionality with proper error handling.
     * @return void
     */
    public function toggleWishlist(): void
    {
        if (!auth()->check()) {
            $this->dispatch('notify', ['type' => 'error', 'message' => __('translations.login_required_for_wishlist')]);
            return;
        }
        $this->dispatch('add-to-wishlist', productId: $this->product->id);
        $this->dispatch('notify', ['type' => 'success', 'message' => __('translations.product_added_to_wishlist', ['name' => $this->product->name])]);
    }
    /**
     * Handle toggleComparison functionality with proper error handling.
     * @return void
     */
    public function toggleComparison(): void
    {
        $this->dispatch('add-to-comparison', productId: $this->product->id);
        $this->dispatch('notify', ['type' => 'success', 'message' => __('translations.product_added_to_comparison', ['name' => $this->product->name])]);
    }
    /**
     * Handle quickView functionality with proper error handling.
     * @return void
     */
    public function quickView(): void
    {
        $this->dispatch('product-quick-view', productId: $this->product->id);
    }
    /**
     * Handle checkWishlistStatus functionality with proper error handling.
     * @return void
     */
    private function checkWishlistStatus(): void
    {
        if (!auth()->check()) {
            $this->isInWishlist = false;
            return;
        }
        $this->isInWishlist = false;
        // Simplified for now
    }
    /**
     * Handle checkComparisonStatus functionality with proper error handling.
     * @return void
     */
    private function checkComparisonStatus(): void
    {
        $this->isInComparison = false;
        // Simplified for now
    }
    /**
     * Render the Livewire component view with current state.
     * @return View
     */
    public function render(): View
    {
        return view('livewire.components.product-card-detailed');
    }
}