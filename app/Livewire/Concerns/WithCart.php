<?php declare(strict_types=1);

namespace App\Livewire\Concerns;

use App\Models\Product;

trait WithCart
{
    use WithNotifications;

    public function addToCart(int $productId, int $quantity = 1, array $options = []): void
    {
        $product = Product::find($productId);
        
        if (!$product || !$product->is_visible) {
            $this->notifyError(__('translations.product_not_available'));
            return;
        }

        // Check stock availability
        if ($product->stock_quantity !== null && $product->stock_quantity < $quantity) {
            $this->notifyWarning(__('translations.insufficient_stock'));
            return;
        }

        // Add to session cart (simplified for now)
        $cart = session('cart', []);
        $cartKey = $productId . '_' . md5(serialize($options));
        
        if (isset($cart[$cartKey])) {
            $cart[$cartKey]['quantity'] += $quantity;
        } else {
            $cart[$cartKey] = [
                'product_id' => $productId,
                'quantity' => $quantity,
                'options' => $options,
                'added_at' => now()->toISOString(),
            ];
        }

        session(['cart' => $cart]);

        $this->dispatch('cart:updated', [
            'product' => $product->name,
            'quantity' => $quantity,
        ]);

        $this->notifySuccess(__('translations.product_added_to_cart'));
    }

    public function removeFromCart(string $cartKey): void
    {
        $cart = session('cart', []);
        unset($cart[$cartKey]);
        session(['cart' => $cart]);

        $this->dispatch('cart:updated');
        $this->notifySuccess(__('translations.product_removed_from_cart'));
    }

    public function updateCartQuantity(string $cartKey, int $quantity): void
    {
        if ($quantity <= 0) {
            $this->removeFromCart($cartKey);
            return;
        }

        $cart = session('cart', []);
        if (isset($cart[$cartKey])) {
            $cart[$cartKey]['quantity'] = $quantity;
            session(['cart' => $cart]);
            
            $this->dispatch('cart:updated');
        }
    }

    public function getCartItemsProperty(): array
    {
        $cart = session('cart', []);
        $items = [];

        foreach ($cart as $key => $item) {
            $product = Product::find($item['product_id']);
            if ($product) {
                $items[$key] = [
                    'product' => $product,
                    'quantity' => $item['quantity'],
                    'options' => $item['options'],
                    'added_at' => $item['added_at'],
                ];
            }
        }

        return $items;
    }

    public function getCartTotalProperty(): float
    {
        $total = 0;
        foreach ($this->cartItems as $item) {
            $price = $item['product']->prices->first()?->amount ?? 0;
            $total += $price * $item['quantity'];
        }
        return $total;
    }

    public function getCartCountProperty(): int
    {
        return array_sum(array_column($this->cartItems, 'quantity'));
    }
}
