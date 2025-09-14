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
 * Livewire component for reactive frontend functionality.
 */
class ShippingPrice extends Component
{
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

    #[Computed]
    public function shippingOptions(): array
    {
        $zoneCode = (string) (session('zone.code') ?? session('zoneCode') ?? '');
        $zones = (array) config('shipping.zones', []);
        
        if ($zoneCode && isset($zones[$zoneCode])) {
            return [
                'zone_code' => $zoneCode,
                'rate' => (float) $zones[$zoneCode],
                'available' => true,
            ];
        }
        
        return [
            'zone_code' => null,
            'rate' => (float) config('shipping.default_rate', 0.0),
            'available' => false,
        ];
    }

    #[On('cartUpdated')]
    public function updateAmounts(): void
    {
        // Computed properties will automatically update
    }

    public function mount(): void
    {
        // Computed properties will be calculated on first access
    }

    public function render(): View
    {
        return view('livewire.components.shipping-price', [
            'amount' => $this->shippingAmount,
            'options' => $this->shippingOptions,
        ]);
    }
}
