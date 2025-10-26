<?php

declare(strict_types=1);

namespace App\Livewire\Components\Checkout;

use App\Models\Address;
use App\Models\CartItem;
use App\Models\ShippingOption;
use App\Services\Shipping\ShippingOptionResolver;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection as SupportCollection;
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
     * @var array<int, array{id:int,name:string,description:string,price:float,formatted_price:string,estimated_delivery:string,currency_code:string}>
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
        // Restore the persisted selection so returning visitors keep their previous
        // choice, then immediately resolve context-aware options for the cart.
        $storedSelection = data_get(session()->get('checkout'), 'shipping_option.0.id');
        $this->currentSelected = is_numeric($storedSelection) ? (int) $storedSelection : null;
        $this->resolveOptions();
    }

    /**
     * Handle save functionality with proper error handling.
     */
    public function save(): void
    {
        $this->validate();

        session()->forget('checkout.shipping_option');

        // Retrieve the hydrated shipping payload that matches the current selection
        // so we can persist the calculated price back into the session.
        $optionData = $this->findResolvedOption((int) $this->currentSelected);
        $optionModel = ShippingOption::query()->find($this->currentSelected);

        if ($optionModel === null || $optionData === null) {
            // Fall back to validation messaging when the selected option disappears
            // between renders (for example, after an address change).
            $this->addError('currentSelected', __('Please choose a valid delivery option.'));

            return;
        }

        $optionPrice = (float) $optionData['price'];
        $option = array_merge($optionModel->toArray(), [
            'price'                   => $optionPrice,
            'formatted_price'         => (string) $optionData['formatted_price'],
            'estimated_delivery'      => (string) $optionData['estimated_delivery'],
            'estimated_delivery_text' => (string) $optionData['estimated_delivery'],
        ]);
        $baseAmount = $optionPrice;
        $channelUrl = config('app.url');
        $cartSubtotal = session('cart.subtotal');
        $subtotal = is_numeric($cartSubtotal) ? (float) $cartSubtotal : 0.0;

        // Apply shipping discount context if any (free shipping or cap)
        $engine = app(\App\Services\Discounts\DiscountEngine::class);
        $context = [
            'currency_code' => current_currency(),
            'channel_id'    => is_string($channelUrl) ? $channelUrl : null,
            'user_id'       => Auth::id(),
            'now'           => now(),
            'cart'          => ['subtotal' => $subtotal, 'items' => []],
            'shipping'      => ['base_amount' => $baseAmount],
        ];
        $result = $engine->evaluate($context);
        $discountValue = data_get($result, 'shipping.discount_amount', 0.0);
        $shippingDiscount = is_numeric($discountValue) ? (float) $discountValue : 0.0;
        if ($shippingDiscount > 0) {
            $option['price'] = max(0.0, $baseAmount - $shippingDiscount);
        }

        session()->push('checkout.shipping_option', $selectedOption);
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
        $this->resolveOptions($addressId, $countryCode);
    }

    /**
     * Backwards-compatible listener for legacy shipping address updates fired
     * from older checkout flows.
     */
    #[On('shipping-address-updated')]
    public function handleShippingAddressUpdated(?int $addressId = null): void
    {
        session()->forget('checkout.shipping_option');
        $this->resolveOptions($addressId);
    }

    /**
     * Optimistically update the selection when a radio item is clicked so the UI
     * reflects the expected choice before the network round trip finishes.
     */
    public function selectOption(int $optionId): void
    {
        if ($this->findResolvedOption($optionId) === null) {
            return;
        }

        $this->currentSelected = $optionId;
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

            $this->options = $this->normaliseOptions($query->get());
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
     * Resolve the current shipping options for the active cart and address.
     */
    private function resolveOptions(?int $addressId = null, ?string $countryCode = null): void
    {
        $this->isResolving = true;

        $resolver = app(ShippingOptionResolver::class);
        $cartItems = $this->getCartItems();
        /** @var SupportCollection<int, array{id:int,name:string,price:float,formatted_price:string,estimated_delivery:string}> $resolved */
        $resolved = $resolver->resolve($cartItems->collect(), $countryCode ?? $this->resolveCountryCode($addressId));

        $optionIds = $resolved
            ->pluck('id')
            ->filter(static fn ($id): bool => is_numeric($id))
            ->map(static fn ($id): int => (int) $id);

        /** @var SupportCollection<int, ShippingOption> $optionModels */
        $optionModels = ShippingOption::query()->whereIn('id', $optionIds)->get()->keyBy('id');

        // Normalise the resolved dataset so the view only needs to consume a
        // predictable array structure, regardless of the underlying model state.
        $this->options = $resolved
            ->map(function (array $option) use ($optionModels): array {
                $identifier = (int) $option['id'];
                $model = $optionModels->get($identifier);
                $name = (string) $option['name'];
                $price = (float) $option['price'];
                $formatted = (string) $option['formatted_price'];
                $eta = (string) $option['estimated_delivery'];
                $description = $model !== null ? (string) $model->description : '';
                $currency = $model !== null ? (string) $model->currency_code : current_currency();

                return [
                    'id'                 => $identifier,
                    'name'               => $name,
                    'description'        => $description,
                    'price'              => $price,
                    'formatted_price'    => $formatted,
                    'estimated_delivery' => $eta,
                    'currency_code'      => $currency,
                ];
            })
            ->values()
            ->all();

        if ($this->currentSelected !== null && $this->findResolvedOption((int) $this->currentSelected) === null) {
            $this->currentSelected = null;
        }

        if ($this->currentSelected === null && $this->options !== []) {
            $firstIdentifier = Arr::get($this->options, '0.id');
            $this->currentSelected = is_numeric($firstIdentifier) ? (int) $firstIdentifier : null;
        }

        $this->isResolving = false;
    }

    /**
     * Attempt to find the resolved option for a specific identifier.
     *
     * @return array{id:int,name:string,description:string,price:float,formatted_price:string,estimated_delivery:string,currency_code:string}|null
     */
    private function findResolvedOption(int $optionId): ?array
    {
        foreach ($this->options as $option) {
            if (! is_array($option)) {
                continue;
            }

            $identifier = $option['id'] ?? null;
            if (is_numeric($identifier) && (int) $identifier === $optionId) {
                /** @var array{id:int,name:string,description:string,price:float,formatted_price:string,estimated_delivery:string,currency_code:string} $option */
                return $option;
            }
        }

        return null;
    }

    /**
     * Load the cart items for the active shopper so the shipping resolver can
     * inspect weights and order totals.
     *
     * @return EloquentCollection<int, CartItem>
     */
    private function getCartItems(): EloquentCollection
    {
        $query = CartItem::query()->with('product');

        if (Auth::check()) {
            $query->forUser((int) Auth::id());
        } else {
            $query->forSession(Session::getId());
        }

        return $query->get();
    }

    /**
     * Derive the most relevant country code from either the dispatched event or
     * the persisted checkout session so pricing stays accurate.
     */
    private function resolveCountryCode(?int $addressId = null): ?string
    {
        if ($addressId !== null) {
            $address = Address::query()
                ->when(Auth::check(), static fn ($query) => $query->where('user_id', Auth::id()))
                ->find($addressId);

            $code = $address?->getAttribute('country_code');
            if (is_string($code) && $code !== '') {
                return $code;
            }
        }

        $storedCountry = data_get(session()->get('checkout'), 'shipping_address.country_code');

        return is_string($storedCountry) && $storedCountry !== '' ? $storedCountry : null;
    }
}
