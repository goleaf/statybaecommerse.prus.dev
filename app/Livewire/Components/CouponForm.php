<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
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
        if ($this->code) {
            $raw = strtoupper(trim($this->code));
            $row = DB::table('sh_discount_codes')->select('id', 'discount_id', 'expires_at', 'max_uses', 'usage_count')->whereRaw('UPPER(code) = ?', [$raw])->first();
            if ($row) {
                $now = now();
                $valid = true;
                if ($row->expires_at && $now->greaterThan($row->expires_at)) {
                    $valid = false;
                }
                if ($row->max_uses !== null && $row->usage_count >= $row->max_uses) {
                    $valid = false;
                }
                if ($valid) {
                    session()->put('checkout.coupon', ['code' => $raw]);
                } else {
                    session()->forget('checkout.coupon');
                    $this->addError('code', __('This code is expired or fully used.'));
                }
            } else {
                session()->forget('checkout.coupon');
                $this->addError('code', __('This code is invalid.'));
            }
        }
        $this->dispatch('coupon-updated');
    }

    /**
     * Handle remove functionality with proper error handling.
     */
    public function remove(): void
    {
        session()->forget('checkout.coupon');
        $this->reset('code');
        $this->dispatch('coupon-updated');
    }

    /**
     * Render the Livewire component view with current state.
     */
    public function render(): View
    {
        return view('livewire.components.coupon-form');
    }
}
