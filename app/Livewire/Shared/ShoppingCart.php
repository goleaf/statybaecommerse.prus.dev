<?php

declare(strict_types=1);

namespace App\Livewire\Shared;

use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\On;
use Livewire\Component;

final class ShoppingCart extends Component
{
    public bool $isOpen = false;

    public function mount(): void
    {
        // Initialize cart
    }

    #[On('add-to-cart')]
    public function addToCart(int $productId, int $quantity = 1): void
    {
        $product = Product::findOrFail($productId);
        $sessionId = Session::getId();

        $cartItem = CartItem::where('session_id', $sessionId)
            ->where('product_id', $productId)
            ->first();

        if ($cartItem) {
            $newQuantity = $cartItem->quantity + $quantity;
            $unitPrice = (float) ($cartItem->unit_price ?? ($product->sale_price ?? $product->price));
            $cartItem->update([
                'quantity' => $newQuantity,
                'unit_price' => $unitPrice,
                'total_price' => round($unitPrice * $newQuantity, 2),
            ]);
        } else {
            $unitPrice = (float) ($product->sale_price ?? $product->price);
            CartItem::create([
                'session_id' => $sessionId,
                'user_id' => auth()->id(),
                'product_id' => $productId,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_price' => round($unitPrice * $quantity, 2),
                'product_snapshot' => [
                    'name' => $product->name,
                    'price' => $unitPrice,
                    'sku' => $product->sku ?? null,
                ],
            ]);
        }

        $this->dispatch('cart-updated');
    }

    public function updateQuantity(int $cartItemId, int $quantity): void
    {
        if ($quantity <= 0) {
            $this->removeItem($cartItemId);

            return;
        }

        CartItem::where('id', $cartItemId)
            ->where('session_id', Session::getId())
            ->update(['quantity' => $quantity]);

        $this->dispatch('cart-updated');
    }

    public function removeItem(int $cartItemId): void
    {
        CartItem::where('id', $cartItemId)
            ->where('session_id', Session::getId())
            ->delete();

        $this->dispatch('cart-updated');
    }

    public function clearCart(): void
    {
        CartItem::where('session_id', Session::getId())->delete();
        $this->dispatch('cart-updated');
    }

    public function toggleCart(): void
    {
        $this->isOpen = ! $this->isOpen;
    }

    #[On('cart-updated')]
    public function refreshCart(): void
    {
        // This will trigger a re-render
    }

    public function getCartItemsProperty()
    {
        return CartItem::with(['product', 'product.media'])
            ->where('session_id', Session::getId())
            ->get();
    }

    public function getCartTotalProperty(): float
    {
        return $this->cartItems->sum(function (CartItem $item) {
            return (float) $item->total_price;
        });
    }

    public function getCartCountProperty(): int
    {
        return $this->cartItems->sum('quantity');
    }

    public function render(): View
    {
        return view('livewire.shared.shopping-cart');
    }
}
