<?php

declare(strict_types=1);

namespace App\Livewire\Shared;

use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * CurrencySelector
 *
 * Livewire component for CurrencySelector with reactive frontend functionality, real-time updates, and user interaction handling.
 *
 * @property array<int, array{id:int, code:string, symbol:string, active:bool}> $currencies
 * @property string|null                                                        $activeCurrencyCode
 */
class CurrencySelector extends Component
{
    public ?string $activeCurrencyCode = 'EUR';

    /**
     * Initialize the Livewire component with parameters.
     */
    public function mount(): void
    {
        $this->activeCurrencyCode = 'EUR';
    }

    /**
     * Expose primitive currency payloads for Blade consumption.
     *
     * @return array<int, array{id:int, code:string, symbol:string}>
     */
    public function getCurrenciesProperty(): array
    {
        return [[
            'id'     => 1,
            'code'   => 'EUR',
            'symbol' => '€',
            'active' => true,
        ]];
    }

    /**
     * Render the Livewire component view with current state.
     */
    public function render(): View
    {
        return view('livewire.shared.currency-selector');
    }
}
