<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Data\Pricing\PriceBreakdown;
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
 * @property string $billingFirstName
 * @property string $billingLastName
 * @property string $billingEmail
 * @property string $billingPhone
 * @property string $billingAddress
 * @property string $billingCity
 * @property string $billingPostalCode
 * @property string $billingCompany
 * @property bool   $sameAsShipping
 * @property string $shippingFirstName
 * @property string $shippingLastName
 * @property string $shippingAddress
 * @property string $shippingCity
 * @property string $shippingPostalCode
 * @property string $shippingCompany
 * @property string $notes
 * @property int    $currentStep
 */
final class CheckoutProcess extends Component
{
    #[Validate('required|string|max:255')]
    public string $billingFirstName = '';

    #[Validate('required|string|max:255')]
    public string $billingLastName = '';

    #[Validate('required|email|max:255')]
    public string $billingEmail = '';

    #[Validate('required|string|max:255')]
    public string $billingPhone = '';

    #[Validate('required|string|max:255')]
    public string $billingAddress = '';

    #[Validate('required|string|max:255')]
    public string $billingCity = '';

    #[Validate('required|string|max:10')]
    public string $billingPostalCode = '';

    #[Validate('nullable|string|max:255')]
    public string $billingCompany = '';

    public bool $sameAsShipping = true;

    #[Validate('required|string|size:2')]
    public string $billingCountryCode = 'LT';

    #[Validate('required|string|size:2')]
    public string $shippingCountryCode = 'LT';

    #[Validate('nullable|string|max:255')]
    public string $shippingFirstName = '';

    #[Validate('nullable|string|max:255')]
    public string $shippingLastName = '';

    #[Validate('nullable|string|max:255')]
    public string $shippingAddress = '';

    #[Validate('nullable|string|max:255')]
    public string $shippingCity = '';

    #[Validate('nullable|string|max:10')]
    public string $shippingPostalCode = '';

    #[Validate('nullable|string|max:255')]
    public string $shippingCompany = '';

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
                $this->billingFirstName = (string) ($user->first_name ?? '');
                $this->billingLastName = (string) ($user->last_name ?? '');
                $this->billingEmail = (string) $user->email;
                $this->billingPhone = (string) ($user->phone_number ?? '');
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
            1       => $this->validate(['billingFirstName' => 'required|string|max:255', 'billingLastName' => 'required|string|max:255', 'billingEmail' => 'required|email|max:255', 'billingPhone' => 'required|string|max:255', 'billingAddress' => 'required|string|max:255', 'billingCity' => 'required|string|max:255', 'billingPostalCode' => 'required|string|max:10']),
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
        $country = $this->resolveCountryDetails($this->billingCountryCode);

        return ['first_name' => $this->billingFirstName, 'last_name' => $this->billingLastName, 'company' => $this->billingCompany, 'email' => $this->billingEmail, 'phone' => $this->billingPhone, 'address' => $this->billingAddress, 'city' => $this->billingCity, 'postal_code' => $this->billingPostalCode, 'country' => $country['name'], 'country_code' => $country['code']];
    }

    /**
     * @return array<string, string|null>
     */
    private function getShippingAddress(): array
    {
        if ($this->sameAsShipping) {
            return $this->getBillingAddress();
        }

        $country = $this->resolveCountryDetails($this->shippingCountryCode);

        return ['first_name' => $this->shippingFirstName, 'last_name' => $this->shippingLastName, 'company' => $this->shippingCompany, 'address' => $this->shippingAddress, 'city' => $this->shippingCity, 'postal_code' => $this->shippingPostalCode, 'country' => $country['name'], 'country_code' => $country['code']];
    }

    /**
     * Render the Livewire component view with current state.
     */
    public function render(): View
    {
        $items = $this->getCartItems();
        $breakdown = $this->calculateBreakdown($items);

        /** @var view-string $view */
        $view = 'livewire.pages.checkout-process';

        return view($view, [
            'cartItems' => $items,
            'summary'   => $breakdown->toSummary(),
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
            'first_name'     => $this->billingFirstName,
            'last_name'      => $this->billingLastName,
            'address_line_1' => $this->billingAddress,
            'city'           => $this->billingCity,
            'postal_code'    => $this->billingPostalCode,
            'email'          => $this->billingEmail,
            'phone'          => $this->billingPhone,
            'country_code'   => strtoupper($this->billingCountryCode),
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
                'first_name'     => $this->shippingFirstName,
                'last_name'      => $this->shippingLastName,
                'address_line_1' => $this->shippingAddress,
                'city'           => $this->shippingCity,
                'postal_code'    => $this->shippingPostalCode,
                'email'          => $this->billingEmail,
                'phone'          => $this->billingPhone,
                'country_code'   => strtoupper($this->shippingCountryCode),
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
            $billingFirstName = $billing->getAttribute('first_name');
            if (is_string($billingFirstName)) {
                $this->billingFirstName = $billingFirstName;
            }
            $billingLastName = $billing->getAttribute('last_name');
            if (is_string($billingLastName)) {
                $this->billingLastName = $billingLastName;
            }
            $billingAddressLine = $billing->getAttribute('address_line_1');
            if (is_string($billingAddressLine)) {
                $this->billingAddress = $billingAddressLine;
            }
            $billingCity = $billing->getAttribute('city');
            if (is_string($billingCity)) {
                $this->billingCity = $billingCity;
            }
            $billingPostalCode = $billing->getAttribute('postal_code');
            if (is_string($billingPostalCode)) {
                $this->billingPostalCode = $billingPostalCode;
            }
            $company = $billing->getAttribute('company_name') ?? $billing->getAttribute('company');
            if (is_string($company)) {
                $this->billingCompany = $company;
            }
            $billingCountry = $billing->getAttribute('country_code');
            if (is_string($billingCountry)) {
                $this->billingCountryCode = strtoupper($billingCountry);
            }
            $billingPhone = $billing->getAttribute('phone');
            if (is_string($billingPhone)) {
                $this->billingPhone = $billingPhone;
            }
        }

        $shipping = $addresses['shipping'] ?? null;
        if ($shipping instanceof Address) {
            // Mirror stored shipping data and disable the "same as billing" toggle when records differ.
            $this->sameAsShipping = false;
            $shippingFirstName = $shipping->getAttribute('first_name');
            if (is_string($shippingFirstName)) {
                $this->shippingFirstName = $shippingFirstName;
            }
            $shippingLastName = $shipping->getAttribute('last_name');
            if (is_string($shippingLastName)) {
                $this->shippingLastName = $shippingLastName;
            }
            $shippingAddressLine = $shipping->getAttribute('address_line_1');
            if (is_string($shippingAddressLine)) {
                $this->shippingAddress = $shippingAddressLine;
            }
            $shippingCity = $shipping->getAttribute('city');
            if (is_string($shippingCity)) {
                $this->shippingCity = $shippingCity;
            }
            $shippingPostalCode = $shipping->getAttribute('postal_code');
            if (is_string($shippingPostalCode)) {
                $this->shippingPostalCode = $shippingPostalCode;
            }
            $shippingCompany = $shipping->getAttribute('company_name') ?? $shipping->getAttribute('company');
            if (is_string($shippingCompany)) {
                $this->shippingCompany = $shippingCompany;
            }
            $shippingCountry = $shipping->getAttribute('country_code');
            if (is_string($shippingCountry)) {
                $this->shippingCountryCode = strtoupper($shippingCountry);
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
    private function refreshShippingOptions(): void
    {
        $cartItems = $this->getCartItems();
        $resolver = app(ShippingOptionResolver::class);
        $options = $resolver->resolve(collect($cartItems->all()), $this->shippingCountryCode);

        $this->availableShippingOptions = $options
            // Preserve manual casting to stabilise Livewire hydration when shipping selections change.
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

        if (! $selectedExists) {
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
        $options = $resolver->resolve(collect($cartItems->all()), $this->shippingCountryCode);
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

        $this->shippingFirstName = $this->billingFirstName;
        $this->shippingLastName = $this->billingLastName;
        $this->shippingAddress = $this->billingAddress;
        $this->shippingCity = $this->billingCity;
        $this->shippingPostalCode = $this->billingPostalCode;
        $this->shippingCompany = $this->billingCompany;
        $this->shippingCountryCode = strtoupper($this->billingCountryCode);
    }

    /**
     * Dispatch the order confirmation email asynchronously so guests receive their receipt immediately.
     */
    private function queueOrderConfirmation(Order $order): void
    {
        Mail::to($this->billingEmail)->queue(new OrderConfirmationMail($order));
    }

    /**
     * Provide comprehensive validation rules when the full form is submitted.
     *
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'billingFirstName'       => 'required|string|max:255',
            'billingLastName'        => 'required|string|max:255',
            'billingEmail'           => 'required|email|max:255',
            'billingPhone'           => 'required|string|max:255',
            'billingAddress'         => 'required|string|max:255',
            'billingCity'            => 'required|string|max:255',
            'billingPostalCode'      => 'required|string|max:10',
            'billingCountryCode'     => 'required|string|size:2',
            'shippingFirstName'      => $this->sameAsShipping ? 'nullable|string|max:255' : 'required|string|max:255',
            'shippingLastName'       => $this->sameAsShipping ? 'nullable|string|max:255' : 'required|string|max:255',
            'shippingAddress'        => $this->sameAsShipping ? 'nullable|string|max:255' : 'required|string|max:255',
            'shippingCity'           => $this->sameAsShipping ? 'nullable|string|max:255' : 'required|string|max:255',
            'shippingPostalCode'     => $this->sameAsShipping ? 'nullable|string|max:10' : 'required|string|max:10',
            'shippingCountryCode'    => 'required|string|size:2',
            'selectedShippingOption' => ['required', 'integer', Rule::in($this->shippingOptionIds())],
            'selectedPaymentMethod'  => ['required', Rule::in(array_keys($this->paymentMethods))],
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
            'shippingFirstName'      => 'required|string|max:255',
            'shippingLastName'       => 'required|string|max:255',
            'shippingAddress'        => 'required|string|max:255',
            'shippingCity'           => 'required|string|max:255',
            'shippingPostalCode'     => 'required|string|max:10',
            'shippingCountryCode'    => 'required|string|size:2',
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
    }

    public function updatedBillingCountryCode(): void
    {
        $this->billingCountryCode = strtoupper($this->billingCountryCode);
        $this->synchroniseShippingFromBilling();
    }

    public function updatedShippingCountryCode(): void
    {
        $this->shippingCountryCode = strtoupper($this->shippingCountryCode);
        $this->refreshShippingOptions();
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
