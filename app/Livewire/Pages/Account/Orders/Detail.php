<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Account\Orders;

use App\Models\Order;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * Order Detail
 *
 * Livewire component for displaying order details with reactive frontend functionality.
 */
final class Detail extends Component
{
    public Order $order;

    /**
     * Initialize the Livewire component with parameters.
     */
    public function mount(string $number): void
    {
        $this->order = Order::with(['items', 'items.product', 'shipping'])
            ->where('number', $number)
            ->firstOrFail();
    }

    /**
     * Render the Livewire component view with current state.
     */
    public function render(): View
    {
        return view('livewire.pages.account.orders.detail')
            ->layout('components.layouts.templates.account')
            ->title(__('Details of your order'));
    }
}
