<?php

declare (strict_types=1);
namespace App\Livewire\Components;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
/**
 * ProductCard
 * 
 * Livewire component for ProductCard with reactive frontend functionality, real-time updates, and user interaction handling.
 * 
 * @property Product $product
 */
final class ProductCard extends Component
{
    public Product $product;
    /**
     * Initialize the Livewire component with parameters.
     * @param Product $product
     * @return void
     */
    public function mount(Product $product): void
    {
        // Optimize relationship loading using Laravel 12.10 relationLoaded dot notation
        if (!$product->relationLoaded('brand') || !$product->relationLoaded('media')) {
            $product->load(['brand', 'media']);
        }
        $this->product = $product;
    }
    /**
     * Handle addToCart functionality with proper error handling.
     * @return void
     */
    public function addToCart(): void
    {
        $this->dispatch('add-to-cart', productId: $this->product->id, quantity: 1);
        // Track analytics
        \App\Models\AnalyticsEvent::create(['event_type' => 'add_to_cart', 'user_id' => auth()->id(), 'session_id' => session()->getId(), 'properties' => ['product_id' => $this->product->id, 'product_name' => $this->product->name, 'product_price' => $this->product->price], 'created_at' => now()]);
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Produktas pridėtas į krepšelį!']);
    }
    /**
     * Handle addToWishlist functionality with proper error handling.
     * @return void
     */
    public function addToWishlist(): void
    {
        $this->dispatch('add-to-wishlist', productId: $this->product->id);
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Produktas pridėtas į pageidavimų sąrašą!']);
    }
    /**
     * Handle toggleWishlist functionality with proper error handling.
     * @return void
     */
    public function toggleWishlist(): void
    {
        if (!auth()->check()) {
            $this->dispatch('notify', ['type' => 'warning', 'message' => 'Norėdami pridėti produktą į pageidavimų sąrašą, turite prisijungti.']);
            return;
        }
        $user = auth()->user();
        $wishlist = $user->wishlists()->where('is_default', true)->first();
        if (!$wishlist) {
            $wishlist = $user->wishlists()->create(['name' => 'My Wishlist', 'is_default' => true, 'is_public' => false]);
        }
        if ($wishlist->hasProduct($this->product->id)) {
            $wishlist->removeProduct($this->product->id);
            $message = 'Produktas pašalintas iš pageidavimų sąrašo!';
        } else {
            $wishlist->addProduct($this->product->id);
            $message = 'Produktas pridėtas į pageidavimų sąrašą!';
        }
        $this->dispatch('wishlist-updated');
        $this->dispatch('notify', ['type' => 'success', 'message' => $message]);
    }
    /**
     * Handle toggleComparison functionality with proper error handling.
     * @return void
     */
    public function toggleComparison(): void
    {
        $sessionId = session()->getId();
        $existing = \App\Models\ProductComparison::where('session_id', $sessionId)->where('product_id', $this->product->id)->first();
        if ($existing) {
            $existing->delete();
            $message = 'Produktas pašalintas iš palyginimo!';
        } else {
            \App\Models\ProductComparison::create(['session_id' => $sessionId, 'product_id' => $this->product->id]);
            $message = 'Produktas pridėtas į palyginimą!';
        }
        $this->dispatch('comparison-updated');
        $this->dispatch('notify', ['type' => 'success', 'message' => $message]);
    }
    /**
     * Handle quickView functionality with proper error handling.
     * @return void
     */
    public function quickView(): void
    {
        $this->dispatch('open-quick-view', product_id: $this->product->id);
        // Track analytics
        \App\Models\AnalyticsEvent::create(['event_type' => 'product_view', 'user_id' => auth()->id(), 'session_id' => session()->getId(), 'properties' => ['product_id' => $this->product->id, 'product_name' => $this->product->name, 'product_price' => $this->product->price, 'view_type' => 'quick_view'], 'created_at' => now()]);
    }
    /**
     * Handle viewProduct functionality with proper error handling.
     * @return void
     */
    public function viewProduct(): void
    {
        // Track analytics
        \App\Models\AnalyticsEvent::create(['event_type' => 'product_view', 'user_id' => auth()->id(), 'session_id' => session()->getId(), 'properties' => ['product_id' => $this->product->id, 'product_name' => $this->product->name, 'product_price' => $this->product->price, 'view_type' => 'full_page'], 'created_at' => now()]);
        $this->redirect(route('product.show', $this->product));
    }
    /**
     * Handle getImageUrlProperty functionality with proper error handling.
     * @return string
     */
    public function getImageUrlProperty(): string
    {
        return $this->product->getFirstMediaUrl('images', 'thumb') ?: asset('images/placeholder-product.png');
    }
    /**
     * Handle getCurrentPriceProperty functionality with proper error handling.
     * @return float
     */
    public function getCurrentPriceProperty(): float
    {
        $price = $this->product->sale_price ?? $this->product->price ?? 0.0;
        return (float) $price;
    }
    /**
     * Handle getOriginalPriceProperty functionality with proper error handling.
     * @return float|null
     */
    public function getOriginalPriceProperty(): ?float
    {
        if ($this->product->sale_price) {
            return (float) $this->product->price;
        }
        return null;
    }
    /**
     * Handle isInWishlist functionality with proper error handling.
     * @return bool
     */
    #[Computed]
    public function isInWishlist(): bool
    {
        if (!auth()->check()) {
            return false;
        }
        $user = auth()->user();
        $wishlist = $user->wishlists()->where('is_default', true)->first();
        if (!$wishlist) {
            return false;
        }
        return $wishlist->hasProduct($this->product->id);
    }
    /**
     * Handle isInComparison functionality with proper error handling.
     * @return bool
     */
    #[Computed]
    public function isInComparison(): bool
    {
        $sessionId = session()->getId();
        return \App\Models\ProductComparison::where('session_id', $sessionId)->where('product_id', $this->product->id)->exists();
    }
    /**
     * Handle discountPercentage functionality with proper error handling.
     * @return int|null
     */
    #[Computed]
    public function discountPercentage(): ?int
    {
        // Check for sale_price first (if product is on sale)
        if ($this->product->sale_price && $this->product->price) {
            return (int) round(($this->product->price - $this->product->sale_price) / $this->product->price * 100);
        }
        // Check for compare_price (regular price vs compare price)
        if ($this->product->compare_price && $this->product->price) {
            return (int) round(($this->product->compare_price - $this->product->price) / $this->product->compare_price * 100);
        }
        return null;
    }
    /**
     * Handle stockStatus functionality with proper error handling.
     * @return string
     */
    #[Computed]
    public function stockStatus(): string
    {
        if (!$this->product->track_inventory) {
            return __('translations.in_stock');
        }
        if ($this->product->stock_quantity <= 0) {
            return __('translations.out_of_stock');
        }
        if ($this->product->low_stock_threshold && $this->product->stock_quantity <= $this->product->low_stock_threshold) {
            return $this->product->stock_quantity . ' ' . __('translations.left');
        }
        return __('translations.in_stock');
    }
    /**
     * Handle isOutOfStock functionality with proper error handling.
     * @return bool
     */
    #[Computed]
    public function isOutOfStock(): bool
    {
        return $this->product->track_inventory && $this->product->stock_quantity <= 0;
    }
    /**
     * Handle getListeners functionality with proper error handling.
     * @return array
     */
    protected function getListeners(): array
    {
        return ['wishlist-updated' => '$refresh', 'comparison-updated' => '$refresh'];
    }
    /**
     * Render the Livewire component view with current state.
     * @return View
     */
    public function render(): View
    {
        return view('livewire.components.product-card');
    }
}