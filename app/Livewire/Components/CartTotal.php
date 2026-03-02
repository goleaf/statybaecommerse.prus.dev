<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use App\Services\Cart\CartService;
use App\Services\Pricing\PriceCalculator;
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
        return (float) ($this->resolveCartSummary()['subtotal'] ?? 0.0);
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
        $summary = $this->resolveCartSummary();
        $this->subtotal = (float) ($summary['subtotal'] ?? 0.0);
        $result = $this->calculateDiscountsAndShipping($summary);
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
    protected function calculateDiscountsAndShipping(array $summary): array
    {
        $amount = (float) ($summary['subtotal'] ?? 0.0);
        $coupon = session('checkout.coupon.code');
        $code = $coupon ? strtoupper(trim((string) $coupon)) : '';
        $engine = app(\App\Services\Discounts\DiscountEngine::class);
        $items = collect((array) ($summary['items'] ?? []))
            ->map(static function ($item): array {
                $item = (array) $item;

                return [
                    'product_id' => isset($item['product_id']) && is_numeric($item['product_id']) ? (int) $item['product_id'] : null,
                    'variant_id' => isset($item['variant_id']) && is_numeric($item['variant_id']) ? (int) $item['variant_id'] : null,
                    'quantity'   => isset($item['quantity']) && is_numeric($item['quantity']) ? (int) $item['quantity'] : 0,
                    'unit_price' => isset($item['price']) && is_numeric($item['price']) ? (float) $item['price'] : 0.0,
                ];
            })
            ->values()
            ->all();
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
     * @return array<string, mixed>
     */
    protected function resolveCartSummary(): array
    {
        $sessionId = (string) session()->getId();
        $userId = auth()->id();
        $resolvedUserId = is_numeric($userId) ? (int) $userId : null;

        return app(CartService::class)->getSummary($resolvedUserId, $sessionId);
    }

    /**
     * Render the Livewire component view with current state.
     */
    public function render(): View
    {
        return view('livewire.components.cart-total', ['subtotal' => $this->subtotal, 'discount' => $this->discount, 'total' => $this->finalTotal]);
    }
}
