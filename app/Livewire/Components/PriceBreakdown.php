<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Throwable;

/**
 * PriceBreakdown
 *
 * Reusable Livewire component for displaying price breakdown with subtotal, taxes, and total.
 * Eliminates duplication between desktop and mobile checkout views.
 */
class PriceBreakdown extends Component
{
    public string $variant = 'default';

    public bool $showSubtotal = true;

    public bool $showTaxes = true;

    public bool $showTotal = true;

    /**
     * Get the subtotal amount from cart
     */
    #[Computed]
    public function subtotal(): float
    {
        $sessionKey = session()->getId();
        if (class_exists(\Darryldecode\Cart\Facades\CartFacade::class)) {
            try {
                return (float) \Darryldecode\Cart\Facades\CartFacade::session($sessionKey)->getSubTotal();
            } catch (Throwable $e) {
                return 0.0;
            }
        }

        return 0.0;
    }

    /**
     * Update breakdown when cart changes
     */
    #[On('cart-updated')]
    #[On('coupon-updated')]
    public function updateBreakdown(): void
    {
        // Computed properties will automatically refresh
    }

    /**
     * Initialize the component
     */
    public function mount(
        string $variant = 'default',
        bool $showSubtotal = true,
        bool $showTaxes = true,
        bool $showTotal = true
    ): void {
        $this->variant = $variant;
        $this->showSubtotal = $showSubtotal;
        $this->showTaxes = $showTaxes;
        $this->showTotal = $showTotal;
    }

    /**
     * Render the component view
     */
    public function render(): View
    {
        return view('livewire.components.price-breakdown');
    }
}
