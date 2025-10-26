<?php

declare(strict_types=1);

namespace App\Livewire\Components\Checkout;

use App\Models\Address;
use App\Models\ShippingOption;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Spatie\LivewireWizard\Components\StepComponent;

/**
 * Delivery
 *
 * Livewire component for Delivery with reactive frontend functionality, real-time updates, and user interaction handling.
 *
 * @property array<int, array<string, mixed>> $options
 * @property int|null                         $currentSelected
 */
class Delivery extends StepComponent
{
    /**
     * @var array<int, array<string, mixed>>
     */
    public array $options = [];

    #[Validate('required', message: 'You must select a delivery method')]
    public ?int $currentSelected = null;

    public bool $isResolvingOptions = false;

    /**
     * Initialize the Livewire component with parameters.
     */
    public function mount(): void
    {
        $checkout = session()->get('checkout');
        $this->currentSelected = data_get($checkout, 'shipping_option.0.id');

        $this->options = $this->normaliseOptions(
            ShippingOption::query()
                ->where('is_enabled', true)
                ->orderBy('sort_order')
                ->get()
        );
    }

    /**
     * Handle save functionality with proper error handling.
     */
    public function save(): void
    {
        $this->validate();

        session()->forget('checkout.shipping_option');

        $selectedOption = collect($this->options)->firstWhere('id', $this->currentSelected);

        if (! is_array($selectedOption)) {
            // Fallback to a fresh lookup to avoid session holes when the view payload was stale.
            $model = ShippingOption::query()->find($this->currentSelected);
            $selectedOption = $model !== null ? $this->normaliseOptions([$model])[0] ?? null : null;
        }

        if (! is_array($selectedOption)) {
            $this->addError('currentSelected', __('Unable to locate the chosen delivery method.'));

            return;
        }

        // Apply shipping discount context if any (free shipping or cap)
        $engine = app(\App\Services\Discounts\DiscountEngine::class);
        $context = [
            'currency_code' => current_currency(),
            'channel_id'    => optional(config('app.url')),
            'user_id'       => optional(auth()->user())->id,
            'now'           => now(),
            'cart'          => ['subtotal' => (float) (session('cart.subtotal') ?? 0), 'items' => []],
            'shipping'      => ['base_amount' => (float) ($selectedOption['price'] ?? 0)],
        ];
        $result = $engine->evaluate($context);
        $shippingDiscount = (float) data_get($result, 'shipping.discount_amount', 0.0);
        if ($shippingDiscount > 0) {
            $selectedOption['price'] = max(0, (float) ($selectedOption['price'] ?? 0) - $shippingDiscount);
            $selectedOption['formatted_price'] = app_money_format((float) $selectedOption['price'], current_currency());
        }

        session()->push('checkout.shipping_option', $selectedOption);
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
     * Convert ShippingOption models (or array payloads) into the serialisable structure required by the view.
     *
     * @param  iterable<int, array<string, mixed>|ShippingOption> $options
     * @return array<int, array<string, mixed>>
     */
    private function normaliseOptions(iterable $options): array
    {
        $collection = $options instanceof Collection ? $options : collect($options);

        return $collection
            ->map(function ($option): array {
                if ($option instanceof ShippingOption) {
                    $id = (int) $option->getKey();
                    $description = (string) ($option->description ?? '');
                    $price = (float) $option->price;
                    $formattedPrice = $option->formatted_price ?? app_money_format($price, $option->currency_code ?? current_currency());
                    $estimated = $option->estimated_delivery_text ?? '';
                } else {
                    $id = (int) ($option['id'] ?? 0);
                    $description = (string) ($option['description'] ?? '');
                    $price = (float) ($option['price'] ?? 0);
                    $formattedPrice = (string) ($option['formatted_price'] ?? app_money_format($price, current_currency()));
                    $estimated = (string) ($option['estimated_delivery'] ?? '');
                }

                return [
                    'id'                => $id,
                    'name'              => (string) ($option['name'] ?? ($option instanceof ShippingOption ? $option->name : '')),
                    'description'       => $description,
                    'price'             => $price,
                    'formatted_price'   => $formattedPrice,
                    'estimated_delivery' => $estimated,
                ];
            })
            ->filter(fn (array $option): bool => $option['id'] > 0)
            ->values()
            ->all();
    }
}
