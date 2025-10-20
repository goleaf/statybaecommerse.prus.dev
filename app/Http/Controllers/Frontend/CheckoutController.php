<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

final class CheckoutController extends Controller
{
    public function index(): View
    {
        return view('frontend.checkout.index', [
            'cart' => $this->buildCartSummary(),
        ]);
    }

    public function process(Request $request): RedirectResponse
    {
        $cart = $this->buildCartSummary();

        if ($cart['items']->isEmpty()) {
            return redirect()->route('frontend.cart.index')->with('status', __('Your cart is empty.'));
        }

        $data = $request->validate([
            'email' => ['required', 'email'],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:50'],
            'payment_method' => ['required', 'string'],
        ]);

        $orderReference = 'CHK-' . now()->format('YmdHis');

        Session::put('checkout.receipt', [
            'order_reference' => $orderReference,
            'customer' => $data,
            'cart' => $cart,
            'processed_at' => now(),
        ]);

        Session::forget('cart');

        return redirect()->route('frontend.checkout.success');
    }

    public function success(): View
    {
        $receipt = Session::get('checkout.receipt');

        abort_if(empty($receipt), 404);

        return view('frontend.checkout.success', [
            'receipt' => $receipt,
        ]);
    }

    public function cancel(): View
    {
        return view('frontend.checkout.cancel');
    }

    private function buildCartSummary(): array
    {
        $items = collect(Session::get('cart', []))->map(function (array $item) {
            $quantity = (int) ($item['quantity'] ?? 0);
            $price = (float) ($item['price'] ?? 0);

            return [
                'id' => $item['id'] ?? null,
                'product_id' => $item['product_id'] ?? null,
                'name' => $item['name'] ?? '',
                'price' => $price,
                'quantity' => $quantity,
                'total' => round($price * $quantity, 2),
                'slug' => $item['slug'] ?? null,
            ];
        });

        $subtotal = $items->sum('total');
        $taxRate = (float) config('shared.tax.default_rate', 0.21);
        $tax = round($subtotal * $taxRate, 2);
        $shipping = $subtotal > 50 ? 0.0 : 5.99;
        $discount = (float) Session::get('cart_discount', 0);
        $total = max(0, round($subtotal + $tax + $shipping - $discount, 2));

        return [
            'items' => $items,
            'subtotal' => round($subtotal, 2),
            'tax' => $tax,
            'shipping' => round($shipping, 2),
            'discount' => round($discount, 2),
            'total' => $total,
        ];
    }
}
