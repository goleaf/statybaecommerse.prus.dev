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


