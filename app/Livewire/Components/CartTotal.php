<?php declare(strict_types=1);

namespace App\Livewire\Components;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

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
     * Handle cartSubtotal functionality with proper error handling.
     * @return float
     */
    #[Computed]
    public function cartSubtotal(): float
    {
        $sessionKey = session()->getId();
        if (class_exists(\Darryldecode\Cart\Facades\CartFacade::class)) {
            try {
                // @phpstan-ignore-next-line
                return (float) \Darryldecode\Cart\Facades\CartFacade::session($sessionKey)->getSubTotal();
            } catch (\Throwable $e) {
                return 0.0;
            }
        }
        return 0.0;
    }

    /**
     * Handle discountCalculation functionality with proper error handling.
     * @return array
     */
    #[Computed]
    public function discountCalculation(): array
    {
        return $this->calculateDiscountsAndShipping($this->cartSubtotal);
    }

    /**
     * Handle finalTotal functionality with proper error handling.
     * @return float
     */
    #[Computed]
    public function finalTotal(): float
    {
        $subtotal = $this->cartSubtotal;
        $result = $this->discountCalculation;
        $discount = (float) ($result['discount_total_amount'] ?? 0.0);
        $shippingDiscount = (float) data_get($result, 'shipping.discount_amount', 0.0);
        $shipping = (float) data_get(session()->get('checkout'), 'shipping_option.0.price', 0.0);
        return max(0, $subtotal - $discount + max(0, $shipping - $shippingDiscount));
    }

    /**
     * Handle updateTotals functionality with proper error handling.
     * @return void
     */
    #[On('cartUpdated')]
    #[On('coupon-updated')]
    public function updateTotals(): void
    {
        $this->compute();
    }

    /**
     * Initialize the Livewire component with parameters.
     * @return void
     */
    public function mount(): void
    {
        $this->compute();
    }

    /**
     * Handle compute functionality with proper error handling.
     * @return void
     */
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
        $this->total = max(0, $this->subtotal - $this->discount + max(0, $shipping - $this->shippingDiscount));
    }

    /**
     * Handle calculateDiscountsAndShipping functionality with proper error handling.
     * @param float $amount
     * @return array
     */
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
                    $items[] = ['product_id' => optional($item->associatedModel)->id, 'variant_id' => method_exists($item->associatedModel, 'getKey') ? $item->associatedModel->getKey() : null, 'quantity' => (int) $item->quantity, 'unit_price' => (float) $item->price];
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
                $partnerTier = \DB::table('sh_partner_users as pu')->join('sh_partners as p', 'p.id', '=', 'pu.partner_id')->where('pu.user_id', $userId)->value('p.tier');
            } catch (\Throwable $e) {
                // ignore if tables not present
            }
        }
        $context = ['currency_code' => current_currency(), 'channel_id' => optional(config('app.url')), 'user_id' => $userId, 'group_ids' => $groupIds, 'partner_tier' => $partnerTier, 'now' => now(), 'code' => $code, 'cart' => ['subtotal' => $amount, 'items' => $items]];
        try {
            return (array) $engine->evaluate($context);
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Render the Livewire component view with current state.
     * @return View
     */
    public function render(): View
    {
        return view('livewire.components.cart-total', ['subtotal' => $this->cartSubtotal, 'discount' => $this->discount, 'total' => $this->finalTotal]);
    }
}
