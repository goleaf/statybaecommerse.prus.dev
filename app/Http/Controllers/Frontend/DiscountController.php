<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Discount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

final class DiscountController extends Controller
{
    public function index(): View
    {
        $discounts = Discount::query()
            ->active()
            ->orderByDesc('priority')
            ->paginate(12);

        return view('frontend.discounts.index', compact('discounts'));
    }

    public function coupons(): View
    {
        $coupons = Coupon::query()
            ->valid()
            ->orderByDesc('created_at')
            ->paginate(12);

        return view('frontend.discounts.coupons', compact('coupons'));
    }

    public function applyCoupon(\App\Http\Requests\ApplyCouponRequest $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validated();
        $expectsJson = $request->expectsJson();

        $cart = Session::get('cart', []);
        $subtotal = isset($validated['cart']['subtotal'])
            ? (float) $validated['cart']['subtotal']
            : $this->buildCartSummary()['subtotal'];

        if (! $expectsJson && empty($cart)) {
            return redirect()->route('frontend.cart.index')->withErrors([
                'cart' => __('Add items to your cart before applying a coupon.'),
            ]);
        }

        $normalizedCode = strtoupper($validated['code']);
        $coupon = Coupon::query()
            ->valid()
            ->whereRaw('upper(code) = ?', [$normalizedCode])
            ->first();

        if (! $coupon) {
            Session::forget('checkout.coupon');

            if ($expectsJson) {
                return response()->json([
                    'success' => false,
                    'message' => __('The provided coupon code is not valid.'),
                ], 422);
            }

            return redirect()->back()->withErrors([
                'code' => __('The provided coupon code is not valid.'),
            ]);
        }

        $minimumAmount = $coupon->minimum_amount !== null ? (float) $coupon->minimum_amount : null;

        if ($minimumAmount !== null && $subtotal < $minimumAmount) {
            Session::forget('checkout.coupon');

            if ($expectsJson) {
                return response()->json([
                    'success' => false,
                    'message' => __('This coupon requires a minimum order amount of :amount.', [
                        'amount' => app_money_format($minimumAmount),
                    ]),
                ], 422);
            }

            return redirect()->back()->withErrors([
                'code' => __('This coupon requires a minimum order amount of :amount.', ['amount' => app_money_format($coupon->minimum_amount ?? 0)]),
            ]);
        }

        $discountAmount = $this->calculateDiscount($coupon, $subtotal);

        Session::put('cart_discount', $discountAmount);
        Session::put('applied_coupon', [
            'id' => $coupon->getKey(),
            'code' => $coupon->code,
        ]);
        Session::put('checkout.coupon', [
            'id' => $coupon->getKey(),
            'code' => $coupon->code,
            'discount_amount' => $discountAmount,
        ]);

        if ($expectsJson) {
            return response()->json([
                'success' => true,
                'coupon' => [
                    'id' => $coupon->getKey(),
                    'code' => $coupon->code,
                    'type' => $coupon->type,
                    'value' => (float) $coupon->value,
                ],
                'discount_amount' => $discountAmount,
            ]);
        }

        return redirect()->route('frontend.cart.index')->with('status', 'coupon-applied');
    }

    public function removeCoupon(Request $request): RedirectResponse|JsonResponse
    {
        Session::forget('cart_discount');
        Session::forget('applied_coupon');
        Session::forget('checkout.coupon');

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
            ]);
        }

        return redirect()->route('frontend.cart.index')->with('status', 'coupon-removed');
    }

    private function calculateDiscount(Coupon $coupon, float $subtotal): float
    {
        $discount = 0.0;

        if ($coupon->type === 'percentage') {
            $discount = $subtotal * ((float) $coupon->value / 100);
        } else {
            $discount = (float) $coupon->value;
        }

        if ($coupon->maximum_discount) {
            $discount = min($discount, (float) $coupon->maximum_discount);
        }

        if ($coupon->type === 'fixed') {
            $discount = min($discount, $subtotal);
        }

        return round(max($discount, 0), 2);
    }

    private function buildCartSummary(): array
    {
        $cart = Session::get('cart', []);
        $subtotal = 0.0;

        foreach ($cart as $item) {
            $subtotal += (float) ($item['price'] ?? 0) * (int) ($item['quantity'] ?? 0);
        }

        $taxRate = config('shared.tax.default_rate', 0.21);
        $tax = $subtotal * $taxRate;
        $shipping = $subtotal > 50 ? 0 : 5.99;
        $discount = (float) Session::get('cart_discount', 0);
        $total = $subtotal + $tax + $shipping - $discount;

        return [
            'subtotal' => round($subtotal, 2),
            'tax' => round($tax, 2),
            'shipping' => round($shipping, 2),
            'discount' => round($discount, 2),
            'total' => round(max($total, 0), 2),
        ];
    }
}
