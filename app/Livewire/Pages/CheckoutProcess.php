<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Data\Pricing\PriceBreakdown;
use App\Data\Shipping\ShippingOptionData;
use App\Enums\AddressType;
use App\Enums\PaymentMethod;
use App\Mail\OrderConfirmationMail;
use App\Models\Address;
use App\Models\CartItem;
use App\Models\Country;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderShipping;
use App\Models\Product;
use App\Models\ShippingOption;
use App\Models\User;
use App\Services\Cart\CartLifecycleService;
use App\Services\Discounts\DiscountEngine;
use App\Services\Pricing\PriceCalculator;
use App\Services\Shipping\ShippingOptionResolver;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

/**
 * CheckoutProcess orchestrates the four-step checkout wizard.
 *
 * Livewire component for CheckoutProcess with reactive frontend functionality, real-time updates, and user interaction handling.
 *
 * @property array{first_name:string,last_name:string,email:string,phone:string,address:string,city:string,postal_code:string,company:?string,country:string,region:?string} $billing
 * @property bool                                                                                                                                                            $sameAsShipping
 * @property array{first_name:string,last_name:string,address:string,city:string,postal_code:string,company:?string,country:string,region:?string}                           $shipping
 * @property string                                                                                                                                                          $notes
 * @property int                                                                                                                                                             $currentStep
 */
final class CheckoutProcess extends Component
{
    #[Validate('required|array')]
    public array $billing = [
        'first_name'  => '',
        'last_name'   => '',
        'email'       => '',
        'phone'       => '',
        'address'     => '',
        'city'        => '',
        'postal_code' => '',
        'company'     => '',
        'country'     => 'LT',
        'region'      => '',
    ];

    public bool $sameAsShipping = true;

    #[Validate('required|array')]
    public array $shipping = [
        'first_name'  => '',
        'last_name'   => '',
        'address'     => '',
        'city'        => '',
        'postal_code' => '',
        'company'     => '',
        'country'     => 'LT',
        'region'      => '',
    ];

    #[Validate('nullable|string')]
    public string $notes = '';

    public int $currentStep = 1;

    public ?int $selectedShippingOption = null;

    public float $selectedShippingPrice = 0.0;

    /** @var list<array{id:int,name:string,price:float,formatted_price:string,estimated_delivery:string,currency?:string|null,original_price?:float,badges?:array<int, array{type:string,label:string}>}> */
    public array $availableShippingOptions = [];

    /**
     * Flag indicating whether the resolver is currently recalculating options.
     */
    public bool $isResolvingShippingOptions = false;

    /** @var array{id:int,name:string,price:float,formatted_price?:string|null,estimated_delivery?:string|null,currency_code?:string|null,original_price?:float,badges?:array<int, array{type:string,label:string}>}|array{} */
    public array $selectedShippingSnapshot = [];

    /** @var array<string, string> */
    public array $paymentMethods = [];

    public string $selectedPaymentMethod = '';

    public function mount(): void
    {
        if (auth()->check()) {
            $user = auth()->user();
            if ($user instanceof User) {
                // Populate billing fields from the authenticated profile so returning customers skip manual entry.
                $this->billing['first_name'] = (string) ($user->first_name ?? '');
                $this->billing['last_name'] = (string) ($user->last_name ?? '');
                $this->billing['email'] = (string) $user->email;
                $this->billing['phone'] = (string) ($user->phone_number ?? '');
            }
            $this->hydrateSavedAddresses();
        }

        $this->initialisePaymentMethods();
        $this->resolveShippingOptions();
    }

    /**
     * Allow the UI to jump to a specific step while clamping the range.
     */
    public function toStep(int $targetStep): void
    {
        // Clamp the target step so we never render beyond the wizard bounds.
        $targetStep = max(1, min(3, $targetStep));
        $movingForward = $targetStep > $this->currentStep;

        if ($movingForward) {
            $this->validateCurrentStep();
        }

        if ($movingForward && $this->currentStep === 1) {
            $this->shipping = $this->normalizedShippingAddress();
            // Persist address data for authenticated shoppers and refresh the available shipping matrix.
            $this->persistAuthenticatedAddresses();
            $this->resolveShippingOptions();
        }

        if ($movingForward && $this->currentStep === 2) {
            // Lock in the shipping selection before moving to the payment stage.
            $this->ensureShippingSelection();
        }

        if ($targetStep < $this->currentStep) {
            // Clear validation errors when the shopper navigates backwards.
            $this->resetErrorBag();
        }

        $this->currentStep = $targetStep;

        // Notify listening components (like the order summary) that the
        // active step changed, enabling them to adjust their own UI state.
        $this->dispatch('checkout-step-changed', step: $this->currentStep);
    }

    /**
     * When the delivery component persists a shipping option we can move
     * forward to the payment selection automatically.
     */
    #[On('shippingOptionSaved')]
    public function handleShippingSaved(): void
    {
        $this->toStep(3);
    }

    /**
     * React to downstream recalculation events so the order summary knows
     * to refresh its totals (shipping rate changes, discounts, etc.).
     */
    #[On('cart-price-update')]
    #[On('orderTotalsRecalculated')]
    public function handleTotalsChanged(): void
    {
        // Emit a dedicated event consumed by the order summary component
        // which triggers a `$refresh` without forcing a full page rerender.
        $this->dispatch('refreshOrderSummary');
    }

    /**
     * Handle validateCurrentStep functionality with proper error handling.
     */
    public function validateCurrentStep(): void
    {
        match ($this->currentStep) {
            1 => $this->validate($this->addressStepRules()),
            2 => $this->validate($this->deliveryStepRules()),
            3 => $this->validate([
                'selectedPaymentMethod' => [
                    'required',
                    Rule::in(array_keys($this->paymentMethods)),
                ],
            ]),
            default => null,
        };
    }

    /**
     * Handle placeOrder functionality with proper error handling.
     */
    public function placeOrder(): void
    {
        if ($this->currentStep < 3) {
            // Force customers through the payment screen before completing the checkout.
            $this->toStep(3);

            return;
        }

        $this->validate($this->rules());
        $cartItems = $this->getCartItems();
        if ($cartItems->isEmpty()) {
            $this->addError('cart', __('messages.your_cart_is_empty'));

            return;
        }
        $createdOrder = DB::transaction(function () use ($cartItems): Order {
            // Resolve the final order payload, persist line items, and dispatch the email confirmation.
            $order = $this->createOrder($cartItems);
            $this->createOrderItems($order, $cartItems);
            $this->createOrUpdateOrderShipping($order);
            $this->queueOrderConfirmation($order);
            app(CartLifecycleService::class)->clearAfterCheckout($this->authenticatedUserId(), Session::getId());

            return $order;
        });

        session()->flash('order_number', $createdOrder->number);

        if ($this->selectedPaymentMethod === PaymentMethod::MONTONIO->value) {
            try {
                $montonioUrl = app(\App\Services\Payments\MontonioService::class)->getPaymentUrl($createdOrder);
                $this->redirect($montonioUrl);

                return;
            } catch (Exception $e) {
                // If Montonio API fails, fall back to failure page or order confirmation with an error
                session()->flash('error', __('messages.payment_initialization_failed', ['error' => $e->getMessage()]));
                $this->redirect(route('checkout.confirmation', $createdOrder->number));

                return;
            }
        }

        $this->redirect(route('checkout.confirmation', $createdOrder->number));
    }

    /**
     * Alias invoked from the payment step CTA so Livewire targets stay descriptive.
     */
    public function submitOrder(): void
    {
        $this->placeOrder();
    }

    /**
     * @param EloquentCollection<int, CartItem> $cartItems
     */
    private function createOrder(EloquentCollection $cartItems): Order
    {
        $breakdown = $this->calculateBreakdown($cartItems);
        $selectedOption = $this->resolveSelectedShippingOptionModel();
        $shippingAddress = $this->enrichShippingAddressWithDeliveryPlace($this->getShippingAddress());

        return Order::create([
            'number'             => 'LT-' . strtoupper(uniqid()),
            'user_id'            => $this->authenticatedUserId(),
            'status'             => 'pending',
            'subtotal'           => $breakdown->subtotal,
            'tax_amount'         => $breakdown->tax,
            'shipping_amount'    => $breakdown->shipping,
            'discount_amount'    => $breakdown->discount,
            'total'              => $breakdown->total,
            'currency'           => $breakdown->currency,
            'billing_address'    => $this->getBillingAddress(),
            'shipping_address'   => $shippingAddress,
            'notes'              => $this->notes,
            'shipping_option_id' => $selectedOption?->getKey(),
            'payment_method'     => $this->selectedPaymentMethod,
        ]);
    }

    /**
     * @param EloquentCollection<int, CartItem> $cartItems
     */
    private function createOrderItems(Order $order, EloquentCollection $cartItems): void
    {
        /** @var CartItem $cartItem */
        foreach ($cartItems as $cartItem) {
            OrderItem::create([
                'order_id'     => $order->id,
                'product_id'   => $cartItem->product_id,
                'product_name' => (string) $cartItem->product?->name,
                'product_sku'  => (string) $cartItem->product?->sku,
                'quantity'     => (int) $cartItem->quantity,
                'price'        => (float) $cartItem->price,
                'total'        => (float) $cartItem->price * (int) $cartItem->quantity,
            ]);
        }
    }

    /**
     * Persist shipment information for the created order, including Venipak pickup place metadata.
     */
    private function createOrUpdateOrderShipping(Order $order): void
    {
        $selected = $this->selectedShippingSnapshot;
        $isVenipak = $this->isVenipakPickupSelection($selected);
        $deliveryPlaceName = is_string($selected['name'] ?? null) ? (string) $selected['name'] : null;
        $deliveryPlaceAddress = is_string($selected['description'] ?? null)
            ? (string) $selected['description']
            : (is_string($selected['estimated_delivery'] ?? null) ? (string) $selected['estimated_delivery'] : null);
        $deliveryPlaceId = isset($selected['id']) ? (string) $selected['id'] : null;
        $price = isset($selected['price']) && is_numeric($selected['price']) ? (float) $selected['price'] : (float) $order->shipping_amount;
        $basePrice = isset($selected['original_price']) && is_numeric($selected['original_price']) ? (float) $selected['original_price'] : $price;

        $metadata = [
            'delivery_provider'      => $isVenipak ? 'venipak' : 'storefront',
            'delivery_place_id'      => $deliveryPlaceId,
            'delivery_place_name'    => $deliveryPlaceName,
            'delivery_place_address' => $deliveryPlaceAddress,
        ];

        OrderShipping::query()->updateOrCreate(
            ['order_id' => $order->id],
            [
                'carrier_name'    => $isVenipak ? 'Venipak' : (string) ($selected['carrier_name'] ?? ''),
                'shipping_method' => $deliveryPlaceName,
                'service'         => $isVenipak ? 'pickup_point' : (string) ($selected['service'] ?? ''),
                'service_type'    => $isVenipak ? 'pickup' : (string) ($selected['service_type'] ?? ''),
                'cost'            => $price,
                'base_cost'       => $basePrice,
                'total_cost'      => $price,
                'metadata'        => $metadata,
                'status'          => 'pending',
                'is_delivered'    => false,
            ]
        );
    }

    /**
     * @return EloquentCollection<int, CartItem>
     */
    private function getCartItems(): EloquentCollection
    {
        $sessionId = (string) Session::getId();
        $authId = auth()->id();
        $userId = is_numeric($authId) ? (int) $authId : null;

        // Resolve persisted cart rows using both ownership contexts so authenticated
        // users keep seeing items that were attached before/after session rotation.
        $items = CartItem::withoutGlobalScopes()
            ->with(['product', 'productVariant', 'variant'])
            ->where(function (Builder $query) use ($sessionId, $userId): void {
                $hasCondition = false;

                if ($sessionId !== '') {
                    $query->where('session_id', $sessionId);
                    $hasCondition = true;
                }

                if ($userId !== null) {
                    if ($hasCondition) {
                        $query->orWhere('user_id', $userId);
                    } else {
                        $query->where('user_id', $userId);
                        $hasCondition = true;
                    }
                }

                if (! $hasCondition) {
                    $query->whereRaw('1 = 0');
                }
            })
            ->orderBy('created_at')
            ->get();

        if ($items->isNotEmpty()) {
            return $items;
        }

        // Fall back to the serialized session cart so checkout stays in sync with
        // `/lt/cart` even when rows have not been persisted yet.
        $sessionCart = Session::get('cart', []);
        if (! is_array($sessionCart) || $sessionCart === []) {
            return new EloquentCollection;
        }

        $productIds = collect($sessionCart)
            ->pluck('product_id')
            ->filter(static fn ($value): bool => is_numeric($value) && (int) $value > 0)
            ->map(static fn ($value): int => (int) $value)
            ->unique()
            ->values();

        /** @var SupportCollection<int, Product> $products */
        $products = Product::withoutGlobalScopes()
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        $hydrated = collect($sessionCart)
            ->filter(static fn ($item): bool => is_array($item) && isset($item['product_id']) && is_numeric($item['product_id']))
            ->map(static function (array $item) use ($products): CartItem {
                $productId = (int) $item['product_id'];
                $price = isset($item['price']) && is_numeric($item['price'])
                    ? (float) $item['price']
                    : (float) ($item['unit_price'] ?? 0.0);
                $quantity = isset($item['quantity']) && is_numeric($item['quantity'])
                    ? max(1, (int) $item['quantity'])
                    : 1;

                $model = CartItem::make([
                    'product_id'         => $productId,
                    'product_variant_id' => isset($item['product_variant_id']) && is_numeric($item['product_variant_id'])
                        ? (int) $item['product_variant_id']
                        : null,
                    'variant_id' => isset($item['variant_id']) && is_numeric($item['variant_id'])
                        ? (int) $item['variant_id']
                        : null,
                    'quantity' => $quantity,
                    'price' => $price,
                    'unit_price' => $price,
                    'product_snapshot' => [
                        'name'  => $item['name'] ?? null,
                        'sku'   => $item['sku'] ?? null,
                        'image' => $item['image'] ?? null,
                    ],
                    'notes' => $item['notes'] ?? null,
                ]);

                $product = $products->get($productId);
                if ($product instanceof Product) {
                    $model->setRelation('product', $product);
                }

                return $model;
            })
            ->values()
            ->all();

        return new EloquentCollection($hydrated);
    }

    /**
     * @return array<string, string|null>
     */
    private function getBillingAddress(): array
    {
        // Include both the localized country name and the ISO code to satisfy downstream integrations.
        $country = $this->resolveCountryDetails($this->billing['country'] ?? null);

        return [
            'first_name'   => (string) ($this->billing['first_name'] ?? ''),
            'last_name'    => (string) ($this->billing['last_name'] ?? ''),
            'company'      => $this->billing['company'] ?? null,
            'email'        => (string) ($this->billing['email'] ?? ''),
            'phone'        => (string) ($this->billing['phone'] ?? ''),
            'address'      => (string) ($this->billing['address'] ?? ''),
            'city'         => (string) ($this->billing['city'] ?? ''),
            'region'       => $this->billing['region'] ?? null,
            'postal_code'  => (string) ($this->billing['postal_code'] ?? ''),
            'country'      => $country['name'],
            'country_code' => $country['code'],
        ];
    }

    /**
     * @return array<string, string|null>
     */
    private function getShippingAddress(): array
    {
        if ($this->sameAsShipping) {
            return $this->getBillingAddress();
        }

        $shipping = $this->normalizedShippingAddress();
        $country = $this->resolveCountryDetails($shipping['country'] ?? null);

        return [
            'first_name'   => (string) ($shipping['first_name'] ?? ''),
            'last_name'    => (string) ($shipping['last_name'] ?? ''),
            'company'      => $shipping['company'] ?? null,
            'address'      => (string) ($shipping['address'] ?? ''),
            'city'         => (string) ($shipping['city'] ?? ''),
            'region'       => $shipping['region'] ?? null,
            'postal_code'  => (string) ($shipping['postal_code'] ?? ''),
            'country'      => $country['name'],
            'country_code' => $country['code'],
        ];
    }

    /**
     * Render the Livewire component view with current state.
     */
    public function render(): View
    {
        $items = $this->getCartItems();
        $breakdown = $this->calculateBreakdown($items);
        $countries = Country::query()
            ->orderBy('name')
            ->get()
            ->map(static function (Country $country): array {
                // Surface the localized label and ISO code so select fields remain human-friendly.
                return [
                    'code' => $country->code,
                    'name' => $country->translated_name,
                ];
            })
            ->unique('code')
            ->values();

        /** @var view-string $view */
        $view = 'livewire.pages.checkout-process';

        return view($view, [
            'cartItems' => $items,
            'summary'   => $breakdown->toSummary(),
            'countries' => $countries,
        ]);
    }

    /**
     * @param EloquentCollection<int, CartItem> $cartItems
     */
    private function calculateBreakdown(EloquentCollection $cartItems): PriceBreakdown
    {
        $subtotal = (float) $cartItems->sum(static fn (CartItem $item): float => (float) $item->price * (int) $item->quantity);
        $shippingCost = $this->determineShippingCost($cartItems);

        return app(PriceCalculator::class)->breakdown($subtotal, shipping: $shippingCost);
    }

    /**
     * Persist customer address data when an authenticated user proceeds through the checkout.
     */
    private function persistAuthenticatedAddresses(): void
    {
        if (! auth()->check()) {
            return;
        }

        // Upsert billing address details so returning customers can reuse the stored records.
        $billingPayload = array_merge($this->getBillingAddress(), [
            'first_name'     => $this->billing['first_name'] ?? null,
            'last_name'      => $this->billing['last_name'] ?? null,
            'address_line_1' => $this->billing['address'] ?? null,
            'city'           => $this->billing['city'] ?? null,
            'state'          => $this->billing['region'] ?? null,
            'postal_code'    => $this->billing['postal_code'] ?? null,
            'email'          => $this->billing['email'] ?? null,
            'phone'          => $this->billing['phone'] ?? null,
            'country_code'   => strtoupper((string) ($this->billing['country'] ?? '')),
            'is_billing'     => true,
        ]);

        /** @var array<string, mixed> $billingPayload */
        Address::updateOrCreate(
            ['user_id' => $this->authenticatedUserId(), 'type' => AddressType::BILLING],
            $billingPayload
        );

        // When the shipping address differs, store it separately to power delivery dropdowns in future visits.
        if (! $this->sameAsShipping) {
            $shippingPayload = array_merge($this->getShippingAddress(), [
                'first_name'     => $this->shipping['first_name'] ?? null,
                'last_name'      => $this->shipping['last_name'] ?? null,
                'address_line_1' => $this->shipping['address'] ?? null,
                'city'           => $this->shipping['city'] ?? null,
                'state'          => $this->shipping['region'] ?? null,
                'postal_code'    => $this->shipping['postal_code'] ?? null,
                'email'          => $this->billing['email'] ?? null,
                'phone'          => $this->billing['phone'] ?? null,
                'country_code'   => strtoupper((string) ($this->shipping['country'] ?? '')),
                'is_shipping'    => true,
            ]);

            /** @var array<string, mixed> $shippingPayload */
            Address::updateOrCreate(
                ['user_id' => $this->authenticatedUserId(), 'type' => AddressType::SHIPPING],
                $shippingPayload
            );
        }
    }

    /**
     * Populate billing and shipping form fields from the latest saved addresses when available.
     */
    private function hydrateSavedAddresses(): void
    {
        /** @var EloquentCollection<int, Address> $addresses */
        $addresses = Address::query()
            ->where('user_id', $this->authenticatedUserId())
            ->whereIn('type', ['billing', 'shipping'])
            ->get()
            ->keyBy(static function (Address $address): string {
                $type = $address->getAttribute('type');

                if ($type instanceof AddressType) {
                    return $type->value;
                }

                if (is_string($type)) {
                    return $type;
                }

                return 'billing';
            });

        $billing = $addresses['billing'] ?? null;
        if ($billing instanceof Address) {
            // Keep previously saved billing information so repeat customers move faster.
            $this->billing['first_name'] = (string) ($billing->getAttribute('first_name') ?? $this->billing['first_name']);
            $this->billing['last_name'] = (string) ($billing->getAttribute('last_name') ?? $this->billing['last_name']);
            $this->billing['address'] = (string) ($billing->getAttribute('address_line_1') ?? $this->billing['address']);
            $this->billing['city'] = (string) ($billing->getAttribute('city') ?? $this->billing['city']);
            $this->billing['postal_code'] = (string) ($billing->getAttribute('postal_code') ?? $this->billing['postal_code']);
            $company = $billing->getAttribute('company_name') ?? $billing->getAttribute('company');
            if (is_string($company)) {
                $this->billing['company'] = $company;
            }
            $state = $billing->getAttribute('state');
            if (is_string($state)) {
                $this->billing['region'] = $state;
            }
            $billingCountry = $billing->getAttribute('country_code');
            if (is_string($billingCountry)) {
                $this->billing['country'] = strtoupper($billingCountry);
            }
            $billingPhone = $billing->getAttribute('phone');
            if (is_string($billingPhone)) {
                $this->billing['phone'] = $billingPhone;
            }
            $billingEmail = $billing->getAttribute('email');
            if (is_string($billingEmail)) {
                $this->billing['email'] = $billingEmail;
            }
        }

        $shipping = $addresses['shipping'] ?? null;
        if ($shipping instanceof Address) {
            $shippingFirstName = trim((string) ($shipping->getAttribute('first_name') ?? ''));
            $shippingLastName = trim((string) ($shipping->getAttribute('last_name') ?? ''));
            $shippingAddressLine = trim((string) ($shipping->getAttribute('address_line_1') ?? ''));
            $shippingCity = trim((string) ($shipping->getAttribute('city') ?? ''));
            $shippingPostalCode = trim((string) ($shipping->getAttribute('postal_code') ?? ''));
            $shippingCountry = strtoupper(trim((string) ($shipping->getAttribute('country_code') ?? '')));
            $hasCompleteShippingAddress = $shippingFirstName !== ''
                && $shippingLastName !== ''
                && $shippingAddressLine !== ''
                && $shippingCity !== ''
                && $shippingPostalCode !== ''
                && $shippingCountry !== '';

            if (! $hasCompleteShippingAddress) {
                // Keep using billing details in checkout when stored shipping data is incomplete.
                return;
            }

            // Mirror stored shipping data and disable the "same as billing" toggle when records differ.
            $this->sameAsShipping = false;
            $this->shipping['first_name'] = $shippingFirstName;
            $this->shipping['last_name'] = $shippingLastName;
            $this->shipping['address'] = $shippingAddressLine;
            $this->shipping['city'] = $shippingCity;
            $this->shipping['postal_code'] = $shippingPostalCode;
            $shippingCompany = $shipping->getAttribute('company_name') ?? $shipping->getAttribute('company');
            if (is_string($shippingCompany)) {
                $this->shipping['company'] = $shippingCompany;
            }
            $shippingState = $shipping->getAttribute('state');
            if (is_string($shippingState)) {
                $this->shipping['region'] = $shippingState;
            }
            $this->shipping['country'] = $shippingCountry;
        }
    }

    /**
     * Prepare the list of supported payment methods for the checkout experience.
     */
    private function initialisePaymentMethods(): void
    {
        // Keep storefront checkout restricted to Montonio only.
        $this->paymentMethods = [
            PaymentMethod::MONTONIO->value => trans('enums.payment_method.montonio'),
        ];

        $this->selectedPaymentMethod = $this->selectedPaymentMethod !== ''
            ? $this->selectedPaymentMethod
            : PaymentMethod::MONTONIO->value;
    }

    /**
     * Refresh the available shipping options based on the current cart, destination, and package weight.
     */
    public function resolveShippingOptions(mixed $resetSelection = false): void
    {
        $shouldResetSelection = is_bool($resetSelection)
            ? $resetSelection
            : in_array($resetSelection, [1, '1', 'true', 'on'], true);

        if ($shouldResetSelection) {
            $this->selectedShippingOption = null;
            $this->selectedShippingPrice = 0.0;
            $this->selectedShippingSnapshot = [];
            session()->forget('checkout.shipping_option');
        }

        $this->isResolvingShippingOptions = true;

        try {
            $cartItems = $this->getCartItems();
            $resolver = app(ShippingOptionResolver::class);
            $shippingContext = $this->normalizedShippingAddress();
            $this->shipping = $shippingContext;
            $options = $resolver->resolve(
                collect($cartItems->all()),
                $shippingContext['country'] ?? null,
                $shippingContext
            );

            $this->availableShippingOptions = $options
                ->map(function ($option): array {
                    $payload = $option instanceof ShippingOptionData ? $option->toArray() : (array) $option;
                    $payload['id'] = (int) ($payload['id'] ?? 0);
                    $baseAmount = (float) ($payload['price'] ?? 0.0);
                    $currency = 'EUR';
                    $discount = $this->calculateShippingDiscount($baseAmount);
                    $finalAmount = max(0.0, round($baseAmount - $discount, 2));

                    $payload['original_price'] = $baseAmount;
                    $payload['price'] = $finalAmount;
                    $payload['formatted_price'] = app_money_format($finalAmount, $currency);
                    $payload['currency'] = $currency;
                    $payload['currency_code'] = $currency;
                    $payload['badges'] = $this->buildShippingBadges($baseAmount, $finalAmount, $discount, $currency);

                    return $payload;
                })
                ->values()
                ->all();
        } finally {
            $this->isResolvingShippingOptions = false;
        }

        if ($this->availableShippingOptions === []) {
            $this->selectedShippingOption = null;
            $this->selectedShippingPrice = 0.0;
            $this->selectedShippingSnapshot = [];
            $this->addError('selectedShippingOption', __('messages.no_shipping_methods_are_available_for_the_provided_address'));

            return;
        }

        $selectedExists = collect($this->availableShippingOptions)
            ->contains(fn (array $option): bool => $option['id'] === (int) $this->selectedShippingOption);

        if (! $selectedExists) {
            $this->selectedShippingOption = null;
        }

        $this->resetErrorBag('selectedShippingOption');

        $this->updateSelectedShippingPrice();
    }

    /**
     * Resolve a friendly country label and normalised ISO code for address storage.
     *
     * @return array{code:string,name:string}
     */
    private function resolveCountryDetails(?string $countryCode): array
    {
        $code = strtoupper((string) $countryCode);

        /** @var Country|null $country */
        $country = Country::query()
            ->withoutGlobalScopes()
            ->where(static function ($query) use ($code): void {
                $query->where('cca2', $code)->orWhere('code', $code);
            })
            ->first();

        if ($country instanceof Country) {
            // Prefer translated names so storefront and email templates stay localised.
            return ['code' => $code, 'name' => $country->translated_name];
        }

        // Fall back to the uppercased ISO code when no database record exists for the provided value.
        return ['code' => $code, 'name' => $code];
    }

    /**
     * Ensure the shipping option stored on the component stays in sync with the computed price.
     */
    private function updateSelectedShippingPrice(): void
    {
        $selected = collect($this->availableShippingOptions)
            ->firstWhere('id', (int) $this->selectedShippingOption);

        if (is_array($selected)) {
            $this->selectedShippingPrice = (float) ($selected['price'] ?? 0.0);
            $this->selectedShippingSnapshot = $selected;
            session()->put('checkout.shipping_option', [$selected]);
        } else {
            $this->selectedShippingPrice = 0.0;
            $this->selectedShippingSnapshot = [];
            session()->forget('checkout.shipping_option');
        }
    }

    /**
     * Enforce that a valid shipping option is selected before moving to payment.
     */
    private function ensureShippingSelection(): void
    {
        if ($this->selectedShippingOption === null) {
            $this->addError('selectedShippingOption', __('messages.please_choose_a_shipping_method_to_continue'));
        }

        $this->updateSelectedShippingPrice();
    }

    /**
     * Resolve the shipping option model for persistence and internal tracking.
     */
    private function resolveSelectedShippingOptionModel(): ?ShippingOption
    {
        if ($this->selectedShippingOption === null) {
            return null;
        }

        return ShippingOption::query()->find($this->selectedShippingOption);
    }

    /**
     * Add selected delivery place details to the stored shipping address payload.
     *
     * @param  array<string, string|null> $shippingAddress
     * @return array<string, string|null>
     */
    private function enrichShippingAddressWithDeliveryPlace(array $shippingAddress): array
    {
        $selected = $this->selectedShippingSnapshot;

        if ($selected === []) {
            return $shippingAddress;
        }

        $shippingAddress['delivery_place_id'] = isset($selected['id']) ? (string) $selected['id'] : null;
        $shippingAddress['delivery_place_name'] = is_string($selected['name'] ?? null) ? (string) $selected['name'] : null;
        $shippingAddress['delivery_place_address'] = is_string($selected['description'] ?? null)
            ? (string) $selected['description']
            : (is_string($selected['estimated_delivery'] ?? null) ? (string) $selected['estimated_delivery'] : null);
        $shippingAddress['delivery_provider'] = $this->isVenipakPickupSelection($selected) ? 'venipak' : 'storefront';

        return $shippingAddress;
    }

    /**
     * Determine whether selected delivery option is a Venipak pickup place.
     *
     * @param array<string, mixed> $option
     */
    private function isVenipakPickupSelection(array $option): bool
    {
        $name = mb_strtolower(trim((string) ($option['name'] ?? '')));

        return str_starts_with($name, 'venipak');
    }

    /**
     * Calculate the shipping cost using the selected option and cart metrics.
     */
    /**
     * @param EloquentCollection<int, CartItem> $cartItems
     */
    private function determineShippingCost(EloquentCollection $cartItems): float
    {
        if ($this->selectedShippingOption === null) {
            return 0.0;
        }

        if (($this->selectedShippingSnapshot['id'] ?? null) === $this->selectedShippingOption) {
            return (float) ($this->selectedShippingSnapshot['price'] ?? 0.0);
        }

        $resolver = app(ShippingOptionResolver::class);
        $shippingContext = $this->normalizedShippingAddress();
        $options = $resolver->resolve(
            collect($cartItems->all()),
            $shippingContext['country'] ?? null,
            $shippingContext
        );
        $match = $options->firstWhere('id', $this->selectedShippingOption);

        if ($match === null) {
            return $this->selectedShippingPrice;
        }

        $baseAmount = (float) ($match['price'] ?? 0.0);
        $discount = $this->calculateShippingDiscount($baseAmount);

        return max(0.0, round($baseAmount - $discount, 2));
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
            'user_id'       => auth()->id(),
            'now'           => now(),
            'cart'          => ['subtotal' => $subtotal, 'items' => []],
            'shipping'      => ['base_amount' => $baseAmount],
        ];

        $result = $engine->evaluate($context);
        $discountValue = data_get($result, 'shipping.discount_amount', 0.0);

        return is_numeric($discountValue) ? max(0.0, (float) $discountValue) : 0.0;
    }

    /**
     * Build badges describing how the shipping rate is constrained for display purposes.
     *
     * @return array<int, array{type:string,label:string}>
     */
    private function buildShippingBadges(float $baseAmount, float $finalAmount, float $discountAmount, string $currency): array
    {
        $badges = [];

        if ($finalAmount <= 0.0) {
            $badges[] = [
                'type'  => 'free',
                'label' => __('translations.free_shipping_from'),
            ];

            return $badges;
        }

        if ($discountAmount > 0.0 && $finalAmount < $baseAmount) {
            $badges[] = [
                'type'  => 'capped',
                'label' => __('messages.shipping_capped_at_amount', ['amount' => app_money_format($finalAmount, $currency)]),
            ];

            return $badges;
        }

        return $badges;
    }

    /**
     * Keep shipping address fields aligned with billing when the customer chooses the shortcut toggle.
     */
    private function synchroniseShippingFromBilling(): void
    {
        if (! $this->sameAsShipping) {
            return;
        }

        $this->shipping['first_name'] = $this->billing['first_name'] ?? '';
        $this->shipping['last_name'] = $this->billing['last_name'] ?? '';
        $this->shipping['address'] = $this->billing['address'] ?? '';
        $this->shipping['city'] = $this->billing['city'] ?? '';
        $this->shipping['postal_code'] = $this->billing['postal_code'] ?? '';
        $this->shipping['company'] = $this->billing['company'] ?? '';
        $this->shipping['region'] = $this->billing['region'] ?? '';
        $billingCountry = $this->billing['country'] ?? '';
        $this->shipping['country'] = is_string($billingCountry) ? strtoupper($billingCountry) : 'LT';
    }

    /**
     * Build shipping input with billing fallback so delivery lookup always receives a complete address.
     *
     * @return array{first_name:string,last_name:string,address:string,city:string,postal_code:string,company:?string,country:string,region:?string}
     */
    private function normalizedShippingAddress(): array
    {
        $normalized = $this->shipping;
        $fields = ['first_name', 'last_name', 'address', 'city', 'postal_code', 'company', 'country', 'region'];

        foreach ($fields as $field) {
            $shippingValue = $this->shipping[$field] ?? null;
            $shippingText = is_string($shippingValue) ? trim($shippingValue) : '';

            if ($shippingText !== '') {
                $normalized[$field] = $field === 'country' ? strtoupper($shippingText) : $shippingValue;

                continue;
            }

            $billingValue = $this->billing[$field] ?? null;
            $billingText = is_string($billingValue) ? trim($billingValue) : '';

            if ($billingText !== '') {
                $normalized[$field] = $field === 'country' ? strtoupper($billingText) : $billingValue;

                continue;
            }

            $normalized[$field] = $field === 'country' ? 'LT' : '';
        }

        return $normalized;
    }

    /**
     * Dispatch the order confirmation email asynchronously so guests receive their receipt immediately.
     */
    private function queueOrderConfirmation(Order $order): void
    {
        $recipient = (string) ($this->billing['email'] ?? '');

        Mail::to($recipient)->queue(new OrderConfirmationMail($order));
    }

    /**
     * Provide comprehensive validation rules when the full form is submitted.
     *
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return $this->addressStepRules() + [
            'selectedShippingOption' => ['required', 'integer', Rule::in($this->shippingOptionIds())],
            'selectedPaymentMethod'  => ['required', Rule::in(array_keys($this->paymentMethods))],
        ];
    }

    /**
     * Provide validation constraints for the combined billing and shipping address step.
     *
     * @return array<string, mixed>
     */
    private function addressStepRules(): array
    {
        $rules = [
            'billing.first_name'  => 'required|string|max:255',
            'billing.last_name'   => 'required|string|max:255',
            'billing.email'       => 'required|email|max:255',
            'billing.phone'       => 'required|string|max:255',
            'billing.address'     => 'required|string|max:255',
            'billing.city'        => 'required|string|max:255',
            'billing.postal_code' => 'required|string|max:10',
            'billing.country'     => 'required|string|size:2',
            'shipping.country'    => 'required|string|size:2',
        ];

        if (! $this->sameAsShipping) {
            // Require explicit recipient details whenever the parcel ships to a different address.
            $rules['shipping.first_name'] = 'required|string|max:255';
            $rules['shipping.last_name'] = 'required|string|max:255';
            $rules['shipping.address'] = 'required|string|max:255';
            $rules['shipping.city'] = 'required|string|max:255';
            $rules['shipping.postal_code'] = 'required|string|max:10';
        }

        return $rules;
    }

    /**
     * Return validation rules that are specific to the delivery selection step of the wizard.
     *
     * @return array<string, mixed>
     */
    private function deliveryStepRules(): array
    {
        return [
            'selectedShippingOption' => ['required', 'integer', Rule::in($this->shippingOptionIds())],
        ];
    }

    /**
     * Helper to expose the list of selectable shipping identifiers for validation constraints.
     *
     * @return list<int>
     */
    private function shippingOptionIds(): array
    {
        /** @var list<int> $ids */
        $ids = collect($this->availableShippingOptions)
            ->pluck('id')
            ->filter(static fn ($id): bool => is_numeric($id))
            ->map(static fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        return $ids;
    }

    /**
     * React to changes on the "same as shipping" toggle by synchronising address fields.
     */
    public function updatedSameAsShipping(bool $value): void
    {
        $this->sameAsShipping = $value;
        $this->synchroniseShippingFromBilling();
    }

    /**
     * Sync shipping address updates when billing fields change for customers using the shortcut toggle.
     */
    public function updatedBillingFirstName(): void
    {
        $this->synchroniseShippingFromBilling();
    }

    public function updatedBillingLastName(): void
    {
        $this->synchroniseShippingFromBilling();
    }

    public function updatedBillingAddress(): void
    {
        $this->synchroniseShippingFromBilling();
    }

    public function updatedBillingCity(): void
    {
        $this->synchroniseShippingFromBilling();
    }

    public function updatedBillingPostalCode(): void
    {
        $this->synchroniseShippingFromBilling();
        if ($this->sameAsShipping) {
            $this->resolveShippingOptions(resetSelection: true);
        }
    }

    public function updatedBillingRegion(): void
    {
        $this->synchroniseShippingFromBilling();
        if ($this->sameAsShipping) {
            $this->resolveShippingOptions(resetSelection: true);
        }
    }

    public function updatedBillingCompany(): void
    {
        $this->synchroniseShippingFromBilling();
    }

    public function updatedBillingCountry(): void
    {
        $country = $this->billing['country'] ?? '';
        $this->billing['country'] = is_string($country) ? strtoupper($country) : 'LT';
        $this->synchroniseShippingFromBilling();
        if ($this->sameAsShipping) {
            $this->resolveShippingOptions(resetSelection: true);
        }
    }

    public function updatedShippingCountry(): void
    {
        $country = $this->shipping['country'] ?? '';
        $this->shipping['country'] = is_string($country) ? strtoupper($country) : 'LT';
        $this->resolveShippingOptions(resetSelection: true);
    }

    public function updatedShippingRegion(): void
    {
        $this->resolveShippingOptions(resetSelection: true);
    }

    public function updatedShippingPostalCode(): void
    {
        $this->resolveShippingOptions(resetSelection: true);
    }

    public function updatedSelectedShippingOption(): void
    {
        $this->updateSelectedShippingPrice();
    }

    private function authenticatedUserId(): ?int
    {
        $user = auth()->user();

        if ($user instanceof User) {
            $identifier = $user->getAuthIdentifier();

            return is_numeric($identifier) ? (int) $identifier : null;
        }

        return null;
    }
}
