<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\Cart\CartLifecycleService;
use App\Services\Pricing\PriceCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class CheckoutController extends Controller
{
    public function index(Request $request): View
    {
        $items = $this->getCartItems($request);

        return view('frontend.checkout.index', [
            'cartItems' => $items,
            'summary' => $this->summarize($items),
        ]);
    }

    public function process(Request $request, CartLifecycleService $cartLifecycleService): RedirectResponse
    {
        $items = $this->getCartItems($request);
        if ($items->isEmpty()) {
            return redirect()->route('frontend.cart.index')->with('error', __('Your cart is empty.'));
        }

        $validated = $request->validate([
            'payment_method' => ['required', 'string', 'max:255'],
            'confirm' => ['accepted'],
        ]);

        $order = DB::transaction(function () use ($items, $request, $validated) {
            $subtotal = (float) $items->sum(fn (CartItem $item) => $item->calculateSubtotal());
            $breakdown = app(PriceCalculator::class)->breakdown($subtotal);

            $order = Order::query()->create([
                'number' => Str::upper(Str::random(10)),
                'user_id' => $request->user()?->id,
                'status' => 'processing',
                'subtotal' => $breakdown->subtotal,
                'tax_amount' => $breakdown->tax,
                'shipping_amount' => $breakdown->shipping,
                'discount_amount' => $breakdown->discount,
                'total' => $breakdown->total,
                'currency' => $breakdown->currency,
                'billing_address' => [],
                'shipping_address' => [],
                'payment_status' => 'paid',
                'payment_method' => $validated['payment_method'],
                'payment_reference' => (string) Str::uuid(),
            ]);

            foreach ($items as $item) {
                OrderItem::query()->create([
                    'order_id' => $order->getKey(),
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'name' => $item->product_snapshot['name'] ?? $item->product?->name,
                    'sku' => $item->product_snapshot['sku'] ?? $item->product?->sku,
                    'quantity' => $item->quantity,
                    'unit_price' => (float) $item->price,
                    'price' => (float) $item->price,
                    'total' => $item->calculateSubtotal(),
                    'notes' => $item->notes,
                ]);
            }

            return $order;
        });

        $cartLifecycleService->clearAfterCheckout(
            $request->user()?->id,
            $request->session()->getId(),
            $order->payment_status ?? null
        );

        $request->session()->put('checkout.last_order_id', $order->getKey());

        return redirect()->route('frontend.checkout.success')->with('status', __('Order placed successfully.'));
    }

    public function success(Request $request): View|RedirectResponse
    {
        $orderId = $request->session()->pull('checkout.last_order_id');
        if (! $orderId) {
            return redirect()->route('frontend.cart.index')->with('info', __('No recent checkout found.'));
        }

        $order = Order::withoutGlobalScopes()->with('items')->findOrFail($orderId);

        return view('frontend.checkout.success', [
            'order' => $order,
        ]);
    }

    public function cancel(Request $request, CartLifecycleService $cartLifecycleService): View
    {
        $cartLifecycleService->clearForAbandonedCheckout($request->user()?->id, $request->session()->getId());

        return view('frontend.checkout.cancel');
    }

    private function getCartItems(Request $request): Collection
    {
        return CartItem::query()
            ->where('session_id', $request->session()->getId())
            ->orderBy('created_at')
            ->get();
    }

    private function summarize(Collection $items): array
    {
        $subtotal = (float) $items->sum(fn (CartItem $item) => $item->calculateSubtotal());
        $breakdown = app(PriceCalculator::class)->breakdown($subtotal);

        return ['item_count' => (int) $items->sum('quantity')] + $breakdown->toSummary();
    }
}
