<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use App\Models\DiscountCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class DiscountController extends Controller
{
    public function index(Request $request): View
    {
        return view('frontend.discounts.index', [
            'activeDiscounts' => Discount::query()->active()->orderByDesc('created_at')->get(),
            'upcomingDiscounts' => Discount::query()->scheduled()->orderBy('starts_at')->get(),
            'expiredDiscounts' => Discount::query()->expired()->orderByDesc('ends_at')->limit(10)->get(),
        ]);
    }

    public function show(Discount $discount): View
    {
        $discount->load(['codes', 'conditions']);

        return view('frontend.discounts.show', [
            'discount' => $discount,
            'codes' => $discount->codes()->latest()->get(),
        ]);
    }

    public function coupons(): View
    {
        $codes = DiscountCode::query()->orderByDesc('created_at')->paginate(15);

        return view('frontend.discounts.coupons', [
            'codes' => $codes,
        ]);
    }

    public function applyCoupon(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string'],
        ]);

        $code = DiscountCode::query()
            ->whereRaw('LOWER(code) = ?', [Str::lower($data['code'])])
            ->first();

        if (! $code) {
            return back()->withErrors(['code' => __('Coupon not found.')]);
        }

        $cartItems = collect($request->session()->get('cart', []));
        $discountAmount = $this->calculateDiscountAmount($code, $cartItems);

        if ($discountAmount <= 0) {
            return back()->withErrors(['code' => __('This coupon cannot be applied to your cart.')]);
        }

        $request->session()->put('cart_discount', $discountAmount);
        $request->session()->put('applied_coupon', $code->code);

        return redirect()->route('frontend.cart.index')->with('status', __('Coupon applied successfully.'));
    }

    public function removeCoupon(Request $request): RedirectResponse
    {
        $request->session()->forget(['cart_discount', 'applied_coupon']);

        return back()->with('status', __('Coupon removed.'));
    }

    public function validate(Request $request): JsonResponse
    {
        $code = DiscountCode::query()
            ->whereRaw('LOWER(code) = ?', [Str::lower($request->input('code'))])
            ->first();

        if (! $code) {
            return response()->json(['valid' => false, 'message' => __('Coupon not found.')]);
        }

        return response()->json([
            'valid' => true,
            'discount' => $code->value,
            'type' => $code->type,
            'expires_at' => optional($code->expires_at)?->toDateTimeString(),
        ]);
    }

    private function calculateDiscountAmount(DiscountCode $code, Collection $cartItems): float
    {
        $subtotal = $cartItems->sum(fn ($item) => ($item['price'] ?? 0) * ($item['quantity'] ?? 0));

        if ($subtotal <= 0) {
            return 0.0;
        }

        $value = (float) $code->value;

        if ($code->type === 'percentage') {
            $amount = $subtotal * ($value / 100);
        } else {
            $amount = $value;
        }

        if ($code->maximum_discount) {
            $amount = min($amount, (float) $code->maximum_discount);
        }

        $amount = min($amount, $subtotal);

        return round($amount, 2);
    }
}
