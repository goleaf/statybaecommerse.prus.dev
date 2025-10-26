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
 */
class Delivery extends StepComponent
{
    /**
     * Normalised shipping options resolved for the current checkout context.
     *
     * @var array<int, array{id:int,name:string,price:float,formatted_price:string,estimated_delivery:string}>
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
        $this->currentSelected = data_get(session()->get('checkout'), 'shipping_option')
            ? data_get(session()->get('checkout'), 'shipping_option')[0]['id']
            : null;

        // Resolve shipping options immediately so the delivery step starts hydrated.
        $this->recalculateOptions(data_get(session()->get('checkout'), 'shipping_address.id'));
    }

    /**
     * Allow the frontend to explicitly trigger a shipping refresh.
     *
     * This method is invoked both on initial render via `wire:init` and when the
     * shopper taps the "Recalculate" control to pull the latest resolver output.
     */
    public function recalculate(): void
    {
        // Normalise the identifier from the checkout payload to guard against nulls.
        $shippingAddressId = data_get(session()->get('checkout'), 'shipping_address.id');
        $normalizedId = is_numeric($shippingAddressId) ? (int) $shippingAddressId : null;

        $this->recalculateOptions($normalizedId);
    }

    /**
     * Refresh shipping options whenever the shipping address changes upstream.
     */
    #[On('shipping-address-updated')]
    public function handleShippingAddressUpdated(?int $shippingAddressId = null): void
    {
        $this->recalculateOptions($shippingAddressId);
    }

    /**
     * Handle save functionality with proper error handling.
     */
    public function save(): void
    {
        $this->validate();
        session()->forget('checkout.shipping_option');
        $option = ShippingOption::query()->find($this->currentSelected)->toArray();
        // Apply shipping discount context if any (free shipping or cap)
        $engine = app(\App\Services\Discounts\DiscountEngine::class);
        $context = ['currency_code' => current_currency(), 'channel_id' => optional(config('app.url')), 'user_id' => optional(auth()->user())->id, 'now' => now(), 'cart' => ['subtotal' => (float) (session('cart.subtotal') ?? 0), 'items' => []], 'shipping' => ['base_amount' => (float) ($option['price'] ?? 0)]];
        $result = $engine->evaluate($context);
        $shippingDiscount = (float) data_get($result, 'shipping.discount_amount', 0.0);
        if ($shippingDiscount > 0) {
            $option['price'] = max(0, (float) ($option['price'] ?? 0) - $shippingDiscount);
        }
        session()->push('checkout.shipping_option', $option);
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
    private function recalculateOptions(?int $shippingAddressId = null): void
    {
        // Forget previously stored shipping option whenever the address changes.
        session()->forget('checkout.shipping_option');

        $countryCode = $this->resolveCountryCode($shippingAddressId);

        $cartItems = CartItem::with('product')
            ->where('session_id', Session::getId())
            ->get();

        $resolver = app(ShippingOptionResolver::class);

        $resolved = $resolver->resolve($cartItems, $countryCode)->toArray();

        $this->options = $resolved;

        $availableIds = collect($resolved)->pluck('id')->all();

        if (! in_array($this->currentSelected, $availableIds, true)) {
            // Default to the first available option to keep the UI interactive.
            $this->currentSelected = $resolved[0]['id'] ?? null;
        }
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
                return $address->country_code;
            }
        }

        return data_get(session()->get('checkout'), 'shipping_address.country_code');
    }
}
