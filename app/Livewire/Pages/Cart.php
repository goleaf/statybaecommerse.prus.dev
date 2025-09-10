<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.templates.app')]
class Cart extends Component
{
    public float $subtotal = 0.0;

    public function mount(): void
    {
        $this->refreshTotals();
    }

    private function getCartSession(): mixed
    {
        if (!class_exists(\Darryldecode\Cart\Facades\CartFacade::class)) {
            return null;
        }

        try {
            return \Darryldecode\Cart\Facades\CartFacade::session(session()->getId());
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function refreshTotals(): void
    {
        $cart = $this->getCartSession();
        $this->subtotal = $cart ? (float) $cart->getSubTotal() : 0.0;
    }

    public function removeItem(int $id): void
    {
        $cart = $this->getCartSession();
        if ($cart) {
            $cart->remove($id);
            $this->dispatch('cartUpdated');
            $this->refreshTotals();
        }
    }

    // Alias to keep shared cart item component working in different contexts
    public function removeToCart(int $id): void
    {
        $this->removeItem($id);
    }

    public function updateItemQuantity(int $id, int $quantity): void
    {
        $quantity = max(0, $quantity);
        $cart = $this->getCartSession();
        if (!$cart) {
            return;
        }

        if ($quantity === 0) {
            $cart->remove($id);
        } else {
            // Darryldecode\Cart supports absolute updates via ['quantity' => ['relative' => false, 'value' => X]]
            try {
                $cart->update($id, [
                    'quantity' => [
                        'relative' => false,
                        'value' => $quantity,
                    ],
                ]);
            } catch (\Throwable $e) {
                // ignore update failures
            }
        }

        $this->dispatch('cartUpdated');
        $this->refreshTotals();
    }

    public function incrementItem(int $id): void
    {
        $cart = $this->getCartSession();
        if (!$cart) {
            return;
        }
        try {
            $cart->update($id, [
                'quantity' => 1, // relative +1
            ]);
        } catch (\Throwable $e) {
            // ignore
        }
        $this->dispatch('cartUpdated');
        $this->refreshTotals();
    }

    public function decrementItem(int $id): void
    {
        $cart = $this->getCartSession();
        if (!$cart) {
            return;
        }
        try {
            // Fetch current quantity to prevent negative
            $current = 0;
            foreach ($cart->getContent() as $item) {
                if ((int) $item->id === (int) $id) {
                    $current = (int) $item->quantity;
                    break;
                }
            }
            if ($current <= 1) {
                $cart->remove($id);
            } else {
                $cart->update($id, [
                    'quantity' => -1, // relative -1
                ]);
            }
        } catch (\Throwable $e) {
            // ignore
        }
        $this->dispatch('cartUpdated');
        $this->refreshTotals();
    }

    public function render(): View
    {
        $sessionItems = collect();
        $cart = $this->getCartSession();
        if ($cart) {
            $sessionItems = $cart->getContent();
        }

        return view('livewire.pages.cart', [
            'items' => $sessionItems,
            'subtotal' => $this->subtotal,
        ])->title(__('Your cart'));
    }
}

