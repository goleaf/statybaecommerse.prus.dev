<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use App\Services\Discounts\CouponApplicationService;
use App\Services\Discounts\DiscountContextBuilder;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;

/**
 * CouponForm
 *
 * Livewire component for CouponForm with reactive frontend functionality, real-time updates, and user interaction handling.
 *
 * @property string|null $code
 */
class CouponForm extends Component
{
    #[Validate('nullable|string|max:50')]
    public ?string $code = null;

    /**
     * Initialize the Livewire component with parameters.
     */
    public function mount(): void
    {
        $this->code = session('checkout.coupon.code');
    }

    /**
     * Handle isCouponApplied functionality with proper error handling.
     */
    #[Computed]
    public function isCouponApplied(): bool
    {
        return session()->has('checkout.coupon');
    }

    /**
     * Handle appliedCouponCode functionality with proper error handling.
     */
    #[Computed]
    public function appliedCouponCode(): ?string
    {
        return session('checkout.coupon.code');
    }

    /**
     * Handle apply functionality with proper error handling.
     */
    public function apply(): void
    {
        $this->validate();
        if (! $this->code) {
            $this->addError('code', __('messages.please_provide_a_coupon_code'));

            return;
        }

        $service = app(CouponApplicationService::class);
        $context = app(DiscountContextBuilder::class)->fromSession(auth()->user(), $this->code);
        $result = $service->apply($this->code, $context);

        if (! $result['success']) {
            $this->addError('code', $result['message']);
            $this->dispatch('coupon-updated', applied: false);

            return;
        }

        $this->code = $result['coupon']['code'];
        $this->dispatch('coupon-updated', applied: true, coupon: $result['coupon']);
    }

    /**
     * Handle remove functionality with proper error handling.
     */
    public function remove(): void
    {
        $service = app(CouponApplicationService::class);
        $context = app(DiscountContextBuilder::class)->fromSession(auth()->user());
        $service->remove($context);
        $this->reset('code');
        $this->dispatch('coupon-updated', applied: false);
    }

    /**
     * Render the Livewire component view with current state.
     */
    public function render(): View
    {
        return view('livewire.components.coupon-form');
    }
}
