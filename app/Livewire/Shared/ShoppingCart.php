<?php

declare(strict_types=1);

namespace App\Livewire\Shared;

use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * ShoppingCart
 *
 * Livewire component for ShoppingCart with reactive frontend functionality, real-time updates, and user interaction handling.
 *
 * @property bool $isOpen
 */
final class ShoppingCart extends Component
{
    public bool $isOpen = false;

    /**
     * Initialize the Livewire component with parameters.
     */
    public function mount(): void
    {
        // Initialize cart
    }

    /**
     * Handle addToCart functionality with proper error handling.
     */
    #[On('add-to-cart')]
    public function addToCart(int $productId, int $quantity = 1): void
    {
        $product = Product::findOrFail($productId);
        $sessionId = Session::getId();
        $cartItem = CartItem::where('session_id', $sessionId)->where('product_id', $productId)->first();
        if ($cartItem) {
            $newQuantity = $cartItem->quantity + $quantity;
            $unitPrice = (float) ($cartItem->unit_price ?? $product->sale_price ?? $product->price);
            $cartItem->update(['quantity' => $newQuantity, 'unit_price' => $unitPrice, 'total_price' => round($unitPrice * $newQuantity, 2)]);
        } else {
            $unitPrice = (float) ($product->sale_price ?? $product->price);
            CartItem::create(['session_id' => $sessionId, 'user_id' => auth()->id(), 'product_id' => $productId, 'quantity' => $quantity, 'unit_price' => $unitPrice, 'total_price' => round($unitPrice * $quantity, 2), 'product_snapshot' => ['name' => $product->name, 'price' => $unitPrice, 'sku' => $product->sku ?? null]]);
        }
        $this->dispatch('cart-updated');
    }

    /**
     * Handle updateQuantity functionality with proper error handling.
     */
    public function updateQuantity(int $cartItemId, int $quantity): void
    {
        if ($quantity <= 0) {
            $this->removeItem($cartItemId);

            return;
        }
        CartItem::where('id', $cartItemId)->where('session_id', Session::getId())->update(['quantity' => $quantity]);
        $this->dispatch('cart-updated');
    }

    /**
     * Handle removeItem functionality with proper error handling.
     */
    public function removeItem(int $cartItemId): void
    {
        CartItem::where('id', $cartItemId)->where('session_id', Session::getId())->delete();
        $this->dispatch('cart-updated');
    }

    /**
     * Handle clearCart functionality with proper error handling.
     */
    public function clearCart(): void
    {
        CartItem::where('session_id', Session::getId())->delete();
        $this->dispatch('cart-updated');
    }

    /**
     * Handle toggleCart functionality with proper error handling.
     */
    public function toggleCart(): void
    {
        $this->isOpen = ! $this->isOpen;
    }

    /**
     * Handle refreshCart functionality with proper error handling.
     */
    #[On('cart-updated')]
    public function refreshCart(): void
    {
        // This will trigger a re-render
    }

    /**
     * Handle getCartItemsProperty functionality with proper error handling.
     */
    public function getCartItemsProperty()
    {
        return CartItem::with(['product', 'product.media'])->where('session_id', Session::getId())->get();
    }

    /**
     * Handle getCartTotalProperty functionality with proper error handling.
     */
    public function getCartTotalProperty(): float
    {
        return $this->cartItems->sum(function (CartItem $item) {
            return (float) $item->total_price;
        });
    }

    /**
     * Handle getCartCountProperty functionality with proper error handling.
     */
    public function getCartCountProperty(): int
    {
        return $this->cartItems->sum('quantity');
    }

    /**
     * Render the Livewire component view with current state.
     */
    public function render(): View
    {
        return view('livewire.shared.shopping-cart');
    }
}
