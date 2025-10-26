<?php

declare(strict_types=1);

namespace App\Livewire\Components\Checkout;

use App\Models\Address;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Spatie\LivewireWizard\Components\StepComponent;

/**
 * Shipping
 *
 * Livewire component for Shipping with reactive frontend functionality, real-time updates, and user interaction handling.
 *
 * @property int|null $shippingAddressId
 * @property bool     $sameAsShipping
 * @property int|null $billingAddressId
 *
 * @method void nextStep()
 */
class Shipping extends StepComponent
{
    #[Validate('required', message: 'You need to select a delivery address')]
    public ?int $shippingAddressId = null;

    #[Validate('boolean')]
    public bool $sameAsShipping = false;

    #[Validate('required_if_declined:sameAsShipping', message: 'You must choose a billing address')]
    public ?int $billingAddressId = null;

    /**
     * Sync billing address selection when the "same as shipping" toggle flips
     * so totals remain coherent and shipping recalculations stay accurate.
     */
    public function updatedSameAsShipping(bool $value): void
    {
        if ($value) {
            $this->billingAddressId = $this->shippingAddressId;
        }

        // Ensure shipping options recompute when toggling billing state.
        $this->dispatchShippingRecalculation();
        $this->broadcastShippingContext($this->shippingAddressId);
    }

    /**
     * Centralised helper to emit the event consumed by the delivery step when
     * shipping related state changes.
     */
    private function dispatchShippingRecalculation(): void
    {
        $this->dispatch('shipping-address-updated', shippingAddressId: $this->shippingAddressId);
    }

    /**
     * Initialize the Livewire component with parameters.
     */
    public function mount(): void
    {
        $checkout = session()->get('checkout');
        $storedShipping = data_get($checkout, 'shipping_address.id');
        $storedBilling = data_get($checkout, 'billing_address.id');
        $this->shippingAddressId = is_numeric($storedShipping) ? (int) $storedShipping : null;
        $this->billingAddressId = is_numeric($storedBilling) ? (int) $storedBilling : null;
        $this->sameAsShipping = (bool) data_get($checkout, 'same_as_shipping');
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
        if (! Auth::check()) {
            return;
        }

        $shippingAddress = Address::query()
            ->where('user_id', Auth::id())
            ->find($this->shippingAddressId);

        if ($shippingAddress === null) {
            $this->addError('shippingAddressId', __('Please choose a valid delivery address.'));

            return;
        }

        $billingAddress = $this->sameAsShipping
            ? $shippingAddress
            : Address::query()->where('user_id', Auth::id())->find($this->billingAddressId);

        if (! $this->sameAsShipping && $billingAddress === null) {
            $this->addError('billingAddressId', __('Please choose a valid billing address.'));

            return;
        }

        $shippingPayload = $shippingAddress->toArray();
        $billingPayload = $this->sameAsShipping ? $shippingPayload : ($billingAddress?->toArray() ?? []);

        session()->put('checkout', [
            'shipping_address' => $shippingPayload,
            'same_as_shipping' => $this->sameAsShipping,
            'billing_address'  => $billingPayload,
        ]);
        // Notify downstream checkout steps that a definitive shipping address was
        // persisted so they can refresh shipping quotes without waiting for a
        // manual page transition.
        $this->broadcastShippingContext($this->shippingAddressId);
        $this->nextStep();
    }

    /**
     * React to radio button updates with a slight debounce so shipping quotes
     * refresh smoothly without hammering the resolver.
     */
    public function updatedShippingAddressId(mixed $addressId): void
    {
        $normalisedId = is_numeric($addressId) ? (int) $addressId : null;
        $this->shippingAddressId = $normalisedId;

        if ($this->sameAsShipping) {
            // Mirror the shipping selection across billing when toggled on.
            $this->billingAddressId = $normalisedId;
        }

        // Broadcast both the lightweight recalculation signal and the richer
        // context payload so dependent components stay consistent.
        $this->dispatchShippingRecalculation();
        $this->broadcastShippingContext($this->shippingAddressId);
    }

    /**
     * Normalise billing id updates so the typed property stays in sync with the
     * Livewire payload while validation continues to work as expected.
     */
    public function updatedBillingAddressId(mixed $addressId): void
    {
        $this->billingAddressId = is_numeric($addressId) ? (int) $addressId : null;
    }

    /**
     * Handle stepInfo functionality with proper error handling.
     */
    /**
     * @return array{label:string, complete:bool}
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
        $user = Auth::user();
        $addresses = $user instanceof User ? $user->addresses()->get()->groupBy('type') : collect();

        // Whenever address records mutate (create/update/delete) we emit the
        // current selection so dependent components can rehydrate gracefully.
        $this->broadcastShippingContext($this->shippingAddressId);

        return view('livewire.components.checkout.shipping', ['addresses' => $addresses]);
    }

    /**
     * Broadcast the active shipping address context (id + country code) to other
     * checkout components so they can refresh their state without duplication.
     */
    private function broadcastShippingContext(?int $addressId): void
    {
        if ($addressId === null || ! Auth::check()) {
            return;
        }

        $address = Address::query()
            ->where('user_id', Auth::id())
            ->find($addressId);

        if ($address === null) {
            return;
        }

        $countryCode = $address->getAttribute('country_code');
        if (! is_string($countryCode) || $countryCode === '') {
            return;
        }

        $this->dispatch('checkout-address-updated', addressId: $address->getKey(), countryCode: $countryCode);
    }
}
