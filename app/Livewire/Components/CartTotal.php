<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * CartTotal
 * 
 * Livewire component for reactive frontend functionality.
 */
class CartTotal extends Component
{
    public float $subtotal = 0.0;

    public float $discount = 0.0;

    public float $shippingDiscount = 0.0;

    public float $total = 0.0;

    #[On('cartUpdated')]
    #[On('coupon-updated')]
    public function updateTotals(): void
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

        $this->subtotal = $subtotal;

        $result = $this->calculateDiscountsAndShipping($this->subtotal);
        $this->discount = (float) ($result['discount_total_amount'] ?? 0.0);
        $this->shippingDiscount = (float) data_get($result, 'shipping.discount_amount', 0.0);
        $shipping = (float) data_get(session()->get('checkout'), 'shipping_option.0.price', 0.0);
        $this->total = max(0, ($this->subtotal - $this->discount) + max(0, $shipping - $this->shippingDiscount));
    }

    protected function calculateDiscountsAndShipping(float $amount): array
    {
        $coupon = session('checkout.coupon.code');
        $code = $coupon ? strtoupper(trim((string) $coupon)) : '';
        $engine = app(\App\Services\Discounts\DiscountEngine::class);

        $items = [];
        if (class_exists(\Darryldecode\Cart\Facades\CartFacade::class)) {
            try {
                // @phpstan-ignore-next-line
                foreach (\Darryldecode\Cart\Facades\CartFacade::session(session()->getId())->getContent() as $item) {
                    $items[] = [
                        'product_id' => optional($item->associatedModel)->id,
                        'variant_id' => method_exists($item->associatedModel, 'getKey') ? $item->associatedModel->getKey() : null,
                        'quantity' => (int) $item->quantity,
                        'unit_price' => (float) $item->price,
                    ];
                }
            } catch (\Throwable $e) {
                // fail-open: no items when cart driver fails
                $items = [];
            }
        }

        $userId = optional(auth()->user())->id;
        $groupIds = [];
        $partnerTier = null;
        if ($userId) {
            try {
                $groupIds = (array) \DB::table('sh_customer_group_user')->where('user_id', $userId)->pluck('group_id')->all();
                $partnerTier = \DB::table('sh_partner_users as pu')
                    ->join('sh_partners as p', 'p.id', '=', 'pu.partner_id')
                    ->where('pu.user_id', $userId)
                    ->value('p.tier');
            } catch (\Throwable $e) {
                // ignore if tables not present
            }
        }

        $context = [
            'zone_id' => session('zone.id'),
            'currency_code' => current_currency(),
            'channel_id' => optional(config('app.url')),
            'user_id' => $userId,
            'group_ids' => $groupIds,
            'partner_tier' => $partnerTier,
            'now' => now(),
            'code' => $code,
            'cart' => [
                'subtotal' => $amount,
                'items' => $items,
            ],
        ];

        try {
            return (array) $engine->evaluate($context);
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function render(): View
    {
        return view('livewire.components.cart-total', [
            'subtotal' => $this->subtotal,
            'discount' => $this->discount,
            'total' => $this->total,
        ]);
    }
}
