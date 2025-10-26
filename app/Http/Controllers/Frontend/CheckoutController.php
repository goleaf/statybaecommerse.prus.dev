<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Events\OrderPlaced;
use App\Exceptions\CheckoutStockException;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\CartItem;
use App\Models\IdempotencyKey;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\VariantInventory;
use App\Services\Cart\CartLifecycleService;
use App\Services\Cart\CartService;
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
use JsonException;
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

        $validated = $request->validated();
        $paymentMethod = PaymentMethod::tryFrom($validated['payment_method'] ?? '') ?? PaymentMethod::CREDIT_CARD;
        $contactAddress = array_filter([
            'full_name'      => $validated['full_name'] ?? null,
            'email'          => $validated['email'] ?? null,
            'phone'          => $validated['phone'] ?? null,
            'address_line_1' => $validated['address_line_1'] ?? null,
            'address_line_2' => $validated['address_line_2'] ?? null,
            'city'           => $validated['city'] ?? null,
            'postal_code'    => $validated['postal_code'] ?? null,
            'country'        => $validated['country'] ?? null,
        ], static fn ($value) => $value !== null && $value !== '');

        $idempotencyKeyRecord = null;
        if ($request->wantsJson()) {
            $idempotencyOutcome = $this->ensureIdempotency(
                $request,
                $validated
            );

            if ($idempotencyOutcome['response'] instanceof JsonResponse) {
                return $idempotencyOutcome['response'];
            }

            $idempotencyKeyRecord = $idempotencyOutcome['record'];
        }

        $items = $this->getCartItems($request);
        if ($items->isEmpty()) {
            if ($request->expectsJson()) {
                $response = ApiErrorResponse::problem(
                    request: $request,
                    errorCode: ErrorCodes::CHECKOUT_CART_EMPTY,
                    detail: __('errors.messages.checkout_empty'),
                    status: 422,
                    title: ApiErrorResponse::titleFor(ErrorCodes::CHECKOUT_CART_EMPTY),
                );

                if ($idempotencyKeyRecord !== null) {
                    $this->storeIdempotencyResponse($idempotencyKeyRecord, $response);
                }

                return $response;
            }

            return $this->respondError(
                $request,
                __('errors.messages.checkout_empty'),
                422,
                'frontend.cart.index',
                'cart'
            );
        }

        // Build a canonical summary so totals are computed with the same logic everywhere.
        $cartSummary = app(CartService::class)->getSummary(
            $request->user()?->getAuthIdentifier(),
            (string) $request->session()->getId()
        );
        $serverTotals = $this->extractServerTotals($cartSummary);
        $clientTotals = $this->normalizeClientTotals($validated['totals'] ?? null, $serverTotals);

        if (! $this->totalsMatch($serverTotals, $clientTotals)) {
            $response = response()->json([
                'success'          => false,
                'message'          => __('The cart totals changed while processing the checkout.'),
                'reason'           => 'totals_mismatch',
                'corrected_totals' => $serverTotals,
            ], 409);

            if ($idempotencyKeyRecord !== null) {
                $this->storeIdempotencyResponse($idempotencyKeyRecord, $response);
            }

            return $response;
        }

        try {
            $order = DB::transaction(function () use ($items, $request, $validated, $paymentMethod, $contactAddress, $serverTotals, $idempotencyKeyRecord) {
                $userId = $request->user()?->getAuthIdentifier();
                $userId = is_numeric($userId) ? (int) $userId : null;
                $orderNumber = Str::upper(Str::random(10));

                /** @var Order $order */
                $order = Order::query()->create([
                    'number'            => $orderNumber,
                    'user_id'           => $userId,
                    'status'            => OrderStatus::PROCESSING,
                    'subtotal'          => $serverTotals['subtotal'],
                    'tax_amount'        => $serverTotals['tax'],
                    'shipping_amount'   => $serverTotals['shipping'],
                    'discount_amount'   => $serverTotals['discount'],
                    'total'             => $serverTotals['total'],
                    'currency'          => current_currency(),
                    'billing_address'   => $contactAddress,
                    'shipping_address'  => $contactAddress,
                    'payment_status'    => PaymentStatus::PAID,
                    'payment_method'    => $paymentMethod->value,
                    'payment_reference' => (string) Str::uuid(),
                    'notes'             => $validated['notes'] ?? null,
                ]);

                foreach ($items as $index => $item) {
                    $this->assertAndReserveStock($item, $idempotencyKeyRecord);

                    $snapshot = is_array($item->product_snapshot) ? $item->product_snapshot : [];
                    $product = $item->product ?? Product::query()->find($item->product_id);
                    $unitPrice = (float) ($item->price ?? $item->unit_price ?? $item->total_price ?? 0.0);
                    $lineTotal = round($item->calculateSubtotal(), 2);

                    OrderItem::query()->create([
                        'order_id'           => $order->getKey(),
                        'product_id'         => $item->product_id,
                        'product_variant_id' => $item->product_variant_id ?? $item->variant_id,
                        'name'               => $snapshot['name'] ?? $product?->name,
                        'sku'                => $snapshot['sku'] ?? $product?->sku,
                        'quantity'           => (int) $item->quantity,
                        'unit_price'         => $unitPrice,
                        'price'              => $unitPrice,
                        'total'              => $lineTotal,
                        'notes'              => $item->notes,
                    ]);
                }

                return $order->load('items');
            });
        } catch (CheckoutStockException $exception) {
            $response = response()->json([
                'success'          => false,
                'message'          => __('The requested items are no longer in stock.'),
                'reason'           => 'stock_unavailable',
                'corrected_totals' => $serverTotals,
            ], 409);

            if ($idempotencyKeyRecord !== null) {
                $this->storeIdempotencyResponse($idempotencyKeyRecord, $response);
            }

            return $response;
        } catch (Throwable $exception) {
            Log::error('Failed to process checkout.', [
                'user_id'   => $request->user()?->getAuthIdentifier(),
                'session'   => $request->session()->getId(),
                'exception' => $exception,
            ]);

            $errorResponse = $this->respondError($request, __('ecommerce.payment_failed'), 500);

            if ($request->wantsJson() && $idempotencyKeyRecord !== null && $errorResponse instanceof JsonResponse) {
                $this->storeIdempotencyResponse($idempotencyKeyRecord, $errorResponse);
            }

            return $errorResponse;
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

        activity()
            ->performedOn($order)
            ->withProperties([
                'idempotency_key' => $idempotencyKeyRecord?->key,
                'grand_total'     => $order->total,
            ])
            ->log('order.placed');

        event(new OrderPlaced($order));

        if ($request->wantsJson()) {
            $response = (new OrderResource($order))->response()->setStatusCode(201);

            if ($idempotencyKeyRecord !== null) {
                $this->storeIdempotencyResponse($idempotencyKeyRecord, $response, $order);
            }

            return $response;
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
     * Normalize the authoritative totals computed from the cart summary.
     *
     * @return array{subtotal:float,tax:float,shipping:float,discount:float,total:float,lines:array<int,float>}
     */
    private function extractServerTotals(array $summary): array
    {
        $lines = [];

        foreach ($summary['items'] ?? [] as $item) {
            $lines[] = round((float) ($item['total'] ?? 0.0), 2);
        }

        return [
            'subtotal' => round((float) ($summary['subtotal'] ?? 0.0), 2),
            'tax'      => round((float) ($summary['tax'] ?? 0.0), 2),
            'shipping' => round((float) ($summary['shipping'] ?? 0.0), 2),
            'discount' => round((float) ($summary['discount'] ?? 0.0), 2),
            'total'    => round((float) ($summary['total'] ?? 0.0), 2),
            'lines'    => $lines,
        ];
    }

    /**
     * Bring the client-provided totals into a consistent float format with fallbacks.
     *
     * @param  array<string, mixed>|null                                                                                                 $clientTotals
     * @return array{subtotal:float|null,tax:float|null,shipping:float|null,discount:float|null,total:float|null,lines:array<int,float>}
     */
    private function normalizeClientTotals(?array $clientTotals, array $fallback): array
    {
        $normalized = [];

        foreach (['subtotal', 'tax', 'shipping', 'discount', 'total'] as $key) {
            if (isset($clientTotals[$key])) {
                $normalized[$key] = round((float) $clientTotals[$key], 2);
            } else {
                $normalized[$key] = $fallback[$key];
            }
        }

        $lines = [];
        foreach (($clientTotals['lines'] ?? $fallback['lines'] ?? []) as $line) {
            $lines[] = round((float) $line, 2);
        }

        $normalized['lines'] = $lines;

        return $normalized;
    }

    /**
     * Compare totals with a small tolerance to avoid floating point noise.
     */
    private function totalsMatch(array $serverTotals, array $clientTotals): bool
    {
        foreach (['subtotal', 'tax', 'shipping', 'discount', 'total'] as $key) {
            if ($this->differs($serverTotals[$key], $clientTotals[$key])) {
                return false;
            }
        }

        $serverLines = $serverTotals['lines'];
        $clientLines = $clientTotals['lines'];

        if (count($serverLines) !== count($clientLines)) {
            return false;
        }

        foreach ($serverLines as $index => $expected) {
            if ($this->differs($expected, $clientLines[$index] ?? 0.0)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determine whether the numeric comparison exceeds the allowed delta.
     */
    private function differs(float $expected, float $actual): bool
    {
        return abs($expected - $actual) > 0.01;
    }

    /**
     * Ensure the request is wrapped in an idempotency guard for API consumers.
     *
     * @return array{record:?IdempotencyKey,response:?JsonResponse}
     */
    private function ensureIdempotency(Request $request, array $validated): array
    {
        $keyValue = trim((string) $request->header('Idempotency-Key', ''));

        if ($keyValue === '') {
            return [
                'record'   => null,
                'response' => response()->json([
                    'success' => false,
                    'message' => __('The Idempotency-Key header is required.'),
                    'reason'  => 'missing_idempotency_key',
                ], 400),
            ];
        }

        $result = ['record' => null, 'response' => null];
        $fingerprint = $this->buildRequestFingerprint($request, $validated);

        DB::transaction(function () use (&$result, $keyValue, $fingerprint, $request): void {
            $existing = IdempotencyKey::query()
                ->where('key', $keyValue)
                ->lockForUpdate()
                ->first();

            if ($existing !== null) {
                if ($existing->response_code !== null) {
                    if (! hash_equals($existing->request_hash, $fingerprint)) {
                        $result['response'] = response()->json([
                            'success' => false,
                            'message' => __('This idempotency key was already used with a different payload.'),
                            'reason'  => 'idempotency_mismatch',
                        ], 409);

                        return;
                    }

                    $body = $existing->response_body ?? [];
                    $result['response'] = response()->json($body, (int) $existing->response_code);

                    return;
                }

                if (! hash_equals($existing->request_hash, $fingerprint)) {
                    $result['response'] = response()->json([
                        'success' => false,
                        'message' => __('This idempotency key was already used with a different payload.'),
                        'reason'  => 'idempotency_mismatch',
                    ], 409);

                    return;
                }

                $result['response'] = response()->json([
                    'success' => false,
                    'message' => __('Another checkout request is already processing with this key.'),
                    'reason'  => 'idempotency_in_progress',
                ], 409);

                return;
            }

            $userId = $request->user()?->getAuthIdentifier();
            $userId = is_numeric($userId) ? (int) $userId : null;

            $result['record'] = IdempotencyKey::query()->create([
                'key'          => $keyValue,
                'user_id'      => $userId,
                'request_hash' => $fingerprint,
                'locked_at'    => now(),
            ]);
        });

        return $result;
    }

    /**
     * Generate a stable hash that ties the request payload to the cart contents.
     */
    private function buildRequestFingerprint(Request $request, array $validated): string
    {
        $rawContent = $request->getContent();
        // Fall back to the validated payload for non-JSON submissions.
        $payloadBody = $rawContent !== '' ? $rawContent : $this->canonicalizeForHash($validated);

        // Intentionally avoid session identifiers so replays still work after checkout mutates the session cookie.
        $payload = [
            'user_id' => $request->user()?->getAuthIdentifier(),
            'route'   => $request->route()?->getName(),
            'body'    => $payloadBody,
        ];

        try {
            return hash('sha256', json_encode($payload, JSON_THROW_ON_ERROR));
        } catch (JsonException) {
            return hash('sha256', serialize($payload));
        }
    }

    /**
     * Produce a deterministic structure so hashing is stable between retries.
     */
    private function canonicalizeForHash(mixed $value): mixed
    {
        if (is_array($value)) {
            // Recursively sort associative arrays while preserving indexed order for lists.
            $isList = array_keys($value) === range(0, count($value) - 1);

            if ($isList) {
                return array_map(fn ($item) => $this->canonicalizeForHash($item), $value);
            }

            ksort($value);

            foreach ($value as $key => $item) {
                $value[$key] = $this->canonicalizeForHash($item);
            }

            return $value;
        }

        if ($value instanceof Stringable) {
            return $value->__toString();
        }

        return $value;
    }

    /**
     * Persist the API response so subsequent requests can replay it without side effects.
     */
    private function storeIdempotencyResponse(IdempotencyKey $record, JsonResponse $response, ?Order $order = null): void
    {
        $body = null;

        try {
            $content = $response->getContent();
            $body = $content !== false ? json_decode($content, true, 512, JSON_THROW_ON_ERROR) : null;
        } catch (JsonException) {
            $body = null;
        }

        $record->forceFill([
            'response_code' => $response->getStatusCode(),
            'response_body' => $body,
            'order_id'      => $order?->getKey(),
            'locked_at'     => null,
        ])->save();
    }

    /**
     * Guard against inventory changes and reserve the required quantities.
     */
    private function assertAndReserveStock(CartItem $item, ?IdempotencyKey $idempotencyKey): void
    {
        $productId = $item->product_id;
        if (! $productId) {
            throw new CheckoutStockException('Cart item is missing a product reference.');
        }

        $product = Product::query()->lockForUpdate()->find($productId);
        if ($product === null) {
            throw new CheckoutStockException('Product no longer exists.');
        }

        $quantity = max(1, (int) $item->quantity);
        $reservation = $product->reserveStock(
            $quantity,
            null,
            array_filter([
                'cart_item_id'    => $item->getKey(),
                'idempotency_key' => $idempotencyKey?->key,
            ]),
            'checkout',
            $idempotencyKey?->key
        );

        if ($product->manage_stock && $reservation === null) {
            throw new CheckoutStockException('Unable to reserve product stock.');
        }

        if ($item->product_variant_id !== null) {
            $this->reserveVariantStock((int) $item->product_variant_id, $quantity);
        }
    }

    /**
     * Reserve inventory for tracked variants by updating their stock rows.
     */
    private function reserveVariantStock(int $variantId, int $quantity): void
    {
        $variant = ProductVariant::query()->find($variantId);
        if ($variant === null) {
            throw new CheckoutStockException('Variant no longer exists.');
        }

        if (! $variant->track_inventory) {
            return;
        }

        if (! $variant->availableQuantity() && $quantity > 0) {
            throw new CheckoutStockException('Variant is out of stock.');
        }

        $remaining = $quantity;

        $inventories = VariantInventory::query()
            ->where('variant_id', $variantId)
            ->lockForUpdate()
            ->orderBy('id')
            ->get();

        foreach ($inventories as $inventory) {
            $available = max(0, (int) $inventory->stock - (int) $inventory->reserved);
            if ($available <= 0) {
                continue;
            }

            $allocate = min($available, $remaining);
            if ($allocate <= 0) {
                continue;
            }

            $inventory->forceFill([
                'reserved'  => (int) $inventory->reserved + $allocate,
                'available' => max(0, ((int) $inventory->stock) - ((int) $inventory->reserved + $allocate)),
            ])->save();

            $remaining -= $allocate;

            if ($remaining <= 0) {
                break;
            }
        }

        if ($remaining > 0) {
            throw new CheckoutStockException('Not enough variant inventory available.');
        }
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
                    'product_id'         => (int) $item['product_id'],
                    'product_variant_id' => isset($item['product_variant_id']) ? (int) $item['product_variant_id'] : null,
                    'quantity'           => $quantity > 0 ? $quantity : 1,
                    'price'              => $price,
                    'unit_price'         => $price,
                    'product_snapshot'   => [
                        'name'  => $item['name'] ?? null,
                        'sku'   => $item['sku'] ?? null,
                        'image' => $item['image'] ?? null,
                    ],
                    'notes' => $item['notes'] ?? null,
                ]);
            })
            ->values();
    }

    /**
     * @param  Collection<int, CartItem>                                        $items
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
                'errors'  => $errorKey !== null ? [$errorKey => [$message]] : [],
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
            return 'checkout:user:' . $userId;
        }

        if ($userId instanceof Stringable) {
            return 'checkout:user:' . $userId->__toString();
        }

        $sessionId = (string) $request->session()->getId();
        if ($sessionId === '') {
            $sessionId = 'guest';
        }

        return sprintf('checkout:session:%s', $sessionId);
    }
}
