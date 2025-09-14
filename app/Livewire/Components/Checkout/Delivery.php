<?php

declare(strict_types=1);

namespace App\Livewire\Components\Checkout;

use App\Models\Country;
use App\Models\Zone;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Validate;
use Spatie\LivewireWizard\Components\StepComponent;

/**
 * Delivery
 * 
 * Livewire component for reactive frontend functionality.
 */
class Delivery extends StepComponent
{
    /**
     * @var array|Collection
     */
    public $options = [];

    #[Validate('required', message: 'You must select a delivery method')]
    public ?int $currentSelected = null;

    public function mount(): void
    {
        $countryId = data_get(session()->get('checkout'), 'shipping_address.country_id');
        $this->currentSelected = data_get(session()->get('checkout'), 'shipping_option')
            ? data_get(session()->get('checkout'), 'shipping_option')[0]['id']
            : null;

        $country = Country::query()->with('zones')->find($countryId);
        /** @var ?Zone $zone */
        // @phpstan-ignore-next-line
        $zone = $country->zones()
            ->with('shippingOptions')
            ->where('is_enabled', true)
            ->first();

        $this->options = $zone
            ? $zone->shippingOptions()->where('is_enabled', true)->get()
            : [];
    }

    public function save(): void
    {
        $this->validate();

        session()->forget('checkout.shipping_option');

        $option = CarrierOption::query()->find($this->currentSelected)->toArray();
        // Apply shipping discount context if any (free shipping or cap)
        $engine = app(\App\Services\Discounts\DiscountEngine::class);
        $context = [
            'zone_id' => session('zone.id'),
            'currency_code' => current_currency(),
            'channel_id' => optional(config('app.url')),
            'user_id' => optional(auth()->user())->id,
            'now' => now(),
            'cart' => [
                'subtotal' => (float) (session('cart.subtotal') ?? 0),
                'items' => [],
            ],
            'shipping' => [
                'base_amount' => (float) ($option['price'] ?? 0),
            ],
        ];
        $result = $engine->evaluate($context);
        $shippingDiscount = (float) data_get($result, 'shipping.discount_amount', 0.0);
        if ($shippingDiscount > 0) {
            $option['price'] = max(0, ((float) ($option['price'] ?? 0)) - $shippingDiscount);
        }

        session()->push('checkout.shipping_option', $option);

        $this->dispatch('cart-price-update');

        $this->nextStep();
    }

    public function stepInfo(): array
    {
        return [
            'label' => __('Delivery method'),
            'complete' => session()->exists('checkout')
                && data_get(session()->get('checkout'), 'shipping_option') !== null,
        ];
    }

    public function render(): View
    {
        return view('livewire.components.checkout.delivery');
    }
}
