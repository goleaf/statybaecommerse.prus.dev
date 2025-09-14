<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Livewire\Component;

final class ProductCardDetailed extends Component
{
    public Product $product;

    public function mount(Product $product): void
    {
        $this->product = $product->load(['brand', 'media', 'categories']);
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

    public function addToWishlist(): void
    {
        $this->dispatch('add-to-wishlist', productId: $this->product->id);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => __('translations.product_added_to_wishlist', ['name' => $this->product->name]),
        ]);
    }

    public function addToComparison(): void
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

    public function render(): View
    {
        return view('livewire.components.product-card-detailed');
    }
}
