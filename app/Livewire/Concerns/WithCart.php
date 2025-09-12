<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

use App\Models\Product;

trait WithCart
{
    public function addToCart(int $productId, int $quantity = 1): void
    {
        $product = Product::findOrFail($productId);

        if ($product->stock_quantity < $quantity) {
            $this->notifyError(__('Not enough stock available'));

            return;
        }

        $cartItems = session()->get('cart', []);

        if (isset($cartItems[$productId])) {
            $cartItems[$productId]['quantity'] += $quantity;
        } else {
            $cartItems[$productId] = [
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => $quantity,
                'image' => $product->getFirstMediaUrl('images'),
                'sku' => $product->sku,
            ];
        }

        session()->put('cart', $cartItems);

        $this->dispatch('cart-updated');
        $this->notifySuccess(__('Product added to cart'));
    }

    public function removeFromCart(int $productId): void
    {
        $cartItems = session()->get('cart', []);

        if (isset($cartItems[$productId])) {
            unset($cartItems[$productId]);
            session()->put('cart', $cartItems);

            $this->dispatch('cart-updated');
            $this->notifySuccess(__('Product removed from cart'));
        }
    }

    public function updateCartQuantity(int $productId, int $quantity): void
    {
        if ($quantity <= 0) {
            $this->removeFromCart($productId);

            return;
        }

        $product = Product::find($productId);
        if (! $product || $product->stock_quantity < $quantity) {
            $this->notifyError(__('Not enough stock available'));

            return;
        }

        $cartItems = session()->get('cart', []);

        if (isset($cartItems[$productId])) {
            $cartItems[$productId]['quantity'] = $quantity;
            session()->put('cart', $cartItems);

            $this->dispatch('cart-updated');
        }
    }

    public function getCartCount(): int
    {
        $cartItems = session()->get('cart', []);

        return array_sum(array_column($cartItems, 'quantity'));
    }

    public function getCartTotal(): float
    {
        $cartItems = session()->get('cart', []);
        $total = 0;

        foreach ($cartItems as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        return $total;
    }

    public function clearCart(): void
    {
        session()->forget('cart');
        $this->dispatch('cart-updated');
        $this->notifySuccess(__('Cart cleared'));
    }
}
