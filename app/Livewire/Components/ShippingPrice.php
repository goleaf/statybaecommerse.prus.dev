<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * ShippingPrice
 *
 * Livewire component for ShippingPrice with reactive frontend functionality, real-time updates, and user interaction handling.
 */
class ShippingPrice extends Component
{
    /**
     * Handle shippingAmount functionality with proper error handling.
     */
    #[Computed]
    public function shippingAmount(): float
    {
        $selected = (float) data_get(session()->get('checkout'), 'shipping_option.0.price', 0.0);
        if ($selected > 0) {
            return $selected;
        }

        return (float) config('shipping.default_rate', 0.0);
    }

    /**
     * Handle shippingOptions functionality with proper error handling.
     */
    #[Computed]
    public function shippingOptions(): array
    {
        return ['rate' => (float) config('shipping.default_rate', 0.0), 'available' => true];
    }

    /**
     * Handle updateAmounts functionality with proper error handling.
     */
    #[On('cartUpdated')]
    public function updateAmounts(): void
    {
        // Computed properties will automatically update
    }

    /**
     * Initialize the Livewire component with parameters.
     */
    public function mount(): void
    {
        // Computed properties will be calculated on first access
    }

    /**
     * Render the Livewire component view with current state.
     */
    public function render(): View
    {
        return view('livewire.components.shipping-price', ['amount' => $this->shippingAmount, 'options' => $this->shippingOptions]);
    }
}
