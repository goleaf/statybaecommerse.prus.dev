<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use App\Services\Cart\CartService;
use Livewire\Attributes\On;
use Livewire\Component;
use Throwable;

/**
 * ShoppingCartButton
 *
 * Livewire component for ShoppingCartButton with reactive frontend functionality, real-time updates, and user interaction handling.
 *
 * @property int    $cartTotalItems
 * @property string $sessionKey
 * @property mixed  $listeners
 */
final class ShoppingCartButton extends Component
{
    public int $cartTotalItems = 0;

    public string $sessionKey = '';

    /**
     * Initialize the Livewire component with parameters.
     */
    public function mount(): void
    {
        $this->sessionKey = session()->getId();
        $this->cartTotalItems = $this->resolveCartCount();
        $this->dispatch('cart-count-changed', quantity: $this->cartTotalItems);
    }

    /**
     * Handle updateCartCount functionality with proper error handling.
     */
    #[On('cart-updated')]
    #[On('itemAddedToCart')]
    #[On('add-to-cart')]
    public function updateCartCount(): void
    {
        $this->cartTotalItems = $this->resolveCartCount();
        $this->dispatch('cart-count-changed', quantity: $this->cartTotalItems);
    }

    /**
     * Handle resolveCartCount functionality with proper error handling.
     */
    private function resolveCartCount(): int
    {
        $serviceCount = app(CartService::class)->getCount(auth()->id(), $this->sessionKey);

        if ($serviceCount > 0) {
            return $serviceCount;
        }

        if (class_exists(\Darryldecode\Cart\Facades\CartFacade::class)) {
            try {
                return (int) \Darryldecode\Cart\Facades\CartFacade::session($this->sessionKey)->getTotalQuantity();
            } catch (Throwable $e) {
                // fall through to cart service fallback
            }
        }

        return $serviceCount;
    }

    /**
     * Render the Livewire component view with current state.
     */
    public function render()
    {
        return view('livewire.components.shopping-cart-button');
    }
}
