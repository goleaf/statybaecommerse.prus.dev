<?php

declare(strict_types=1);

namespace App\Livewire\Components\Checkout;

use App\Models\Address;
use App\Models\CartItem;
use App\Models\ShippingOption;
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

    /**
     * Track whether the component is currently resolving shipping options so the
     * view can disable actions and show optimistic UI feedback.
     */
    public bool $isResolving = false;

    #[Validate('required', message: 'You must select a delivery method')]
    public ?int $currentSelected = null;

    public bool $isResolvingOptions = false;

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
     * Handle address-driven shipping refresh requests triggered from the
     * preceding address step. The Livewire payload supplies the address id and
     * country code so we can avoid redundant lookups.
     */
    #[On('checkout-address-updated')]
    public function handleCheckoutAddressUpdated(?int $addressId = null, ?string $countryCode = null): void
    {
        session()->forget('checkout.shipping_option');
        $this->recalculateOptions($addressId, $countryCode);
    }

    /**
     * Optimistically update the selection when a radio item is clicked so the UI
     * reflects the expected choice before the network round trip finishes.
     */
    public function selectOption(int $optionId): void
    {
        if ($this->getResolvedOptionById($optionId) === null) {
            return;
        }

        $this->currentSelected = $optionId;
    }

    /**
     * Handle stepInfo functionality with proper error handling.
     *
     * @return array{label:string, complete:bool}
     */
    public function stepInfo(): array
    {
        return [
            'label'    => __('Delivery method'),
            'complete' => session()->exists('checkout') && data_get(session()->get('checkout'), 'shipping_option') !== null,
        ];
    }

    /**
     * React to address updates so the shipping matrix can be recalculated.
     */
    #[On('shipping-address-selected')]
    public function handleShippingAddressSelected(?int $addressId = null): void
    {
        $this->isResolvingOptions = true;

        try {
            $countryId = null;
            if ($addressId !== null) {
                $countryId = Address::query()->find($addressId)?->country_id;
            }

            $query = ShippingOption::query()->where('is_enabled', true)->orderBy('sort_order');

            if ($countryId !== null) {
                $query->where(static function ($builder) use ($countryId): void {
                    $builder->whereNull('country_id')->orWhere('country_id', $countryId);
                });
            }

            $this->options = $query->get()->map(function ($option) {
                return [
                    'id'                 => $option->id,
                    'name'               => $option->name,
                    'description'        => $option->description,
                    'price'              => $option->price,
                    'formatted_price'    => app_money_format($option->price, current_currency()),
                    'estimated_delivery' => $option->estimated_delivery,
                ];
            })->toArray();
            $this->resetErrorBag('currentSelected');
        } finally {
            $this->isResolvingOptions = false;
        }
    }

    /**
     * Render the Livewire component view with current state.
     */
    public function render(): View
    {
        return view('livewire.components.checkout.delivery');
    }

    /**
     * Recalculate shipping options for the current checkout context.
     */
    private function recalculateOptions(?int $shippingAddressId = null, ?string $countryCode = null): void
    {
        $this->isResolving = true;

        $countryCode = $countryCode ?? $this->resolveCountryCode($shippingAddressId);

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
        $this->isResolving = false;
    }

    /**
     * Resolve the country code for shipping calculations.
     */
    private function resolveCountryCode(?int $shippingAddressId = null): ?string
    {
        if ($shippingAddressId !== null) {
            $address = Address::query()->find($shippingAddressId);

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
