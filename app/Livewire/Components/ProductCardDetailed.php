<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Livewire\Component;

final class ProductCardDetailed extends Component
{
    public Product $product;

    public bool $showQuickView = false;

    public bool $showCompare = true;

    public bool $showWishlist = true;

    public string $layout = 'grid'; // grid, list, minimal

    public bool $isInWishlist = false;

    public bool $isInComparison = false;

    public function mount(Product $product): void
    {
        $this->product = $product->load(['brand', 'media', 'categories']);
        $this->checkWishlistStatus();
        $this->checkComparisonStatus();
    }

    public function addToCart(): void
    {
        $this->dispatch('add-to-cart', productId: $this->product->id, quantity: 1);

        // Track analytics
        if (class_exists(\App\Models\AnalyticsEvent::class)) {
            \App\Models\AnalyticsEvent::create([
                'event_type' => 'add_to_cart',
                'user_id' => auth()->id(),
                'session_id' => session()->getId(),
                'properties' => [
                    'product_id' => $this->product->id,
                    'product_name' => $this->product->name,
                    'product_price' => $this->product->price,
                ],
                'created_at' => now(),
            ]);
        }

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => __('translations.product_added_to_cart', ['name' => $this->product->name]),
        ]);
    }

    public function viewProduct()
    {
        // Track analytics
        if (class_exists(\App\Models\AnalyticsEvent::class)) {
            \App\Models\AnalyticsEvent::create([
                'event_type' => 'product_view',
                'user_id' => auth()->id(),
                'session_id' => session()->getId(),
                'properties' => [
                    'product_id' => $this->product->id,
                    'product_name' => $this->product->name,
                    'view_type' => 'card_click',
                ],
                'created_at' => now(),
            ]);
        }

        return $this->redirect(route('product.show', $this->product));
    }

    public function toggleWishlist(): void
    {
        if (! auth()->check()) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => __('translations.login_required_for_wishlist'),
            ]);
            return;
        }

        $this->dispatch('add-to-wishlist', productId: $this->product->id);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => __('translations.product_added_to_wishlist', ['name' => $this->product->name]),
        ]);
    }

    public function toggleComparison(): void
    {
        $this->dispatch('add-to-comparison', productId: $this->product->id);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => __('translations.product_added_to_comparison', ['name' => $this->product->name]),
        ]);
    }

    public function quickView(): void
    {
        $this->dispatch('product-quick-view', productId: $this->product->id);
    }

    private function checkWishlistStatus(): void
    {
        if (! auth()->check()) {
            $this->isInWishlist = false;
            return;
        }

        $this->isInWishlist = false; // Simplified for now
    }

    private function checkComparisonStatus(): void
    {
        $this->isInComparison = false; // Simplified for now
    }

    public function render(): View
    {
        return view('livewire.components.product-card-detailed');
    }
}
