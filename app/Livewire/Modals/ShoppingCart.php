<?php

declare(strict_types=1);

namespace App\Livewire\Modals;

use Darryldecode\Cart\CartCollection;
use Darryldecode\Cart\Facades\CartFacade;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Laravelcm\LivewireSlideOvers\SlideOverComponent;

/**
 * ShoppingCart
 *
 * Livewire component for ShoppingCart with reactive frontend functionality, real-time updates, and user interaction handling.
 *
 * @property float $subtotal
 * @property CartCollection $items
 * @property string|null $sessionKey
 */
class ShoppingCart extends SlideOverComponent
{
    public float $subtotal = 0;

    public CartCollection $items;

    public ?string $sessionKey = null;

    /**
     * Handle panelMaxWidth functionality with proper error handling.
     */
    public static function panelMaxWidth(): string
    {
        return 'lg';
    }

    /**
     * Initialize the Livewire component with parameters.
     */
    public function mount(): void
    {
        $sessionKey = session()->getId();
        $this->sessionKey = $sessionKey;
        $this->items = CartFacade::session($sessionKey)->getContent();
        $this->subtotal = CartFacade::session($sessionKey)->getSubTotal();
    }

    /**
     * Handle cartUpdated functionality with proper error handling.
     */
    public function cartUpdated(): void
    {
        $this->items = CartFacade::session($this->sessionKey)->getContent();
        $this->subtotal = CartFacade::session($this->sessionKey)->getSubTotal();
    }

    /**
     * Handle removeToCart functionality with proper error handling.
     */
    public function removeToCart(int $id): void
    {
        CartFacade::session($this->sessionKey)->remove($id);
        Notification::make()->title(__('Cart updated'))->body(__('The product  has been removed from your cart !'))->success()->send();
        $this->dispatch('cartUpdated');
        $this->dispatch('closePanel');
    }

    /**
     * Render the Livewire component view with current state.
     */
    public function render(): View
    {
        return view('livewire.modals.shopping-cart');
    }
}
