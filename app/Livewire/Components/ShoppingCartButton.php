<?php

declare (strict_types=1);
namespace App\Livewire\Components;

use Livewire\Component;
/**
 * ShoppingCartButton
 * 
 * Livewire component for ShoppingCartButton with reactive frontend functionality, real-time updates, and user interaction handling.
 * 
 * @property int $cartTotalItems
 * @property string $sessionKey
 * @property mixed $listeners
 */
final class ShoppingCartButton extends Component
{
    public int $cartTotalItems = 0;
    public string $sessionKey = '';
    protected $listeners = ['cartUpdated' => 'updateCartCount'];
    /**
     * Initialize the Livewire component with parameters.
     * @return void
     */
    public function mount(): void
    {
        $this->sessionKey = session()->getId();
        $this->cartTotalItems = $this->resolveCartCount();
    }
    /**
     * Handle updateCartCount functionality with proper error handling.
     * @return void
     */
    public function updateCartCount(): void
    {
        $this->cartTotalItems = $this->resolveCartCount();
    }
    /**
     * Handle resolveCartCount functionality with proper error handling.
     * @return int
     */
    private function resolveCartCount(): int
    {
        if (class_exists(\Darryldecode\Cart\Facades\CartFacade::class)) {
            try {
                return (int) \Darryldecode\Cart\Facades\CartFacade::session($this->sessionKey)->getTotalQuantity();
            } catch (\Throwable $e) {
                // fall through to session-based fallback
            }
        }
        $cart = (array) session('cart', []);
        return array_sum(array_map(static function ($item) {
            return (int) ($item['quantity'] ?? 0);
        }, $cart));
    }
    /**
     * Render the Livewire component view with current state.
     */
    public function render()
    {
        return view('livewire.components.shopping-cart-button');
    }
}