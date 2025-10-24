<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Enums\PaymentMethod;
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
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Stringable;
use Throwable;

final class CheckoutController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Collection<int, CartItem> $items */
        $items = $this->getCartItems($request);

        return view('frontend.checkout.index', [
            'cartItems' => $items,
            'summary'   => $this->summarize($items),
        ]);
    }

    public function process(\App\Http\Requests\Frontend\CheckoutProcessRequest $request, CartLifecycleService $cartLifecycleService): RedirectResponse|JsonResponse
    {
        $throttleKey = $this->checkoutThrottleKey($request);
        $maxAttempts = (int) (Config::get('checkout.rate_limit.attempts', 3) ?? 3);
        if ($maxAttempts <= 0) {
            $maxAttempts = 3;
        }

        $decaySeconds = (int) (Config::get('checkout.rate_limit.decay_seconds', 60) ?? 60);
        if ($decaySeconds <= 0) {
            $decaySeconds = 60;
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
                return ApiErrorResponse::problem(
                    request: $request,
                    errorCode: ErrorCodes::CHECKOUT_CART_EMPTY,
                    detail: __('errors.messages.checkout_empty'),
                    status: 422,
                    title: ApiErrorResponse::titleFor(ErrorCodes::CHECKOUT_CART_EMPTY),
                );
            }

            return $this->respondError(
                $request,
                __('errors.messages.checkout_empty'),
                422,
                'frontend.cart.index',
                'cart'
            );
        }

        $validated = $request->validated();
        $paymentMethod = PaymentMethod::tryFrom($validated['payment_method'] ?? '') ?? PaymentMethod::CREDIT_CARD;
        $contactAddress = array_filter([
            'full_name' => $validated['full_name'] ?? null,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'address_line_1' => $validated['address_line_1'] ?? null,
            'address_line_2' => $validated['address_line_2'] ?? null,
            'city' => $validated['city'] ?? null,
            'postal_code' => $validated['postal_code'] ?? null,
            'country' => $validated['country'] ?? null,
        ], static fn ($value) => $value !== null && $value !== '');
        $subtotal = (float) $items->sum(fn (CartItem $item) => $item->calculateSubtotal());
        $breakdown = app(PriceCalculator::class)->breakdown($subtotal);

        try {
            $order = DB::transaction(function () use ($items, $request, $validated, $paymentMethod, $breakdown, $contactAddress) {
                /** @var Order $order */
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
                    'billing_address'   => $contactAddress,
                    'shipping_address'  => $contactAddress,
                    'payment_status'    => 'paid',
                    'payment_method'    => $paymentMethod->value,
                    'payment_reference' => (string) Str::uuid(),
                    'notes'             => $validated['notes'] ?? null,
                ]);

                foreach ($items as $item) {
                    $snapshot = $item->product_snapshot ?? [];
                    $product = $item->product;

                    OrderItem::query()->create([
                        'order_id'           => $order->getKey(),
                        'product_id'         => $item->product_id,
                        'product_variant_id' => $item->product_variant_id ?? $item->variant_id,
                        'name'               => $snapshot['name'] ?? $product?->name,
                        'sku'                => $snapshot['sku'] ?? $product?->sku,
                        'quantity'           => $item->quantity,
                        'unit_price'         => (float) ($item->price ?? $item->unit_price ?? 0),
                        'price'              => (float) ($item->price ?? $item->unit_price ?? 0),
                        'total'              => $item->calculateSubtotal(),
                        'notes'              => $item->notes,
                    ]);
                }

                return $order;
            });
        } catch (Throwable $exception) {
            Log::error('Failed to process checkout.', [
                'user_id'   => $request->user()?->getAuthIdentifier(),
                'session'   => $request->session()->getId(),
                'exception' => $exception,
            ]);

            return $this->respondError($request, __('ecommerce.payment_failed'), 500);
        }

        $cartLifecycleService->clearAfterCheckout(
            $request->user()?->id,
            $request->session()->getId(),
            $order->payment_status ?? null
        );

        RateLimiter::clear($throttleKey);

        Session::forget('cart');
        Session::forget('cart_discount');
        Session::forget('applied_coupon');
        Session::put('checkout_order_id', $order->getKey());

        $request->session()->put('checkout.last_order_id', $order->getKey());

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('ecommerce.order_placed_successfully'),
                'order_id' => $order->getKey(),
                'order' => [
                    'id' => $order->getKey(),
                    'number' => $order->number,
                ],
            ], 201);
        }

        return redirect()->route('frontend.checkout.success')->with('status', __('ecommerce.order_placed_successfully'));
    }

    public function success(Request $request): View
    {
        $orderId = Session::get('checkout_order_id');
        if ($orderId === null) {
            abort(404);
        }

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

        $items = CartItem::query()
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
        if ($items->isNotEmpty()) {
            return $items;
        }

        $sessionCart = $request->session()->get('cart', []);
        if (! is_array($sessionCart) || $sessionCart === []) {
            return collect();
        }

        return collect($sessionCart)
            ->filter(static fn ($item): bool => is_array($item) && isset($item['product_id']))
            ->map(static function (array $item): CartItem {
                $price = isset($item['price']) ? (float) $item['price'] : (float) ($item['unit_price'] ?? 0.0);
                $quantity = (int) ($item['quantity'] ?? 0);

                return CartItem::make([
                    'product_id' => (int) $item['product_id'],
                    'product_variant_id' => isset($item['product_variant_id']) ? (int) $item['product_variant_id'] : null,
                    'quantity' => $quantity > 0 ? $quantity : 1,
                    'price' => $price,
                    'unit_price' => $price,
                    'product_snapshot' => [
                        'name' => $item['name'] ?? null,
                        'sku' => $item['sku'] ?? null,
                        'image' => $item['image'] ?? null,
                    ],
                    'notes' => $item['notes'] ?? null,
                ]);
            })
            ->values();
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

    private function respondError(Request $request, string $message, int $status, ?string $redirectRoute = null, ?string $errorKey = null): RedirectResponse|JsonResponse
    {
        if ($request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'errors' => $errorKey !== null ? [$errorKey => [$message]] : [],
            ], $status);
        }

        $flashKey = $status >= 500 ? 'error' : ($status === 429 ? 'warning' : 'error');

        $response = redirect()->route($redirectRoute ?? 'frontend.checkout.index');

        if ($errorKey !== null) {
            $response = $response->withErrors([$errorKey => $message]);
        }

        return $response->with($flashKey, $message);
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
