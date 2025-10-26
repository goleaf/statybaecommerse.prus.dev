<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

use App\Models\Product;
use App\Services\Cart\CartService;

/**
 * WithCart
 *
 * Trait providing reusable functionality across multiple classes.
 */
trait WithCart
{
    public function addToCart(int $productId, int $quantity = 1): void
    {
        $product = Product::query()->findOrFail($productId);

        if ($product->stock_quantity < $quantity) {
            $this->notifyError(__('Not enough stock available'));

            return;
        }

        $this->persistCartItem($product, $quantity);
    }

    protected function persistCartItem(Product $product, int $quantity = 1): void
    {
        $cartItems = session()->get('cart', []);

        if (isset($cartItems[$product->getKey()])) {
            $cartItems[$product->getKey()]['quantity'] += $quantity;
        } else {
            $cartItems[$product->getKey()] = [
                'name'     => $product->name,
                'price'    => $product->price,
                'quantity' => $quantity,
                'image'    => $product->getFirstMediaUrl('images'),
                'sku'      => $product->sku,
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
        return app(CartService::class)->getSessionCount();
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
