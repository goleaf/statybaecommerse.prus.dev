<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class CheckoutController extends Controller
{
    public function index(Request $request): View
    {
        return view('frontend.checkout.index', [
            'cart' => $this->buildCartSummary(),
            'user' => $request->user(),
            'addresses' => $request->user()?->addresses()->latest()->get() ?? collect(),
        ]);
    }

    public function process(Request $request): RedirectResponse
    {
        $cart = Session::get('cart', []);

        if (empty($cart)) {
            return redirect()->route('frontend.cart.index')->withErrors([
                'cart' => __('Your cart is empty.'),
            ]);
        }

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address_line_1' => ['required', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:120'],
            'postal_code' => ['required', 'string', 'max:32'],
            'country' => ['required', 'string', 'max:120'],
            'payment_method' => ['required', 'string', 'max:50'],
            'notes' => ['nullable', 'string'],
        ]);

        $summary = $this->buildCartSummary();

        $order = DB::transaction(function () use ($request, $validated, $summary, $cart) {
            $order = Order::create([
                'number' => 'ORD-'.Str::upper(Str::random(10)),
                'user_id' => $request->user()->getKey(),
                'status' => 'pending',
                'payment_status' => 'pending',
                'payment_method' => $validated['payment_method'],
                'subtotal' => $summary['subtotal'],
                'tax_amount' => $summary['tax'],
                'shipping_amount' => $summary['shipping'],
                'discount_amount' => $summary['discount'],
                'total' => $summary['total'],
                'currency' => config('app.currency', 'EUR'),
                'billing_address' => $this->formatAddress($validated),
                'shipping_address' => $this->formatAddress($validated),
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($cart as $item) {
                OrderItem::create([
                    'order_id' => $order->getKey(),
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['variant_id'] ?? null,
                    'name' => $item['name'],
                    'sku' => $item['sku'] ?? 'N/A',
                    'quantity' => (int) $item['quantity'],
                    'unit_price' => (float) $item['price'],
                    'price' => (float) $item['price'],
                    'discount_amount' => 0,
                    'total' => round((float) $item['price'] * (int) $item['quantity'], 2),
                ]);
            }

            return $order;
        });

        Session::forget('cart');
        Session::forget('cart_discount');
        Session::forget('applied_coupon');
        Session::put('checkout_order_id', $order->getKey());

        return redirect()->route('frontend.checkout.success');
    }

    public function success(): View
    {
        $orderId = Session::get('checkout_order_id');

        $order = $orderId
            ? Order::query()->with(['items'])->find($orderId)
            : null;

        abort_unless($order, 404);

        return view('frontend.checkout.success', [
            'order' => $order,
        ]);
    }

    public function cancel(): View
    {
        return view('frontend.checkout.cancel', [
            'cart' => $this->buildCartSummary(),
        ]);
    }

    private function formatAddress(array $data): array
    {
        return [
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'address_line_1' => $data['address_line_1'],
            'address_line_2' => $data['address_line_2'] ?? null,
            'city' => $data['city'],
            'postal_code' => $data['postal_code'],
            'country' => $data['country'],
        ];
    }

    private function buildCartSummary(): array
    {
        $cart = Session::get('cart', []);
        $items = [];
        $subtotal = 0.0;

        foreach ($cart as $item) {
            $lineTotal = (float) ($item['price'] ?? 0) * (int) ($item['quantity'] ?? 0);
            $items[] = array_merge($item, ['total' => round($lineTotal, 2)]);
            $subtotal += $lineTotal;
        }

        $taxRate = config('shared.tax.default_rate', 0.21);
        $tax = $subtotal * $taxRate;
        $shipping = $subtotal > 50 ? 0 : 5.99;
        $discount = (float) Session::get('cart_discount', 0);
        $total = $subtotal + $tax + $shipping - $discount;

        return [
            'items' => $items,
            'subtotal' => round($subtotal, 2),
            'tax' => round($tax, 2),
            'shipping' => round($shipping, 2),
            'discount' => round($discount, 2),
            'total' => round(max($total, 0), 2),
        ];
    }
}
