<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Discount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
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

        return view('frontend.discounts.index', ['discounts' => $discounts]);
    }

    public function coupons(): View
    {
        $coupons = Coupon::query()
            ->valid()
            ->orderByDesc('created_at')
            ->paginate(12);

        return view('frontend.discounts.coupons', ['coupons' => $coupons]);
    }

    public function applyCoupon(Request $request): RedirectResponse|JsonResponse
    {
        /** @var array<string, mixed> $validated */
        $validated = $request->validate([
            'code' => ['required', 'string'],
            // Allow API callers to provide the current subtotal for validation when bypassing the cart session.
            'cart.subtotal' => ['nullable', 'numeric', 'min:0'],
        ]);

        if ($request->expectsJson()) {
            // Provide an early JSON response for SPA/API clients without altering the web flow.
            return $this->applyCouponJsonResponse($validated);
        }

        $cart = Session::get('cart', []);
        if (! is_array($cart)) {
            $cart = [];
        }
        if (empty($cart)) {
            return redirect()->route('frontend.cart.index')->withErrors([
                'cart' => __('Add items to your cart before applying a coupon.'),
            ]);
        }

        /** @var string $codeValue */
        $codeValue = $validated['code'];

        $coupon = Coupon::query()
            ->valid()
            // Ensure the lookup is case-insensitive so storefront forms remain forgiving.
            ->whereRaw('UPPER(code) = ?', [mb_strtoupper($codeValue)])
            ->first();

        if (! $coupon) {
            return redirect()->back()->withErrors([
                'code' => __('The provided coupon code is not valid.'),
            ]);
        }

        $summary = $this->buildCartSummary();

        $minimumAmount = $coupon->minimum_amount !== null ? (float) $coupon->minimum_amount : null;

        if ($minimumAmount !== null && $summary['subtotal'] < $minimumAmount) {
            return redirect()->back()->withErrors([
                'code' => __('This coupon requires a minimum order amount of :amount.', ['amount' => app_money_format($minimumAmount)]),
            ]);
        }

        $discountAmount = $this->calculateDiscount($coupon, $summary['subtotal']);

        Session::put('cart_discount', $discountAmount);
        Session::put('applied_coupon', [
            'id'   => $coupon->getKey(),
            'code' => $coupon->code,
        ]);

        return redirect()->route('frontend.cart.index')->with('status', 'coupon-applied');
    }

    public function removeCoupon(Request $request): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            // Serve a JSON response for API consumers removing an active coupon.
            return $this->removeCouponJsonResponse();
        }

        Session::forget('cart_discount');
        Session::forget('applied_coupon');

        return redirect()->route('frontend.cart.index')->with('status', 'coupon-removed');
    }

    /**
     * @param array<string, mixed> $validated
     */
    private function applyCouponJsonResponse(array $validated): JsonResponse
    {
        // Normalise incoming data to guard against locale-specific formatting differences.
        /** @var string $codeValue */
        $codeValue = $validated['code'];
        $code = mb_strtoupper($codeValue);

        $rawSubtotal = Arr::get($validated, 'cart.subtotal', 0.0);
        $subtotal = is_numeric($rawSubtotal) ? (float) $rawSubtotal : 0.0;

        $coupon = Coupon::query()
            ->valid()
            // Match the lookup logic used for the web flow to keep behaviour uniform.
            ->whereRaw('UPPER(code) = ?', [$code])
            ->first();

        if (! $coupon) {
            // Reset coupon data to avoid stale checkout calculations when an invalid code is supplied.
            Session::forget('checkout.coupon');
            Session::forget('cart_discount');
            Session::forget('applied_coupon');

            return response()->json([
                'success' => false,
                'message' => __('The provided coupon code is not valid.'),
            ], 422);
        }

        $minimumAmount = $coupon->minimum_amount !== null ? (float) $coupon->minimum_amount : null;

        if ($minimumAmount !== null && $subtotal < $minimumAmount) {
            return response()->json([
                'success' => false,
                'message' => __('This coupon requires a minimum order amount of :amount.', [
                    'amount' => app_money_format($minimumAmount),
                ]),
            ], 422);
        }

        $discountAmount = $this->calculateDiscount($coupon, $subtotal);

        if ($discountAmount <= 0.0) {
            return response()->json([
                'success' => false,
                'message' => __('This coupon does not provide a discount for the current cart.'),
            ], 422);
        }

        $payload = [
            'code'            => $coupon->code,
            'discount_amount' => $discountAmount,
        ];

        // Store both legacy cart keys and the checkout payload so Blade, Livewire, and API flows stay aligned.
        Session::put('checkout.coupon', $payload);
        Session::put('cart_discount', $discountAmount);
        Session::put('applied_coupon', ['code' => $coupon->code]);

        return response()->json([
            'success' => true,
            'message' => __('Coupon applied successfully.'),
            'coupon'  => $payload,
        ]);
    }

    private function removeCouponJsonResponse(): JsonResponse
    {
        // Keep the previously applied coupon in case the client wants to show what was removed.
        /** @var array<string, mixed>|null $previousCoupon */
        $previousCoupon = Session::get('checkout.coupon');

        Session::forget('checkout.coupon');
        Session::forget('cart_discount');
        Session::forget('applied_coupon');

        return response()->json([
            'success' => true,
            'message' => __('Coupon removed.'),
            'coupon'  => $previousCoupon ?? ['code' => null, 'discount_amount' => 0.0],
        ]);
    }

    private function calculateDiscount(Coupon $coupon, float $subtotal): float
    {
        $discount = $coupon->type === 'percentage' ? $subtotal * ((float) $coupon->value / 100) : (float) $coupon->value;
        if ($coupon->maximum_discount) {
            $discount = min($discount, (float) $coupon->maximum_discount);
        }

        if ($coupon->type === 'fixed') {
            $discount = min($discount, $subtotal);
        }

        return round(max($discount, 0), 2);
    }

    /**
     * @return array{subtotal: float, tax: float, shipping: float, discount: float, total: float}
     */
    private function buildCartSummary(): array
    {
        $cart = Session::get('cart', []);
        if (! is_array($cart)) {
            $cart = [];
        }
        $subtotal = 0.0;

        foreach ($cart as $item) {
            if (! is_array($item)) {
                continue;
            }

            $price = $item['price'] ?? null;
            $quantity = $item['quantity'] ?? null;

            $price = is_numeric($price) ? (float) $price : 0.0;

            $quantity = is_numeric($quantity) ? (int) $quantity : 0;

            $subtotal += $price * $quantity;
        }

        $taxRateValue = config('shared.tax.default_rate', 0.21);
        $taxRate = is_numeric($taxRateValue) ? (float) $taxRateValue : 0.0;
        $tax = $subtotal * $taxRate;
        $shipping = $subtotal > 50 ? 0 : 5.99;
        $rawDiscount = Session::get('cart_discount', 0);
        $discount = is_numeric($rawDiscount) ? (float) $rawDiscount : 0.0;
        $total = $subtotal + $tax + $shipping - $discount;

        return [
            'subtotal' => round($subtotal, 2),
            'tax'      => round($tax, 2),
            'shipping' => round($shipping, 2),
            'discount' => round($discount, 2),
            'total'    => round(max($total, 0), 2),
        ];
    }
}
