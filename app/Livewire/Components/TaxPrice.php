<?php declare(strict_types=1);

namespace App\Livewire\Components;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class TaxPrice extends Component
{
    public float $amount = 0.0;

    #[On('cartUpdated')]
    #[On('coupon-updated')]
    public function updateAmounts(): void
    {
        $this->compute();
    }

    public function mount(): void
    {
        $this->compute();
    }

    protected function compute(): void
    {
        $sessionKey = session()->getId();

        $subtotal = 0.0;
        if (class_exists(\Darryldecode\Cart\Facades\CartFacade::class)) {
            try {
                // @phpstan-ignore-next-line
                $subtotal = (float) \Darryldecode\Cart\Facades\CartFacade::session($sessionKey)->getSubTotal();
            } catch (\Throwable $e) {
                $subtotal = 0.0;
            }
        }

        // Get discount via CartTotal calculation
        $discount = 0.0;
        try {
            $cartTotal = app(\App\Livewire\Components\CartTotal::class);
            // Access protected compute via instance render cycle is not trivial; re-run its logic partially
            $coupon = session('checkout.coupon.code');
            $code = $coupon ? strtoupper(trim((string) $coupon)) : '';
            $items = [];
            if (class_exists(\Darryldecode\Cart\Facades\CartFacade::class)) {
                try {
                    foreach (\Darryldecode\Cart\Facades\CartFacade::session(session()->getId())->getContent() as $item) {
                        $items[] = [
                            'product_id' => optional($item->associatedModel)->id,
                            'variant_id' => method_exists($item->associatedModel, 'getKey') ? $item->associatedModel->getKey() : null,
                            'quantity' => (int) $item->quantity,
                            'unit_price' => (float) $item->price,
                        ];
                    }
                } catch (\Throwable $e) {
                    $items = [];
                }
            }
            $context = [
                'zone_id' => session('zone.id'),
                'currency_code' => current_currency(),
                'channel_id' => optional(config('app.url')),
                'user_id' => optional(auth()->user())->id,
                'group_ids' => [],
                'partner_tier' => null,
                'now' => now(),
                'code' => $code,
                'cart' => [
                    'subtotal' => $subtotal,
                    'items' => $items,
                ],
            ];
            $result = (array) app(\App\Services\Discounts\DiscountEngine::class)->evaluate($context);
            $discount = (float) ($result['discount_total_amount'] ?? 0.0);
        } catch (\Throwable $e) {
            $discount = 0.0;
        }

        $zoneCode = (string) (session('zone.code') ?? session('zoneCode') ?? '');
        $this->amount = app(\App\Services\Taxes\TaxCalculator::class)->compute(max(0.0, $subtotal - $discount), $zoneCode ?: null);
    }

    public function render(): View
    {
        return view('livewire.components.tax-price', [
            'amount' => $this->amount,
        ]);
    }
}
