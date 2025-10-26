<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use DB;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Throwable;

/**
 * CartTotal
 *
 * Livewire component for CartTotal with reactive frontend functionality, real-time updates, and user interaction handling.
 *
 * @property float $subtotal
 * @property float $discount
 * @property float $shippingDiscount
 * @property float $total
 */
class CartTotal extends Component
{
    public float $subtotal = 0.0;

    public float $discount = 0.0;

    public float $shippingDiscount = 0.0;

    public float $total = 0.0;

    /**
     * @var array<string, mixed>
     */
    public array $summary = [];

    /**
     * Handle cartSubtotal functionality with proper error handling.
     */
    #[Computed]
    public function cartSubtotal(): float
    {
        $sessionKey = session()->getId();
        if (class_exists(\Darryldecode\Cart\Facades\CartFacade::class)) {
            try {
                return (float) \Darryldecode\Cart\Facades\CartFacade::session($sessionKey)->getSubTotal();
            } catch (Throwable $e) {
                return 0.0;
            }
        }

        return 0.0;
    }

    /**
     * Handle discountCalculation functionality with proper error handling.
     */
    #[Computed]
    public function discountCalculation(): array
    {
        return $this->calculateDiscountsAndShipping($this->cartSubtotal);
    }

    /**
     * Handle finalTotal functionality with proper error handling.
     */
    #[Computed]
    public function finalTotal(): float
    {
        return (float) ($this->summary['total'] ?? 0.0);
    }

    /**
     * Handle updateTotals functionality with proper error handling.
     */
    #[On('cart-updated')] // Keep totals aligned with the canonical cart update event.
    #[On('coupon-updated')]
    public function updateTotals(): void
    {
        $this->compute();
    }

    /**
     * Initialize the Livewire component with parameters.
     */
    public function mount(): void
    {
        $this->compute();
    }

    /**
     * Handle compute functionality with proper error handling.
     */
    protected function compute(): void
    {
        $sessionKey = session()->getId();
        $subtotal = 0.0;
        if (class_exists(\Darryldecode\Cart\Facades\CartFacade::class)) {
            try {
                $subtotal = (float) \Darryldecode\Cart\Facades\CartFacade::session($sessionKey)->getSubTotal();
            } catch (Throwable $e) {
                $subtotal = 0.0;
            }
        }
        $this->subtotal = $subtotal;
        $result = $this->calculateDiscountsAndShipping($this->subtotal);
        $this->discount = (float) ($result['discount_total_amount'] ?? 0.0);
        $this->shippingDiscount = (float) data_get($result, 'shipping.discount_amount', 0.0);
        $shippingOption = data_get(session()->get('checkout'), 'shipping_option.0.price');
        $shippingAmount = $shippingOption === null ? null : max(0.0, (float) $shippingOption - $this->shippingDiscount);
        $breakdown = app(PriceCalculator::class)->breakdown($this->subtotal, $this->discount, $shippingAmount);
        $this->total = $breakdown->total;
        $this->summary = $breakdown->toSummary();
    }

    /**
     * Handle calculateDiscountsAndShipping functionality with proper error handling.
     */
    protected function calculateDiscountsAndShipping(float $amount): array
    {
        $coupon = session('checkout.coupon.code');
        $code = $coupon ? strtoupper(trim((string) $coupon)) : '';
        $engine = app(\App\Services\Discounts\DiscountEngine::class);
        $items = [];
        if (class_exists(\Darryldecode\Cart\Facades\CartFacade::class)) {
            try {
                foreach (\Darryldecode\Cart\Facades\CartFacade::session(session()->getId())->getContent() as $item) {
                    $items[] = ['product_id' => optional($item->associatedModel)->id, 'variant_id' => method_exists($item->associatedModel, 'getKey') ? $item->associatedModel->getKey() : null, 'quantity' => (int) $item->quantity, 'unit_price' => (float) $item->price];
                }
            } catch (Throwable $e) {
                // fail-open: no items when cart driver fails
                $items = [];
            }
        }
        $userId = optional(auth()->user())->id;
        $groupIds = [];
        $partnerTier = null;
        if ($userId) {
            try {
                $groupIds = (array) DB::table('sh_customer_group_user')->where('user_id', $userId)->pluck('group_id')->all();
                $partnerTier = DB::table('sh_partner_users as pu')->join('sh_partners as p', 'p.id', '=', 'pu.partner_id')->where('pu.user_id', $userId)->value('p.tier');
            } catch (Throwable $e) {
                // ignore if tables not present
            }
        }
        $context = ['currency_code' => current_currency(), 'channel_id' => optional(config('app.url')), 'user_id' => $userId, 'group_ids' => $groupIds, 'partner_tier' => $partnerTier, 'now' => now(), 'code' => $code, 'cart' => ['subtotal' => $amount, 'items' => $items]];
        try {
            return (array) $engine->evaluate($context);
        } catch (Throwable $e) {
            return [];
        }
    }

    /**
     * Render the Livewire component view with current state.
     */
    public function render(): View
    {
        return view('livewire.components.cart-total', ['subtotal' => $this->cartSubtotal, 'discount' => $this->discount, 'total' => $this->finalTotal]);
    }
}
