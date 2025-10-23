<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use App\Models\DiscountCode;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

final class DiscountController extends Controller
{
    public function index(): View
    {
        $discounts = Discount::query()
            ->withoutGlobalScopes()
            ->active()
            ->orderByDesc('priority')
            ->get(['id', 'name', 'description', 'type', 'value', 'starts_at', 'ends_at']);

        return view('frontend.discounts.index', [
            'discounts' => $discounts,
        ]);
    }

    public function coupons(): View
    {
        $coupons = DiscountCode::query()
            ->withoutGlobalScopes()
            ->where('is_active', true)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get(['id', 'code', 'name', 'description', 'type', 'value', 'expires_at']);

        return view('frontend.discounts.coupons', [
            'coupons' => $coupons,
        ]);
    }

    public function applyCoupon(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:255'],
        ]);

        $coupon = DiscountCode::query()
            ->withoutGlobalScopes()
            ->whereRaw('LOWER(code) = ?', [strtolower($data['code'])])
            ->where('is_active', true)
            ->first();

        if (! $coupon) {
            return redirect()->route('frontend.discounts.coupons')->withErrors([
                'code' => __('The provided coupon is not valid.'),
            ]);
        }

        Session::put('applied_coupon', $coupon->code);
        Session::put('cart_discount', (float) $coupon->value);

        return redirect()->route('frontend.cart.index')->with('status', __('Coupon applied successfully.'));
    }

    public function removeCoupon(): RedirectResponse
    {
        Session::forget('applied_coupon');
        Session::forget('cart_discount');

        return redirect()->route('frontend.cart.index')->with('status', __('Coupon removed.'));
    }
}
