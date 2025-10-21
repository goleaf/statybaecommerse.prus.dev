<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\Cart\CartLifecycleService;
use App\Services\Pricing\PriceCalculator;
use App\Support\ApiErrorResponse;
use App\Support\ErrorCodes;
use Illuminate\Http\JsonResponse;
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
            'summary'   => $this->summarize($items),
        ]);
    }

    public function process(Request $request, CartLifecycleService $cartLifecycleService): RedirectResponse|JsonResponse
    {
        $items = $this->getCartItems($request);
        if ($items->isEmpty()) {
            if ($request->expectsJson()) {
                // Produce a structured error payload when the cart has no purchasable items.
                return ApiErrorResponse::problem(
                    request: $request,
                    errorCode: ErrorCodes::CHECKOUT_CART_EMPTY,
                    detail: __('errors.messages.checkout_empty'),
                    status: 422,
                    title: ApiErrorResponse::titleFor(ErrorCodes::CHECKOUT_CART_EMPTY),
                );
            }

            return redirect()->route('frontend.cart.index')->with('error', __('Your cart is empty.'));
        }

        $validated = $request->validate([
            'payment_method' => ['required', 'string', 'max:255'],
            'confirm'        => ['accepted'],
        ]);

        $order = DB::transaction(function () use ($items, $request, $validated) {
            $subtotal = (float) $items->sum(fn (CartItem $item) => $item->calculateSubtotal());
            $breakdown = app(PriceCalculator::class)->breakdown($subtotal);

            $order = Order::query()->create([
                'number'            => Str::upper(Str::random(10)),
                'user_id'           => $request->user()?->id,
                'status'            => 'processing',
                'subtotal'          => $breakdown->subtotal,
                'tax_amount'        => $breakdown->tax,
                'shipping_amount'   => $breakdown->shipping,
                'discount_amount'   => $breakdown->discount,
                'total'             => $breakdown->total,
                'currency'          => $breakdown->currency,
                'billing_address'   => [],
                'shipping_address'  => [],
                'payment_status'    => 'paid',
                'payment_method'    => $validated['payment_method'],
                'payment_reference' => (string) Str::uuid(),
            ]);

            foreach ($items as $item) {
                OrderItem::query()->create([
                    'order_id'           => $order->getKey(),
                    'product_id'         => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'name'               => $item->product_snapshot['name'] ?? $item->product?->name,
                    'sku'                => $item->product_snapshot['sku'] ?? $item->product?->sku,
                    'quantity'           => $item->quantity,
                    'unit_price'         => (float) $item->price,
                    'price'              => (float) $item->price,
                    'total'              => $item->calculateSubtotal(),
                    'notes'              => $item->notes,
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

        if ($request->expectsJson()) {
            // Return a compact success document for API clients that initiate checkout via AJAX.
            return response()->json([
                'success' => true,
                'message' => __('Order placed successfully.'),
                'order'   => [
                    'id'       => $order->getKey(),
                    'number'   => $order->number,
                    'status'   => $order->status,
                    'total'    => (float) $order->total,
                    'currency' => $order->currency,
                ],
            ], 201);
        }

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
        $sessionId = (string) $request->session()->getId();
        $userId = $request->user()?->getAuthIdentifier();

        return CartItem::query()
            ->where(function ($query) use ($sessionId, $userId): void {
                $hasCondition = false;

                if ($sessionId !== '') {
                    $query->where('session_id', $sessionId);
                    $hasCondition = true;
                }

                if ($userId !== null) {
                    if ($hasCondition) {
                        $query->orWhere('user_id', (int) $userId);
                    } else {
                        $query->where('user_id', (int) $userId);
                        $hasCondition = true;
                    }
                }

                if (! $hasCondition) {
                    // Prevent accidental full scans when no ownership context is available.
                    $query->whereRaw('1 = 0');
                }
            })
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
