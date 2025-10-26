<?php

declare(strict_types=1);

namespace App\Livewire\Components\Checkout;

use App\Models\Address;
use App\Models\CartItem;
use App\Models\ShippingOption;
use App\Services\Discounts\DiscountEngine;
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
 * Delivery step of the checkout wizard responsible for presenting shipping options.
 *
 * @property array<int, array{id:int,name:string,description:string,price:float,resolved_price:float,formatted_price:string,estimated_delivery:string,currency_code:string,badges:array<int, array{type:string,label:string}>}> $resolvedOptions
 * @property bool                                                                                                                                                $isResolving
 * @property int|string|null                                                                                                                                      $currentSelected
 */
final class Delivery extends StepComponent
{
    /**
     * Normalised shipping options returned by the resolver, including dynamic pricing metadata.
     *
     * @var array<int, array{id:int,name:string,description:string,price:float,resolved_price:float,formatted_price:string,estimated_delivery:string,currency_code:string,badges:array<int, array{type:string,label:string}>}>
     */
    public array $resolvedOptions = [];

    /**
     * Track whether the component is currently resolving shipping options so the
     * view can disable actions and show optimistic UI feedback.
     */
    public bool $isResolving = false;

    #[Validate('required', message: 'You must select a delivery method')]
    public int|string|null $currentSelected = null;

    /**
     * Initialise the component by seeding the current selection and resolving options.
     */
    public function mount(): void
    {
        $storedOption = data_get(session()->get('checkout'), 'shipping_option');
        $this->currentSelected = $storedOption ? data_get($storedOption, '0.id') : null;

        $this->resolveOptions(data_get(session()->get('checkout'), 'shipping_address.id'));
    }

    /**
     * Refresh shipping options whenever the shipping address changes upstream.
     */
    #[On('shipping-address-updated')]
    public function handleShippingAddressUpdated(?int $shippingAddressId = null): void
    {
        $this->resetErrorBag('currentSelected');
        $this->resetValidation();

        $this->resolveOptions($shippingAddressId, true);
    }

    /**
     * Handle address-driven shipping refresh requests triggered from the preceding step.
     */
    #[On('checkout-address-updated')]
    public function handleCheckoutAddressUpdated(?int $addressId = null, ?string $countryCode = null): void
    {
        $this->resolveOptions($addressId, true);
    }

    /**
     * Persist the selected shipping option (with computed price) to the checkout session.
     */
    public function save(): void
    {
        $this->validate();
        session()->forget('checkout.shipping_option');

        $optionData = $this->findResolvedOption((int) $this->currentSelected);
        if ($optionData === null) {
            $this->addError('currentSelected', __('Please choose a valid delivery option.'));

            return;
        }

        $optionModel = ShippingOption::query()->find($optionData['id']);
        if ($optionModel === null) {
            $this->addError('currentSelected', __('Please choose a valid delivery option.'));

            return;
        }

        $payload = $this->buildPersistablePayload($optionData, $optionModel);

        session()->put('checkout.shipping_option', [$payload]);

        $this->dispatch('shippingOptionSaved', $payload);
        $this->dispatch('cart-price-update');

        $this->nextStep();
    }

    /**
     * Optimistically update the selection when a radio item is clicked so the UI reflects the choice.
     */
    public function selectOption(int $optionId): void
    {
        if ($this->findResolvedOption($optionId) === null) {
            return;
        }

        $this->currentSelected = $optionId;
    }

    /**
     * Provide wizard metadata so the parent stepper can indicate completion state.
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

    public function render(): View
    {
        return view('livewire.components.checkout.delivery');
    }

    /**
     * Resolve the current shipping options for the active cart and address.
     */
    public function resolveOptions(?int $shippingAddressId = null, bool $emitLifecycleEvents = false): void
    {
        $this->isResolving = true;
        session()->forget('checkout.shipping_option');

        if ($emitLifecycleEvents) {
            $this->dispatch('shipping-recalculation-started');
        }

        $countryCode = $this->resolveCountryCode($shippingAddressId);

        $resolver = app(ShippingOptionResolver::class);
        $cartItems = $this->getCartItems();
        /** @var SupportCollection<int, array{id:int,name:string,description?:string|null,price:float,formatted_price?:string|null,estimated_delivery?:string|null,currency?:string|null}> $resolved */
        $resolved = $resolver->resolve($cartItems->collect(), $countryCode);

        $optionIds = $resolved
            ->pluck('id')
            ->filter(static fn ($id): bool => is_numeric($id))
            ->map(static fn ($id): int => (int) $id);

        /** @var SupportCollection<int, ShippingOption> $optionModels */
        $optionModels = ShippingOption::query()->whereIn('id', $optionIds)->get()->keyBy('id');

        $this->resolvedOptions = $resolved
            ->map(function (array $option) use ($optionModels): array {
                $identifier = (int) $option['id'];
                $model = $optionModels->get($identifier);
                $baseAmount = (float) $option['price'];
                $currency = (string) ($option['currency'] ?? $model?->currency_code ?? current_currency());

                $discount = $this->calculateShippingDiscount($baseAmount);
                $finalAmount = max(0.0, round($baseAmount - $discount, 2));
                $formattedFinal = app_money_format($finalAmount, $currency);
                $formattedResolved = app_money_format($baseAmount, $currency);

                return [
                    'id'                        => $identifier,
                    'name'                      => (string) ($option['name'] ?? $model?->name ?? ''),
                    'description'               => (string) ($option['description'] ?? $model?->description ?? ''),
                    'price'                     => $finalAmount,
                    'resolved_price'            => $baseAmount,
                    'resolved_formatted_price'  => $formattedResolved,
                    'formatted_price'           => $formattedFinal,
                    'estimated_delivery'        => (string) ($option['estimated_delivery'] ?? $model?->estimated_delivery_text ?? ''),
                    'currency_code'             => $currency,
                    'badges'                    => $this->buildBadges($baseAmount, $finalAmount, $discount, $currency),
                ];
            })
            ->values()
            ->all();

        if ($this->currentSelected !== null && $this->findResolvedOption((int) $this->currentSelected) === null) {
            $this->currentSelected = null;
        }

        if ($this->currentSelected === null && $this->resolvedOptions !== []) {
            $firstIdentifier = Arr::get($this->resolvedOptions, '0.id');
            $this->currentSelected = is_numeric($firstIdentifier) ? (int) $firstIdentifier : null;
        }

        $this->isResolving = false;

        if ($emitLifecycleEvents) {
            $this->dispatch('shipping-recalculation-finished');
        }
    }

    /**
     * Attempt to find the resolved option for a specific identifier.
     *
     * @return array{id:int,name:string,description:string,price:float,formatted_price:string,estimated_delivery:string,currency_code:string,badges:array<int, array{type:string,label:string}>,resolved_price:float}|null
     */
    private function findResolvedOption(int $optionId): ?array
    {
        foreach ($this->resolvedOptions as $option) {
            if (! is_array($option)) {
                continue;
            }

            $identifier = $option['id'] ?? null;
            if (is_numeric($identifier) && (int) $identifier === $optionId) {
                /** @var array{id:int,name:string,description:string,price:float,formatted_price:string,estimated_delivery:string,currency_code:string,badges:array<int, array{type:string,label:string}>,resolved_price:float} $option */
                return $option;
            }
        }

        return null;
    }

    /**
     * Build the payload stored in the session for the selected shipping option.
     *
     * @param  array{id:int,name:string,description:string,price:float,formatted_price:string,estimated_delivery:string,currency_code:string,badges:array<int, array{type:string,label:string}>,resolved_price:float} $optionData
     * @return array{id:int,name:string,description:string,price:float,formatted_price:string,estimated_delivery:string,currency_code:string,badges:array<int, array{type:string,label:string}>,resolved_price:float}
     */
    private function buildPersistablePayload(array $optionData, ShippingOption $optionModel): array
    {
        $baseAmount = (float) ($optionData['resolved_price'] ?? $optionData['price'] ?? 0.0);
        $currency = (string) ($optionData['currency_code'] ?? $optionModel->currency_code ?? current_currency());

        $discount = $this->calculateShippingDiscount($baseAmount);
        $finalAmount = max(0.0, round($baseAmount - $discount, 2));

        return [
            'id'                 => $optionModel->getKey(),
            'name'               => (string) ($optionData['name'] ?? $optionModel->name),
            'description'        => (string) ($optionData['description'] ?? $optionModel->description ?? ''),
            'price'              => $finalAmount,
            'resolved_price'     => $baseAmount,
            'formatted_price'    => app_money_format($finalAmount, $currency),
            'estimated_delivery' => (string) ($optionData['estimated_delivery'] ?? $optionModel->estimated_delivery_text ?? ''),
            'currency_code'      => $currency,
            'badges'             => $this->buildBadges($baseAmount, $finalAmount, $discount, $currency),
        ];
    }

    /**
     * Retrieve cart items for the active shopper so the shipping resolver can inspect weights.
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
     * Resolve the most relevant country code from either the dispatched event or stored checkout state.
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

    /**
     * Calculate a shipping discount for the provided base amount using the discount engine.
     */
    private function calculateShippingDiscount(float $baseAmount): float
    {
        if ($baseAmount <= 0.0) {
            return 0.0;
        }

        $engine = app(DiscountEngine::class);
        $channelUrl = config('app.url');
        $cartSubtotal = session('cart.subtotal');
        $subtotal = is_numeric($cartSubtotal) ? (float) $cartSubtotal : 0.0;

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

        return is_numeric($discountValue) ? max(0.0, (float) $discountValue) : 0.0;
    }

    /**
     * Derive a set of badges describing how the shipping price is constrained.
     *
     * @return array<int, array{type:string,label:string}>
     */
    private function buildBadges(float $baseAmount, float $finalAmount, float $discountAmount, string $currency): array
    {
        $badges = [];

        if ($finalAmount <= 0.0) {
            $badges[] = [
                'type'  => 'free',
                'label' => __('ecommerce.free_shipping'),
            ];

            return $badges;
        }

        if ($discountAmount > 0.0 && $finalAmount < $baseAmount) {
            $badges[] = [
                'type'  => 'capped',
                'label' => __('Shipping capped at :amount', ['amount' => app_money_format($finalAmount, $currency)]),
            ];
        }

        return $badges;
    }
}
