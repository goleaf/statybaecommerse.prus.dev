<?php

declare(strict_types=1);

namespace App\Livewire\Components\Checkout;

use App\Models\Address;
use App\Models\CartItem;
use App\Services\Shipping\ShippingOptionResolver;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
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
 *
 * @method void nextStep()
 */
class Delivery extends StepComponent
{
    /**
     * Normalised shipping options resolved for the current checkout context.
     *
     * @var array<int, array{id:int,name:string,description:?string,price:float,formatted_price:string,estimated_delivery:?string}>
     */
    public array $options = [];

    #[Validate('required', message: 'You must select a delivery method')]
    public ?int $currentSelected = null;

    /**
     * Initialize the Livewire component with parameters.
     */
    public function mount(): void
    {
        // Seed the initially selected option from the persisted checkout state.
        // Remember the option previously confirmed by the customer (if any).
        $selectedFromSession = data_get(session()->get('checkout'), 'shipping_option.0.id');
        $this->currentSelected = $this->normaliseOptionId($selectedFromSession);

        // Resolve shipping options immediately so the delivery step starts hydrated.
        $this->recalculateOptions($this->normaliseAddressId(data_get(session()->get('checkout'), 'shipping_address.id')));
    }

    /**
     * Refresh shipping options whenever the shipping address changes upstream.
     */
    #[On('shipping-address-updated')]
    public function handleShippingAddressUpdated(?int $shippingAddressId = null): void
    {
        // If the address has changed, clear any persisted shipping choice so totals cannot go stale.
        $normalizedIncomingId = $this->normaliseAddressId($shippingAddressId);
        $persistedAddressId = $this->normaliseAddressId(data_get(session()->get('checkout'), 'shipping_address.id'));
        if ($normalizedIncomingId !== null && $persistedAddressId !== null && $normalizedIncomingId !== $persistedAddressId) {
            session()->forget('checkout.shipping_option');
            $this->currentSelected = null;
        }

        $this->recalculateOptions($normalizedIncomingId);
    }

    /**
     * Handle save functionality with proper error handling.
     */
    public function save(): void
    {
        $this->validate();
        session()->forget('checkout.shipping_option');

        // Look up the resolved option so we retain the dynamically calculated price instead of the base model amount.
        $resolvedOption = $this->getResolvedOptionById($this->currentSelected);
        if ($resolvedOption === null) {
            // Attempt to recover gracefully by recalculating once before failing hard.
            $this->recalculateOptions($this->normaliseAddressId(data_get(session()->get('checkout'), 'shipping_address.id')));
            $resolvedOption = $this->getResolvedOptionById($this->currentSelected);
        }

        if ($resolvedOption === null) {
            $this->addError('currentSelected', __('The selected delivery option is no longer available.'));

            return;
        }

        $option = $this->normaliseOptionForSession($resolvedOption);
        // Apply shipping discount context if any (free shipping or cap)
        $engine = app(\App\Services\Discounts\DiscountEngine::class);
        $baseAmount = (float) $option['price'];
        $cartSubtotal = session('cart.subtotal');
        $subtotal = is_numeric($cartSubtotal) ? (float) $cartSubtotal : 0.0;

        $context = [
            'currency_code' => current_currency(),
            'channel_id'    => config('app.url'),
            'user_id'       => Auth::id(),
            'now'           => now(),
            'cart'          => ['subtotal' => $subtotal, 'items' => []],
            'shipping'      => ['base_amount' => $baseAmount],
        ];

        $result = $engine->evaluate($context);
        $evaluatedDiscount = data_get($result, 'shipping.discount_amount', 0.0);
        $shippingDiscount = is_numeric($evaluatedDiscount) ? (float) $evaluatedDiscount : 0.0;
        if ($shippingDiscount > 0) {
            $discounted = max(0.0, $baseAmount - $shippingDiscount);
            $option['price'] = $discounted;
            // Refresh the formatted amount so downstream summaries reflect the discounted value immediately.
            $option['formatted_price'] = app_money_format($discounted, current_currency());
        }
        session()->put('checkout.shipping_option', [$option]);
        $this->dispatch('cart-price-update');
        $this->nextStep();
    }

    /**
     * Handle stepInfo functionality with proper error handling.
     *
     * @return array{label:string, complete:bool}
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
    private function recalculateOptions(?int $shippingAddressId = null): void
    {
        $countryCode = $this->resolveCountryCode($shippingAddressId);

        $cartItems = CartItem::with('product')
            ->where('session_id', Session::getId())
            ->get();

        $resolver = app(ShippingOptionResolver::class);

        /**
         * The resolver returns context-aware shipping options enriched with computed prices.
         *
         * @var array<int, array{id:int,name:string,description:?string,price:float,formatted_price:string,estimated_delivery:?string}> $resolved
         */
        $resolved = $resolver->resolve($cartItems, $countryCode)->toArray();

        $this->options = $resolved;

        $selectedId = $this->currentSelected ?? $this->normaliseOptionId(data_get(session()->get('checkout'), 'shipping_option.0.id'));
        $selectedOption = $this->findOptionInResolved($resolved, $selectedId);

        if ($selectedOption === null && $resolved !== []) {
            // Default to the first available option to keep the UI interactive.
            $selectedOption = $resolved[0];
        }

        $this->currentSelected = $selectedOption !== null ? $selectedOption['id'] : null;
    }

    /**
     * Determine the correct country code from either the supplied address id or session cache.
     */
    private function resolveCountryCode(?int $shippingAddressId = null): ?string
    {
        if ($shippingAddressId !== null) {
            $address = Address::query()
                ->where('user_id', Auth::id())
                ->find($shippingAddressId);

            if ($address !== null) {
                $countryCode = $address->getAttribute('country_code');

                return is_string($countryCode) ? $countryCode : null;
            }
        }

        $countryCode = data_get(session()->get('checkout'), 'shipping_address.country_code');

        return is_string($countryCode) ? $countryCode : null;
    }

    /**
     * Retrieve the option from the in-memory collection using the provided identifier.
     *
     * @return array{id:int,name:string,description:?string,price:float,formatted_price:string,estimated_delivery:?string}|null
     */
    private function getResolvedOptionById(?int $optionId): ?array
    {
        if ($optionId === null) {
            return null;
        }

        /** @var array<int, array{id:int,name:string,description:?string,price:float,formatted_price:string,estimated_delivery:?string}> $options */
        $options = $this->options;

        return $this->findOptionInResolved($options, $optionId);
    }

    /**
     * Locate an option within the provided resolved dataset.
     *
     * @param  array<int, array{id:int,name:string,description:?string,price:float,formatted_price:string,estimated_delivery:?string}> $resolved
     * @return array{id:int,name:string,description:?string,price:float,formatted_price:string,estimated_delivery:?string}|null
     */
    private function findOptionInResolved(array $resolved, ?int $optionId): ?array
    {
        if ($optionId === null) {
            return null;
        }

        foreach ($resolved as $option) {
            if ((int) $option['id'] === $optionId) {
                return $option;
            }
        }

        return null;
    }

    /**
     * Prepare the resolved option for storage in the checkout session payload.
     *
     * @param  array{id:int,name:string,description:?string,price:float,formatted_price:string,estimated_delivery:?string} $option
     * @return array{id:int,name:string,description:?string,price:float,formatted_price:string,estimated_delivery:?string}
     */
    private function normaliseOptionForSession(array $option): array
    {
        // Ensure scalar typing for downstream JSON encoding and persistence.
        $description = $option['description'] ?? null;
        $estimatedDelivery = $option['estimated_delivery'] ?? null;

        return [
            'id'                 => (int) $option['id'],
            'name'               => (string) $option['name'],
            'description'        => is_string($description) ? $description : null,
            'price'              => (float) $option['price'],
            'formatted_price'    => (string) $option['formatted_price'],
            'estimated_delivery' => is_string($estimatedDelivery) ? $estimatedDelivery : null,
        ];
    }

    /**
     * Normalise arbitrary address identifiers into nullable integers.
     */
    private function normaliseAddressId(mixed $addressId): ?int
    {
        if (is_numeric($addressId)) {
            return (int) $addressId;
        }

        return null;
    }

    /**
     * Normalise arbitrary option identifiers into nullable integers.
     */
    private function normaliseOptionId(mixed $optionId): ?int
    {
        if (is_numeric($optionId)) {
            return (int) $optionId;
        }

        return null;
    }
}
