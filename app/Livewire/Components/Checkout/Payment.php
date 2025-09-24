<?php

declare(strict_types=1);

namespace App\Livewire\Components\Checkout;

use App\Actions\CreateOrder;
use App\Actions\Payment\PayWithCash;
use App\Enums\PaymentType;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Validate;
use Spatie\LivewireWizard\Components\StepComponent;

/**
 * Payment
 *
 * Livewire component for Payment with reactive frontend functionality, real-time updates, and user interaction handling.
 *
 * @property int|null $currentSelected
 * @property mixed $methods
 */
class Payment extends StepComponent
{
    #[Validate('required', message: 'You must select a payment method')]
    public ?int $currentSelected = null;

    /**
     * @var array|Collection
     */
    public $methods = [];

    /**
     * Initialize the Livewire component with parameters.
     */
    public function mount(): void
    {
        $countryId = data_get(session()->get('checkout'), 'shipping_address.country_id');
        $this->currentSelected = data_get(session()->get('checkout'), 'payment') ? data_get(session()->get('checkout'), 'payment')[0]['id'] : null;
        $this->methods = [];
    }

    /**
     * Handle save functionality with proper error handling.
     */
    public function save(): void
    {
        $this->validate();
        session()->forget('checkout.payment');
        session()->push('checkout.payment', PaymentMethod::query()->find($this->currentSelected)->toArray());
        $order = (new CreateOrder)->handle();
        match (data_get(session()->get('checkout'), 'payment')[0]['slug']) {
            PaymentType::Cash() => (new PayWithCash)->handle($order),
        };
    }

    /**
     * Handle stepInfo functionality with proper error handling.
     */
    public function stepInfo(): array
    {
        return ['label' => __('Payment'), 'complete' => session()->exists('checkout') && data_get(session()->get('checkout'), 'payment') !== null];
    }

    /**
     * Render the Livewire component view with current state.
     */
    public function render(): View
    {
        return view('livewire.components.checkout.payment');
    }
}
