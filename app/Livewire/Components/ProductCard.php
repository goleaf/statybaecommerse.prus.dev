<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use App\Livewire\Concerns\WithCart;
use App\Livewire\Concerns\WithNotifications;
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
    use WithCart {
        addToCart as performAddToCart;
    }
    use WithNotifications;

    public Product $product;

    /**
     * Initialize the Livewire component with parameters.
     */
    public function mount(Product $product): void
    {
        // Optimize relationship loading using Laravel 12.10 relationLoaded dot notation
        if (! $product->relationLoaded('brand') || ! $product->relationLoaded('media')) {
            $product->load(['brand', 'media']);
        }
        $this->product = $product;
    }

    /**
     * Handle addToCart functionality with proper error handling.
     */
    public function addToCart(): void
    {
        $added = $this->performAddToCart($this->product->id);

        if (! $added) {
            return;
        }
    }

    /**
     * Handle quickView functionality with proper error handling.
     */
    public function quickView(): void
    {
        $this->dispatch('open-quick-view', product_id: $this->product->id);
    }

    /**
     * Handle viewProduct functionality with proper error handling.
     */
    public function viewProduct(): void
    {
        $this->redirect(route('product.show', $this->product));
    }

    /**
     * Handle getImageUrlProperty functionality with proper error handling.
     */
    public function getImageUrlProperty(): string
    {
        return $this->product->getFirstMediaUrl('images', 'thumb') ?: product_placeholder_url('thumb');
    }

    /**
     * Handle getCurrentPriceProperty functionality with proper error handling.
     */
    public function getCurrentPriceProperty(): float
    {
        $price = $this->product->sale_price ?? $this->product->price ?? 0.0;

        return (float) $price;
    }

    /**
     * Handle getOriginalPriceProperty functionality with proper error handling.
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
     */
    #[Computed]
    public function isInWishlist(): bool
    {
        if (! auth()->check()) {
            return false;
        }
        $user = auth()->user();
        $wishlist = $user->wishlists()->where('is_default', true)->first();
        if (! $wishlist) {
            return false;
        }

        return $wishlist->hasProduct($this->product->id);
    }

    /**
     * Handle discountPercentage functionality with proper error handling.
     */
    #[Computed]
    public function discountPercentage(): ?int
    {
        // Check for sale_price (if product is on sale)
        if ($this->product->sale_price && $this->product->price) {
            return (int) round(($this->product->price - $this->product->sale_price) / $this->product->price * 100);
        }

        return null;
    }

    /**
     * Handle stockStatus functionality with proper error handling.
     */
    #[Computed]
    public function stockStatus(): string
    {
        if (! $this->product->track_inventory) {
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
     */
    #[Computed]
    public function isOutOfStock(): bool
    {
        return $this->product->track_inventory && $this->product->stock_quantity <= 0;
    }

    /**
     * Handle getListeners functionality with proper error handling.
     */
    protected function getListeners(): array
    {
        return ['wishlist-updated' => '$refresh'];
    }

    /**
     * Render the Livewire component view with current state.
     */
    public function render(): View
    {
        return view('livewire.components.product-card');
    }
}
