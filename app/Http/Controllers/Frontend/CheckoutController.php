<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class CheckoutController extends Controller
{
    public function index(Request $request): View
    {
        $items = collect($request->session()->get('cart', []));
        $summary = $this->calculateSummary($items);

        return view('frontend.checkout.index', [
            'items' => $items,
            'summary' => $summary,
            'user' => $request->user(),
        ]);
    }

    public function process(Request $request): RedirectResponse
    {
        $items = collect($request->session()->get('cart', []));

        if ($items->isEmpty()) {
            return redirect()->route('frontend.cart.index')->withErrors([
                'cart' => __('Your cart is empty.'),
            ]);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'billing_address' => ['required', 'string', 'max:500'],
            'shipping_address' => ['nullable', 'string', 'max:500'],
            'payment_method' => ['required', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
        ]);

        $summary = $this->calculateSummary($items);
        $orderNumber = Str::upper(Str::random(10));

        $checkoutData = [
            'order_number' => $orderNumber,
            'customer' => [
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
            ],
            'billing_address' => $data['billing_address'],
            'shipping_address' => $data['shipping_address'] ?? $data['billing_address'],
            'payment_method' => $data['payment_method'],
            'notes' => $data['notes'] ?? null,
            'summary' => $summary,
            'items' => $items,
            'placed_at' => now(),
        ];

        $request->session()->put('checkout.completed', $checkoutData);
        $request->session()->forget('cart');

        return redirect()->route('frontend.checkout.success');
    }

    public function success(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('checkout.completed')) {
            return redirect()->route('frontend.cart.index');
        }

        $checkout = $request->session()->get('checkout.completed');

        return view('frontend.checkout.success', [
            'checkout' => $checkout,
        ]);
    }

    public function cancel(Request $request): View
    {
        return view('frontend.checkout.cancel', [
            'items' => collect($request->session()->get('cart', [])),
        ]);
    }

    private function calculateSummary(Collection $items): array
    {
        $subtotal = $items->sum(fn ($item) => ($item['price'] ?? 0) * ($item['quantity'] ?? 0));
        $taxRate = (float) config('shared.tax.default_rate', 0.21);
        $tax = $subtotal * $taxRate;
        $shipping = $subtotal > 50 ? 0 : 5.99;
        $discount = (float) session('cart_discount', 0);
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
