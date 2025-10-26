<?php

declare(strict_types=1);

namespace App\Livewire\Components\Checkout;

use App\Models\Address;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Spatie\LivewireWizard\Components\StepComponent;

/**
 * Shipping
 *
 * Livewire component for Shipping with reactive frontend functionality, real-time updates, and user interaction handling.
 *
 * @property array{id:int|null} $shippingAddress
 * @property bool               $sameAsShipping
 * @property array{id:int|null} $billingAddress
 */
class Shipping extends StepComponent
{
    /**
     * @var array{id:int|null}
     */
    public array $shippingAddress = ['id' => null];

    public bool $sameAsShipping = false;

    /**
     * @var array{id:int|null}
     */
    public array $billingAddress = ['id' => null];

    /**
     * Initialize the Livewire component with parameters.
     */
    public function mount(): void
    {
        $checkout = session()->get('checkout');
        $this->shippingAddress['id'] = data_get($checkout, 'shipping_address.id');
        $this->billingAddress['id'] = data_get($checkout, 'billing_address.id');
        $this->sameAsShipping = (bool) data_get($checkout, 'same_as_shipping');

        if ($this->sameAsShipping && $this->billingAddress['id'] === null) {
            // Ensure the billing selector mirrors shipping when "same as" is restored from the session.
            $this->billingAddress['id'] = $this->shippingAddress['id'];
        }
    }

    /**
     * Handle save functionality with proper error handling.
     */
    public function save(): void
    {
        $this->validate();

        if (session()->exists('checkout')) {
            session()->forget('checkout');
        }

        $shippingAddress = $this->resolveAddress((int) ($this->shippingAddress['id'] ?? 0));
        $billingAddress = $this->sameAsShipping
            ? $shippingAddress
            : $this->resolveAddress((int) ($this->billingAddress['id'] ?? 0));

        session()->put('checkout', [
            'shipping_address' => $shippingAddress,
            'same_as_shipping' => $this->sameAsShipping,
            'billing_address'  => $billingAddress,
        ]);

        $this->nextStep();
    }

    /**
     * Handle stepInfo functionality with proper error handling.
     */
    public function stepInfo(): array
    {
        return [
            'label'    => __('Address'),
            'complete' => session()->exists('checkout') && data_get(session()->get('checkout'), 'shipping_address') !== null,
        ];
    }

    /**
     * Render the Livewire component view with current state.
     */
    #[On('addresses-updated')]
    public function render(): View
    {
        $addresses = Auth::user()->addresses()->get()->groupBy('type');

        return view('livewire.components.checkout.shipping', ['addresses' => $addresses]);
    }

    /**
     * Livewire validation definitions for the nested address payload.
     *
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return [
            // Ensure a shipping address exists so the delivery calculator can resolve rates.
            'shippingAddress.id' => ['required', 'integer'],
            // Maintain standard boolean validation for the toggle state.
            'sameAsShipping'     => ['boolean'],
            // Only demand a billing address when the user opts out of reusing the shipping details.
            'billingAddress.id'  => ['required_unless:sameAsShipping,true', 'nullable', 'integer'],
        ];
    }

    /**
     * Reset validation noise and broadcast the updated selection to dependent steps.
     */
    public function updatedShippingAddressId($value): void
    {
        $this->billingAddress['id'] = $this->sameAsShipping ? $value : $this->billingAddress['id'];
        $this->resetValidation();
        $this->dispatch('shipping-address-selected', addressId: (int) $value);
    }

    /**
     * Keep the billing address aligned whenever the toggle changes.
     */
    public function updatedSameAsShipping(bool $value): void
    {
        if ($value) {
            $this->billingAddress['id'] = $this->shippingAddress['id'];
        }

        $this->resetValidation();
    }

    /**
     * Clear billing validation errors when a new option is chosen.
     */
    public function updatedBillingAddressId($value): void
    {
        $this->resetValidation();
        $this->dispatch('billing-address-selected', addressId: (int) $value);
    }

    /**
     * Resolve an address record into an array while guarding against missing IDs.
     *
     * @return array<string, mixed>
     */
    private function resolveAddress(int $id): array
    {
        $address = $id > 0 ? Address::query()->find($id)?->toArray() : null;

        return is_array($address) ? $address : [];
    }
}
