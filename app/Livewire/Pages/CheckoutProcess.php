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
use App\Models\ShippingOption;
use App\Models\User;
use App\Services\Cart\CartLifecycleService;
use App\Services\Discounts\DiscountEngine;
use App\Services\Pricing\PriceCalculator;
use App\Services\Shipping\ShippingOptionResolver;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
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

    /** @var list<array{id:int,name:string,price:float,formatted_price:string,estimated_delivery:string,currency?:string|null,resolved_price?:float,badges?:array<int, array{type:string,label:string}>}> */
    public array $availableShippingOptions = [];

    /**
     * Flag indicating whether the resolver is currently recalculating options.
     */
    public bool $isResolvingShippingOptions = false;

    /** @var array{id:int,name:string,price:float,formatted_price?:string|null,estimated_delivery?:string|null,currency_code?:string|null,resolved_price?:float,badges?:array<int, array{type:string,label:string}>}|array{} */
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
            1       => $this->validate($this->addressStepRules()),
            2       => $this->validate($this->deliveryStepRules()),
            3       => $this->validate([
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
            $this->addError('cart', 'Jūsų krepšelis tuščias');

            return;
        }
        DB::transaction(function () use ($cartItems): void {
            // Resolve the final order payload, persist line items, and dispatch the email confirmation.
            $order = $this->createOrder($cartItems);
            $this->createOrderItems($order, $cartItems);
            $this->queueOrderConfirmation($order);
            app(CartLifecycleService::class)->clearAfterCheckout($this->authenticatedUserId(), Session::getId());
            session()->flash('order_number', $order->number);
            $this->redirect(route('order.confirmation', $order->number));
        });
    }

    /**
     * @param EloquentCollection<int, CartItem> $cartItems
     */
    private function createOrder(EloquentCollection $cartItems): Order
    {
        $breakdown = $this->calculateBreakdown($cartItems);
        $selectedOption = $this->resolveSelectedShippingOptionModel();

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
            'shipping_address'   => $this->getShippingAddress(),
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
     * @return EloquentCollection<int, CartItem>
     */
    private function getCartItems(): EloquentCollection
    {
        // Retrieve cart items with their related products so shipping logic can inspect weights and totals.
        return CartItem::with('product')->where('session_id', Session::getId())->get();
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

        $country = $this->resolveCountryDetails($this->shipping['country'] ?? null);

        return [
            'first_name'   => (string) ($this->shipping['first_name'] ?? ''),
            'last_name'    => (string) ($this->shipping['last_name'] ?? ''),
            'company'      => $this->shipping['company'] ?? null,
            'address'      => (string) ($this->shipping['address'] ?? ''),
            'city'         => (string) ($this->shipping['city'] ?? ''),
            'region'       => $this->shipping['region'] ?? null,
            'postal_code'  => (string) ($this->shipping['postal_code'] ?? ''),
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
            // Mirror stored shipping data and disable the "same as billing" toggle when records differ.
            $this->sameAsShipping = false;
            $this->shipping['first_name'] = (string) ($shipping->getAttribute('first_name') ?? $this->shipping['first_name']);
            $this->shipping['last_name'] = (string) ($shipping->getAttribute('last_name') ?? $this->shipping['last_name']);
            $this->shipping['address'] = (string) ($shipping->getAttribute('address_line_1') ?? $this->shipping['address']);
            $this->shipping['city'] = (string) ($shipping->getAttribute('city') ?? $this->shipping['city']);
            $this->shipping['postal_code'] = (string) ($shipping->getAttribute('postal_code') ?? $this->shipping['postal_code']);
            $shippingCompany = $shipping->getAttribute('company_name') ?? $shipping->getAttribute('company');
            if (is_string($shippingCompany)) {
                $this->shipping['company'] = $shippingCompany;
            }
            $shippingState = $shipping->getAttribute('state');
            if (is_string($shippingState)) {
                $this->shipping['region'] = $shippingState;
            }
            $shippingCountry = $shipping->getAttribute('country_code');
            if (is_string($shippingCountry)) {
                $this->shipping['country'] = strtoupper($shippingCountry);
            }
        }
    }

    /**
     * Prepare the list of supported payment methods for the checkout experience.
     */
    private function initialisePaymentMethods(): void
    {
        // Offer cash on delivery, bank transfer, and a default online option to match business requirements.
        $this->paymentMethods = [
            PaymentMethod::CASH_ON_DELIVERY->value => trans('orders.payment_methods.cash_on_delivery'),
            PaymentMethod::BANK_TRANSFER->value    => trans('orders.payment_methods.bank_transfer'),
            PaymentMethod::STRIPE->value           => trans('orders.payment_methods.stripe'),
        ];

        $this->selectedPaymentMethod = $this->selectedPaymentMethod !== ''
            ? $this->selectedPaymentMethod
            : PaymentMethod::CASH_ON_DELIVERY->value;
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
            $options = $resolver->resolve(collect($cartItems->all()), $this->shipping['country'] ?? null);

            $this->availableShippingOptions = $options
                ->map(function ($option): array {
                    $payload = $option instanceof ShippingOptionData ? $option->toArray() : (array) $option;
                    $payload['id'] = (int) ($payload['id'] ?? 0);
                    $baseAmount = (float) ($payload['price'] ?? 0.0);
                    $currency = (string) ($payload['currency'] ?? $payload['currency_code'] ?? current_currency());
                    $discount = $this->calculateShippingDiscount($baseAmount);
                    $finalAmount = max(0.0, round($baseAmount - $discount, 2));

                    $payload['resolved_price'] = $baseAmount;
                    $payload['price'] = $finalAmount;
                    $payload['formatted_price'] = (string) ($payload['formatted_price'] ?? app_money_format($finalAmount, $currency));
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
            $this->addError('selectedShippingOption', __('No shipping methods are available for the provided address.'));

            return;
        }

        $selectedExists = collect($this->availableShippingOptions)
            ->contains(fn (array $option): bool => $option['id'] === (int) $this->selectedShippingOption);

        if (! $selectedExists && ! $shouldResetSelection) {
            $this->selectedShippingOption = $this->availableShippingOptions[0]['id'];
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
            $this->addError('selectedShippingOption', __('Please choose a shipping method to continue.'));
        }

        $this->updateSelectedShippingPrice();
    }

    /**
     * Resolve the shipping option model for persistence and analytics tracking.
     */
    private function resolveSelectedShippingOptionModel(): ?ShippingOption
    {
        if ($this->selectedShippingOption === null) {
            return null;
        }

        return ShippingOption::query()->find($this->selectedShippingOption);
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
        $options = $resolver->resolve(collect($cartItems->all()), $this->shipping['country'] ?? null);
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
