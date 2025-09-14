<?php

declare (strict_types=1);
namespace App\Livewire\Components;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
/**
 * ShippingPrice
 * 
 * Livewire component for ShippingPrice with reactive frontend functionality, real-time updates, and user interaction handling.
 * 
 */
class ShippingPrice extends Component
{
    /**
     * Handle shippingAmount functionality with proper error handling.
     * @return float
     */
    #[Computed]
    public function shippingAmount(): float
    {
        $selected = (float) data_get(session()->get('checkout'), 'shipping_option.0.price', 0.0);
        if ($selected > 0) {
            return $selected;
        }
        $zoneCode = (string) (session('zone.code') ?? session('zoneCode') ?? '');
        $zones = (array) config('shipping.zones', []);
        if ($zoneCode && isset($zones[$zoneCode])) {
            return (float) $zones[$zoneCode];
        }
        return (float) config('shipping.default_rate', 0.0);
    }
    /**
     * Handle shippingOptions functionality with proper error handling.
     * @return array
     */
    #[Computed]
    public function shippingOptions(): array
    {
        $zoneCode = (string) (session('zone.code') ?? session('zoneCode') ?? '');
        $zones = (array) config('shipping.zones', []);
        if ($zoneCode && isset($zones[$zoneCode])) {
            return ['zone_code' => $zoneCode, 'rate' => (float) $zones[$zoneCode], 'available' => true];
        }
        return ['zone_code' => null, 'rate' => (float) config('shipping.default_rate', 0.0), 'available' => false];
    }
    /**
     * Handle updateAmounts functionality with proper error handling.
     * @return void
     */
    #[On('cartUpdated')]
    public function updateAmounts(): void
    {
        // Computed properties will automatically update
    }
    /**
     * Initialize the Livewire component with parameters.
     * @return void
     */
    public function mount(): void
    {
        // Computed properties will be calculated on first access
    }
    /**
     * Render the Livewire component view with current state.
     * @return View
     */
    public function render(): View
    {
        return view('livewire.components.shipping-price', ['amount' => $this->shippingAmount, 'options' => $this->shippingOptions]);
    }
}