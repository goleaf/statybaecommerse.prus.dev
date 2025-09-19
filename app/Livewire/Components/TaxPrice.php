<?php declare(strict_types=1);

namespace App\Livewire\Components;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * TaxPrice
 *
 * Livewire component for TaxPrice with reactive frontend functionality, real-time updates, and user interaction handling.
 */
class TaxPrice extends Component
{
    /**
     * Handle taxAmount functionality with proper error handling.
     * @return float
     */
    #[Computed]
    public function taxAmount(): float
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
            $coupon = session('checkout.coupon.code');
            $code = $coupon ? strtoupper(trim((string) $coupon)) : '';
            $items = [];
            if (class_exists(\Darryldecode\Cart\Facades\CartFacade::class)) {
                try {
                    foreach (\Darryldecode\Cart\Facades\CartFacade::session(session()->getId())->getContent() as $item) {
                        $items[] = ['product_id' => optional($item->associatedModel)->id, 'variant_id' => method_exists($item->associatedModel, 'getKey') ? $item->associatedModel->getKey() : null, 'quantity' => (int) $item->quantity, 'unit_price' => (float) $item->price];
                    }
                } catch (\Throwable $e) {
                    $items = [];
                }
            }
            $context = ['currency_code' => current_currency(), 'channel_id' => optional(config('app.url')), 'user_id' => optional(auth()->user())->id, 'group_ids' => [], 'partner_tier' => null, 'now' => now(), 'code' => $code, 'cart' => ['subtotal' => $subtotal, 'items' => $items]];
            $result = (array) app(\App\Services\Discounts\DiscountEngine::class)->evaluate($context);
            $discount = (float) ($result['discount_total_amount'] ?? 0.0);
        } catch (\Throwable $e) {
            $discount = 0.0;
        }
        return app(\App\Services\Taxes\TaxCalculator::class)->compute(max(0.0, $subtotal - $discount), null);
    }

    /**
     * Handle taxBreakdown functionality with proper error handling.
     * @return array
     */
    #[Computed]
    public function taxBreakdown(): array
    {
        $taxRate = app(\App\Services\Taxes\TaxCalculator::class)->getTaxRate(null);
        return ['tax_rate' => $taxRate, 'tax_amount' => $this->taxAmount, 'taxable_amount' => $this->taxAmount / ($taxRate / 100)];
    }

    /**
     * Handle updateAmounts functionality with proper error handling.
     * @return void
     */
    #[On('cartUpdated')]
    #[On('coupon-updated')]
    public function updateAmounts(): void
    {
        // Computed properties will automatically update
    }

    /**
     * Initialize the Livewire component with parameters.
     * @return void
     */
    public function mount(): void
    {
        // Computed properties will be calculated on first access
    }

    /**
     * Render the Livewire component view with current state.
     * @return View
     */
    public function render(): View
    {
        return view('livewire.components.tax-price', ['amount' => $this->taxAmount, 'breakdown' => $this->taxBreakdown]);
    }
}
