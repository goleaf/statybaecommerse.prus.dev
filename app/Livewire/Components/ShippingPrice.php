<?php declare(strict_types=1);

namespace App\Livewire\Components;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class ShippingPrice extends Component
{
    public float $amount = 0.0;

    #[On('cartUpdated')]
    public function updateAmounts(): void
    {
        $this->compute();
    }

    public function mount(): void
    {
        $this->compute();
    }

    protected function compute(): void
    {
        $selected = (float) data_get(session()->get('checkout'), 'shipping_option.0.price', 0.0);
        if ($selected > 0) {
            $this->amount = $selected;
            return;
        }
        $zoneCode = (string) (session('zone.code') ?? session('zoneCode') ?? '');
        $zones = (array) config('shipping.zones', []);
        if ($zoneCode && isset($zones[$zoneCode])) {
            $this->amount = (float) $zones[$zoneCode];
            return;
        }
        $this->amount = (float) config('shipping.default_rate', 0.0);
    }

    public function render(): View
    {
        return view('livewire.components.shipping-price', [
            'amount' => $this->amount,
        ]);
    }
}
