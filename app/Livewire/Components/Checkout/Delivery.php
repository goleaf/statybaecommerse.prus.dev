<?php

declare(strict_types=1);

namespace App\Livewire\Components\Checkout;

use App\Models\Address;
use App\Services\Shipping\ShippingOptionResolver;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Spatie\LivewireWizard\Components\StepComponent;

/**
 * Delivery
 *
 * Livewire component for Delivery with reactive frontend functionality, real-time updates, and user interaction handling.
 *
 * @property mixed    $options
 * @property int|null $currentSelected
 */
class Delivery extends StepComponent
{
    /**
     * Normalised shipping options resolved for the current checkout context.
     *
     * @var array<int, array{id:int|string,name:string,price:float|int,currency:string,eta:string|null}>
     */
    public array $options = [];

    #[Validate('required', message: 'You must select a delivery method')]
    public int|string|null $currentSelected = null;

    /**
     * Example address structure used by resolver.
     *
     * @var array{country:string|null,city:string|null,zip:string|null,street:string|null}
     */
    public array $address = [
        'country' => null,
        'city'    => null,
        'zip'     => null,
        'street'  => null,
    ];

    /**
     * Tracks whether shipping options are currently being recomputed.
     */
    public bool $calculating = false;

    /**
     * Initialize the Livewire component with parameters.
     */
    public function mount(): void
    {
        // Pull any persisted checkout data so the component starts hydrated.
        $checkout = session()->get('checkout');

        // Seed the initially selected option from the persisted checkout state.
        $this->currentSelected = data_get($checkout, 'shipping_option.id');

        // Normalise the stored shipping address into the resolver-friendly format.
        $this->address = $this->normaliseAddress((array) data_get($checkout, 'shipping_address', []));

        // Resolve shipping options immediately so the delivery step starts hydrated.
        $this->recalculate();
    }

    /**
     * Refresh shipping options whenever the shipping address changes upstream.
     */
    #[On('shipping-address-updated')]
    public function handleShippingAddressUpdated(?int $shippingAddressId = null): void
    {
        // Rehydrate the address using the selected ID before recomputing options.
        $this->address = $this->resolveAddressFromId($shippingAddressId);

        $this->recalculate();
    }

    /**
     * React to inline address field updates so shipping prices stay in sync.
     */
    public function updated($prop): void
    {
        if (is_string($prop) && str_starts_with($prop, 'address.')) {
            $this->recalculate();
        }
    }

    /**
     * Handle save functionality with proper error handling.
     */
    public function save(): void
    {
        $this->validate();
        session()->forget('checkout.shipping_option');
        // Find the dynamic option computed by the resolver above.
        $selected = collect($this->options ?? [])->firstWhere('id', $this->currentSelected);

        if ($selected === null) {
            // Bail out early if the referenced option disappeared between recomputations.
            $this->addError('currentSelected', __('Select a shipping option'));

            return;
        }

        // Persist dynamic price (what user saw) for later order calculations.
        session()->put('checkout.shipping_option', [
            'id'       => $selected['id'],
            'name'     => $selected['name'],
            'price'    => $selected['price'],
            'currency' => $selected['currency'] ?? 'EUR',
            'eta'      => $selected['eta'] ?? null,
        ]);

        // Notify interested listeners about the selection so totals can refresh.
        $this->dispatch('shippingOptionSaved', $selected);
        $this->dispatch('cart-price-update');

        $this->nextStep();
    }

    /**
     * Handle stepInfo functionality with proper error handling.
     */
    public function stepInfo(): array
    {
        return ['label' => __('Delivery method'), 'complete' => session()->exists('checkout') && data_get(session()->get('checkout'), 'shipping_option') !== null];
    }

    /**
     * Render the Livewire component view with current state.
     */
    public function render(): View
    {
        return view('livewire.components.checkout.delivery');
    }

    /**
     * Pull fresh options from the resolver so shipping reflects the latest address data.
     */
    private function recalculate(): void
    {
        // Prevent stale session data from polluting downstream calculations.
        session()->forget('checkout.shipping_option');

        // Toggle the loading indicator so the frontend can show feedback.
        $this->calculating = true;

        try {
            $resolver = app(ShippingOptionResolver::class);

            $options = $resolver->forCart(user: auth()->user(), destination: $this->address);

            $this->options = collect($options)
                ->map(function (array $option): array {
                    // Collapse resolver payload into the minimal set required by the checkout UI.
                    return [
                        'id'       => $option['id'],
                        'name'     => $option['name'],
                        'price'    => $option['price'],
                        'currency' => $option['currency'] ?? 'EUR',
                        'eta'      => $option['eta'] ?? ($option['estimated_delivery'] ?? null),
                    ];
                })
                ->values()
                ->all();

            $availableIds = collect($this->options)->pluck('id');

            if ($availableIds->doesntContain($this->currentSelected)) {
                // Default to the first available option to keep the UI interactive.
                $this->currentSelected = $availableIds->first();
            }
        } finally {
            // Always clear the loading flag, even if the resolver fails mid-flight.
            $this->calculating = false;
        }
    }

    /**
     * Normalise the address array into the resolver-friendly structure.
     *
     * @param  array<string, mixed>  $raw
     * @return array{country:string|null,city:string|null,zip:string|null,street:string|null}
     */
    private function normaliseAddress(array $raw): array
    {
        return [
            'country' => $raw['country_code'] ?? $raw['country'] ?? null,
            'city'    => $raw['city'] ?? ($raw['city_name'] ?? null),
            'zip'     => $raw['postal_code'] ?? $raw['zip'] ?? null,
            'street'  => $raw['address_line_1'] ?? $raw['street'] ?? null,
        ];
    }

    /**
     * Load the selected address from storage and normalise it for resolver usage.
     */
    private function resolveAddressFromId(?int $shippingAddressId = null): array
    {
        if ($shippingAddressId !== null) {
            $address = Address::query()
                ->where('user_id', Auth::id())
                ->find($shippingAddressId);

            if ($address !== null) {
                return $this->normaliseAddress($address->toArray());
            }
        }

        // Fallback to any persisted checkout data when no explicit ID is provided.
        return $this->normaliseAddress((array) data_get(session()->get('checkout'), 'shipping_address', []));
    }
}
