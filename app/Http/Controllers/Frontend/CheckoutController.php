<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
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
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Stringable;
use Throwable;

final class CheckoutController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Collection<int, CartItem> $items */
        /** @var Collection<int, CartItem> $items */
        $items = $this->getCartItems($request);

        return view('frontend.checkout.index', [
            'cartItems' => $items,
            'summary'   => $this->summarize($items),
        ]);
    }

    public function process(Request $request, CartLifecycleService $cartLifecycleService): RedirectResponse|JsonResponse
    {
        $throttleKey = $this->checkoutThrottleKey($request);
        $maxAttempts = Config::get('checkout.rate_limit.attempts', 3);
        if (! is_int($maxAttempts)) {
            $maxAttempts = is_numeric($maxAttempts) ? (int) $maxAttempts : 3;
        }

        $decaySeconds = Config::get('checkout.rate_limit.decay_seconds', 60);
        if (! is_int($decaySeconds)) {
            $decaySeconds = is_numeric($decaySeconds) ? (int) $decaySeconds : 60;
        }

        if (RateLimiter::tooManyAttempts($throttleKey, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return $this->respondError(
                $request,
                __('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => (int) ceil($seconds / 60),
                ]),
                429
            );
        }

        RateLimiter::hit($throttleKey, $decaySeconds);

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

                    OrderItem::query()->create([
                        'order_id' => $order->getKey(),
                        'product_id' => $item->product_id,
                        'product_variant_id' => $item->product_variant_id,
                        'name' => $snapshot['name'] ?? $product?->name,
                        'sku' => $snapshot['sku'] ?? $product?->sku,
                        'quantity' => $item->quantity,
                        'unit_price' => (float) $item->price,
                        'price' => (float) $item->price,
                        'total' => $item->calculateSubtotal(),
                        'notes' => $item->notes,
                    ]);

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

            return $this->respondError($request, __('ecommerce.payment_failed'), 500);
        }

        RateLimiter::clear($throttleKey);

        Session::forget('cart');
        Session::forget('cart_discount');
        Session::forget('applied_coupon');
        Session::put('checkout_order_id', $order->getKey());

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('ecommerce.order_placed_successfully'),
                'order_id' => $order->getKey(),
            ]);
        }

        return redirect()->route('frontend.checkout.success')->with('status', __('ecommerce.order_placed_successfully'));
    }

    public function success(): View
    {
        $orderId = Session::get('checkout_order_id');

        /** @var Order $order */
        $order = Order::withoutGlobalScopes()->with('items')->findOrFail($orderId);

        if ($order->user_id !== null && $request->user()?->getKey() !== $order->user_id) {
            abort(403);
        }

        return view('frontend.checkout.success', [
            'order' => $order,
        ]);
    }

    public function cancel(Request $request, CartLifecycleService $cartLifecycleService): View
    {
        $cartLifecycleService->clearForAbandonedCheckout($request->user()?->id, $request->session()->getId());

        return view('frontend.checkout.cancel');
    }

    /**
     * @return Collection<int, CartItem>
     */
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

    /**
     * @param  Collection<int, CartItem>  $items
     * @return array{item_count:int, subtotal:float, formatted_subtotal:string}
     */
    private function summarize(Collection $items): array
    {
        $subtotal = (float) $items->sum(fn (CartItem $item) => $item->calculateSubtotal());
        $breakdown = app(PriceCalculator::class)->breakdown($subtotal);

        return ['item_count' => (int) $items->sum('quantity')] + $breakdown->toSummary();
    }

    private function respondError(Request $request, string $message, int $status, ?string $redirectRoute = null): RedirectResponse|JsonResponse
    {
        if ($request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], $status);
        }

        $flashKey = $status >= 500 ? 'error' : ($status === 429 ? 'warning' : 'error');

        return redirect()->route($redirectRoute ?? 'frontend.checkout.index')->with($flashKey, $message);
    }

    private function checkoutThrottleKey(Request $request): string
    {
        $userId = $request->user()?->getAuthIdentifier();

        if (is_int($userId) || is_string($userId)) {
            return 'checkout:user:'.$userId;
        }

        if ($userId instanceof Stringable) {
            return 'checkout:user:'.$userId->__toString();
        }

        $sessionId = (string) $request->session()->getId();
        if ($sessionId === '') {
            $sessionId = 'guest';
        }

        return sprintf('checkout:session:%s', $sessionId);
    }
}
