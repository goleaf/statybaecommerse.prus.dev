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
use App\Services\Pricing\PriceCalculator;
use App\Services\Shipping\ShippingOptionResolver;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Component;

/**
 * CheckoutProcess
 *
 * Livewire component for CheckoutProcess with reactive frontend functionality, real-time updates, and user interaction handling.
 *
 * @property array{first_name:string,last_name:string,email:string,phone:string,address:string,city:string,postal_code:string,company:?string,country:string,region:?string} $billing
 * @property bool   $sameAsShipping
 * @property array{first_name:string,last_name:string,address:string,city:string,postal_code:string,company:?string,country:string,region:?string} $shipping
 * @property string $notes
 * @property int    $currentStep
 */
final class CheckoutProcess extends Component
{
    #[Validate('required|array')]
    public array $billing = [
        'first_name'   => '',
        'last_name'    => '',
        'email'        => '',
        'phone'        => '',
        'address'      => '',
        'city'         => '',
        'postal_code'  => '',
        'company'      => '',
        'country'      => 'LT',
        'region'       => '',
    ];

    public bool $sameAsShipping = true;

    #[Validate('required|array')]
    public array $shipping = [
        'first_name'   => '',
        'last_name'    => '',
        'address'      => '',
        'city'         => '',
        'postal_code'  => '',
        'company'      => '',
        'country'      => 'LT',
        'region'       => '',
    ];

    #[Validate('nullable|string')]
    public string $notes = '';

    public int $currentStep = 1;

    /**
     * @var array<int, array{id:int,name:string,price:float,formatted_price:string,estimated_delivery:string}>
     */
    public array $availableShippingOptions = [];

    #[Validate('nullable|integer')]
    public ?int $selectedShippingOption = null;

    /**
     * @var array<string, string>
     */
    public array $paymentMethods = [];

    #[Validate('nullable|string|max:255')]
    public string $selectedPaymentMethod = '';

    public float $selectedShippingPrice = 0.0;

    /**
     * Initialize the Livewire component with parameters.
     */
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

        // Default the shipping contact details to mirror the billing information during the first render.
        $this->synchroniseShippingFromBilling();

        // Preload the payment method list so that validation rules know the allowed values immediately.
        $this->initialisePaymentMethods();
    }

    /**
     * Handle nextStep functionality with proper error handling.
     */
    public function nextStep(): void
    {
        $this->validateCurrentStep();

        if ($this->currentStep === 1) {
            // Persist address data for authenticated shoppers and refresh the available shipping matrix.
            $this->persistAuthenticatedAddresses();
            $this->refreshShippingOptions();
        }

        if ($this->currentStep === 2) {
            // Lock in the shipping selection before moving to the payment stage.
            $this->ensureShippingSelection();
        }

        if ($this->currentStep < 3) {
            $this->currentStep++;
        }
    }

    /**
     * Handle previousStep functionality with proper error handling.
     */
    public function previousStep(): void
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    /**
     * Handle validateCurrentStep functionality with proper error handling.
     */
    public function validateCurrentStep(): void
    {
        match ($this->currentStep) {
            1       => $this->validate([
                'billing.first_name'  => 'required|string|max:255',
                'billing.last_name'   => 'required|string|max:255',
                'billing.email'       => 'required|email|max:255',
                'billing.phone'       => 'required|string|max:255',
                'billing.address'     => 'required|string|max:255',
                'billing.city'        => 'required|string|max:255',
                'billing.postal_code' => 'required|string|max:10',
                'billing.country'     => 'required|string|size:2',
            ]),
            2       => $this->validate($this->shippingStepRules()),
            3       => $this->validate(['selectedPaymentMethod' => ['required', Rule::in(array_keys($this->paymentMethods))]]),
            default => null,
        };
    }

    /**
     * Handle placeOrder functionality with proper error handling.
     */
    public function placeOrder(): void
    {
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
    public function refreshShippingOptions(mixed $resetSelection = false): void
    {
        $shouldResetSelection = is_bool($resetSelection)
            ? $resetSelection
            : in_array($resetSelection, [1, '1', 'true', 'on'], true);
        $cartItems = $this->getCartItems();
        $resolver = app(ShippingOptionResolver::class);
        $options = $resolver->resolve(collect($cartItems->all()), $this->shipping['country'] ?? null);

        if ($shouldResetSelection) {
            // Clear the selected option so customers explicitly confirm their choice after a change.
            $this->selectedShippingOption = null;
            $this->selectedShippingPrice = 0.0;
        }

        $this->availableShippingOptions = $options
            // Preserve manual casting to stabilise Livewire hydration when shipping selections change.
            ->map(static fn ($option): array => $option instanceof ShippingOptionData ? $option->toArray() : (array) $option)
            ->map(static function (array $option): array {
                // Cast identifiers and monetary values to predictable scalar types for Livewire hydration.
                $option['id'] = (int) $option['id'];
                $option['price'] = (float) $option['price'];

                return $option;
            })
            ->values()
            ->all();

        if ($this->availableShippingOptions === []) {
            $this->selectedShippingOption = null;
            $this->selectedShippingPrice = 0.0;
            $this->addError('selectedShippingOption', __('No shipping methods are available for the provided address.'));

            return;
        }

        /** @var list<array{id:int,name:string,price:float,formatted_price:string,estimated_delivery:string}> $options */
        $options = $this->availableShippingOptions;

        $selectedExists = collect($options)
            ->contains(fn (array $option): bool => $option['id'] === (int) $this->selectedShippingOption);

        if (! $selectedExists && ! $shouldResetSelection) {
            $this->selectedShippingOption = $this->availableShippingOptions[0]['id'];
        }

        // Remove stale validation errors whenever a valid set of shipping choices is present again.
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

        $this->selectedShippingPrice = $selected['price'] ?? 0.0;
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

        $resolver = app(ShippingOptionResolver::class);
        $options = $resolver->resolve(collect($cartItems->all()), $this->shipping['country'] ?? null);
        $match = $options->firstWhere('id', $this->selectedShippingOption);

        return (float) ($match['price'] ?? $this->selectedShippingPrice);
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
        return [
            'billing.first_name'       => 'required|string|max:255',
            'billing.last_name'        => 'required|string|max:255',
            'billing.email'            => 'required|email|max:255',
            'billing.phone'            => 'required|string|max:255',
            'billing.address'          => 'required|string|max:255',
            'billing.city'             => 'required|string|max:255',
            'billing.postal_code'      => 'required|string|max:10',
            'billing.country'          => 'required|string|size:2',
            'shipping.first_name'      => $this->sameAsShipping ? 'nullable|string|max:255' : 'required|string|max:255',
            'shipping.last_name'       => $this->sameAsShipping ? 'nullable|string|max:255' : 'required|string|max:255',
            'shipping.address'         => $this->sameAsShipping ? 'nullable|string|max:255' : 'required|string|max:255',
            'shipping.city'            => $this->sameAsShipping ? 'nullable|string|max:255' : 'required|string|max:255',
            'shipping.postal_code'     => $this->sameAsShipping ? 'nullable|string|max:10' : 'required|string|max:10',
            'shipping.country'         => 'required|string|size:2',
            'selectedShippingOption'   => ['required', 'integer', Rule::in($this->shippingOptionIds())],
            'selectedPaymentMethod'    => ['required', Rule::in(array_keys($this->paymentMethods))],
        ];
    }

    /**
     * Return validation rules that are specific to the shipping step of the wizard.
     *
     * @return array<string, mixed>
     */
    private function shippingStepRules(): array
    {
        if ($this->sameAsShipping) {
            return [
                'selectedShippingOption' => ['required', 'integer', Rule::in($this->shippingOptionIds())],
            ];
        }

        return [
            'shipping.first_name'      => 'required|string|max:255',
            'shipping.last_name'       => 'required|string|max:255',
            'shipping.address'         => 'required|string|max:255',
            'shipping.city'            => 'required|string|max:255',
            'shipping.postal_code'     => 'required|string|max:10',
            'shipping.country'         => 'required|string|size:2',
            'selectedShippingOption'   => ['required', 'integer', Rule::in($this->shippingOptionIds())],
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
            $this->refreshShippingOptions(resetSelection: true);
        }
    }

    public function updatedBillingRegion(): void
    {
        $this->synchroniseShippingFromBilling();
        if ($this->sameAsShipping) {
            $this->refreshShippingOptions(resetSelection: true);
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
            $this->refreshShippingOptions(resetSelection: true);
        }
    }

    public function updatedShippingCountry(): void
    {
        $country = $this->shipping['country'] ?? '';
        $this->shipping['country'] = is_string($country) ? strtoupper($country) : 'LT';
        $this->refreshShippingOptions(resetSelection: true);
    }

    public function updatedShippingRegion(): void
    {
        $this->refreshShippingOptions(resetSelection: true);
    }

    public function updatedShippingPostalCode(): void
    {
        $this->refreshShippingOptions(resetSelection: true);
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
