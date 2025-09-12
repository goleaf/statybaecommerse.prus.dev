<?php

declare(strict_types=1);

namespace App\Livewire\Components;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Validate;
use Livewire\Component;

class CouponForm extends Component
{
    #[Validate('nullable|string|max:50')]
    public ?string $code = null;

    public function mount(): void
    {
        $this->code = session('checkout.coupon.code');
    }

    public function apply(): void
    {
        $this->validate();
        if ($this->code) {
            $raw = strtoupper(trim($this->code));
            $row = DB::table('sh_discount_codes')
                ->select('id', 'discount_id', 'expires_at', 'max_uses', 'usage_count')
                ->whereRaw('UPPER(code) = ?', [$raw])
                ->first();
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

    public function remove(): void
    {
        session()->forget('checkout.coupon');
        $this->reset('code');
        $this->dispatch('coupon-updated');
    }

    public function render(): View
    {
        return view('livewire.components.coupon-form');
    }
}
