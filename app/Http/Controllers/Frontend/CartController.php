<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\CartAddItemRequest;
use App\Http\Requests\Frontend\CartRemoveItemRequest;
use App\Http\Requests\Frontend\CartUpdateItemRequest;
use App\Http\Resources\CartResource;
use App\Models\CartItem;
use App\Models\Product;
use App\Services\Cart\CartService;
use App\Services\Pricing\PriceCalculator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

final class CartController extends Controller
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly PriceCalculator $priceCalculator,
    ) {}

    public function index(Request $request): View
    {
        return view('frontend.cart.index', $this->buildCartSummary($request));
    }

    public function add(CartAddItemRequest $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validated();

        // Perform the quantity increment inside a transaction to guarantee atomic updates.
        $result = $this->adjustSingleCartItem($request, (int) $validated['product_id'], (int) $validated['quantity'], true);
        $statusCode = $result['status'] === 'ok' ? 201 : 409;

        if ($result['status'] === 'insufficient') {
            // When the product is completely out of stock, ensure the session cart mirrors the removal.
            $this->removeFromSessionCart((int) $result['product']->getKey());

            if ($request->expectsJson()) {
                return $this->respondWithCart($request, [
                    'success'            => false,
                    'message'            => __('The requested product is out of stock.'),
                    'available_quantity' => $result['available'],
                ], 409);
            }

            return redirect()->route('frontend.cart.index')->with('status', 'cart-stock-conflict');
        }

        /** @var CartItem $cartItem */
        $cartItem = $result['item'];
        /** @var Product $product */
        $product = $result['product'];

        // Synchronise the session cache with the persisted cart item snapshot.
        $this->syncSessionFromCartItem($cartItem, $product);

        if ($request->expectsJson()) {
            $payload = [
                'success'   => $result['status'] === 'ok',
                'cart_item' => [
                    'id'          => $cartItem->getKey(),
                    'product_id'  => $cartItem->product_id,
                    'quantity'    => $cartItem->quantity,
                    'total_price' => (float) ($cartItem->total_price ?? 0.0),
                ],
            ];

            if ($result['status'] === 'clamped') {
                $payload['message'] = __('Requested quantity exceeds available stock.');
                $payload['available_quantity'] = $result['available'];
            }

            return $this->respondWithCart($request, $payload, $statusCode);
        }

        $statusKey = $result['status'] === 'ok' ? 'cart-updated' : 'cart-stock-conflict';

        return redirect()->route('frontend.cart.index')->with('status', $statusKey);
    }

    public function update(CartUpdateItemRequest $request, ?CartItem $cartItem = null): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            $validated = $request->validated();

            if ($cartItem === null) {
                return response()->json([
                    'success' => false,
                    'message' => __('Cart item not found.'),
                ], 404);
            }

            // Adjust the quantity atomically to protect against concurrent updates.
            $result = $this->adjustSingleCartItem($request, (int) $cartItem->product_id, (int) $validated['quantity'], false, $cartItem);

            if ($result['status'] === 'insufficient') {
                $this->removeFromSessionCart((int) $result['product']->getKey());

                return $this->respondWithCart($request, [
                    'success'            => false,
                    'message'            => __('The requested product is out of stock.'),
                    'available_quantity' => $result['available'],
                ], 409);
            }

            /** @var CartItem $updatedItem */
            $updatedItem = $result['item'];
            $this->syncSessionFromCartItem($updatedItem, $result['product']);

            $payload = [
                'success'   => $result['status'] === 'ok',
                'cart_item' => [
                    'id'       => $updatedItem->getKey(),
                    'quantity' => $updatedItem->quantity,
                ],
            ];

            if ($result['status'] === 'clamped') {
                $payload['message'] = __('Requested quantity exceeds available stock.');
                $payload['available_quantity'] = $result['available'];
            }

            return $this->respondWithCart($request, $payload, $result['status'] === 'ok' ? 200 : 409);
        }

        $results = $this->applyBulkUpdates($request, $request->validatedItems());
        $hasConflict = false;

        foreach ($results as $result) {
            if (($result['item'] ?? null) instanceof CartItem) {
                $this->syncSessionFromCartItem($result['item'], $result['product']);
            } else {
                $this->removeFromSessionCart((int) $result['product']->getKey());
            }

            if ($result['status'] !== 'ok') {
                $hasConflict = true;
            }
        }

        $statusKey = $hasConflict ? 'cart-stock-conflict' : 'cart-updated';

        return redirect()->route('frontend.cart.index')->with('status', $statusKey);
    }

    public function remove(CartRemoveItemRequest $request, ?CartItem $cartItem = null): RedirectResponse|JsonResponse
    {
        $validated = $request->validated();

        if ($request->expectsJson()) {
            if ($cartItem === null && isset($validated['product_id'])) {
                $cartItem = $this->findCartItemForRequest($request, (int) $validated['product_id']);
            }

            if ($cartItem === null) {
                return response()->json([
                    'success' => false,
                    'message' => __('Cart item not found.'),
                ], 404);
            }

            $productId = (int) $cartItem->product_id;
            $cartItem->forceDelete();
            $this->removeFromSessionCart($productId);

            return $this->respondWithCart($request, [
                'success' => true,
                'message' => __('Item removed from cart.'),
            ]);
        }

        $productId = (int) ($validated['product_id'] ?? 0);

        DB::transaction(function () use ($request, $productId): void {
            $cartItem = $this->findCartItemForRequest($request, $productId);

            if ($cartItem !== null) {
                $cartItem->forceDelete();
            }
        });

        $cart = $this->getCart();
        Arr::forget($cart, (string) $productId);
        $this->saveCart($cart);

        return redirect()->route('frontend.cart.index')->with('status', 'cart-updated');
    }

    public function clear(Request $request): RedirectResponse|JsonResponse
    {
        $userId = $this->resolveUserId($request);
        $sessionId = (string) $request->session()->getId();

        $this->cartService->clear($userId, $sessionId);
        Session::forget('applied_coupon');
        Session::forget('checkout.coupon');

        if ($request->expectsJson()) {
            return $this->respondWithCart($request, [
                'success' => true,
                'message' => __('Cart cleared successfully.'),
            ]);
        }

        return redirect()->route('frontend.cart.index')->with('status', 'cart-cleared');
    }

    /**
     * Adjust a single cart item inside a dedicated transaction.
     *
     * @return array{status:string, available:int, item:?CartItem, product:Product}
     */
    private function adjustSingleCartItem(Request $request, int $productId, int $quantity, bool $additive, ?CartItem $boundItem = null): array
    {
        $sessionId = (string) $request->session()->getId();
        $userId = $this->resolveUserId($request);

        return DB::transaction(function () use ($sessionId, $userId, $productId, $quantity, $additive, $boundItem): array {
            $product = Product::withoutGlobalScopes()->lockForUpdate()->findOrFail($productId);

            $cartItem = $this->findCartItemForContext($sessionId, $userId, $product->getKey());

            if ($boundItem !== null && ($cartItem === null || $cartItem->getKey() !== $boundItem->getKey())) {
                throw (new ModelNotFoundException)->setModel(CartItem::class, [$boundItem->getKey()]);
            }

            $currentQuantity = $cartItem?->quantity ?? 0;
            $desiredQuantity = $additive ? ($currentQuantity + $quantity) : $quantity;

            $result = $this->performCartAdjustment($sessionId, $userId, $product, $cartItem, $desiredQuantity);
            $result['product'] = $product;

            return $result;
        });
    }

    /**
     * Apply a batch of cart updates under a single transaction.
     *
     * @param  list<array{product_id:int, quantity:int}>                                  $items
     * @return list<array{status:string, available:int, item:?CartItem, product:Product}>
     */
    private function applyBulkUpdates(Request $request, array $items): array
    {
        $sessionId = (string) $request->session()->getId();
        $userId = $this->resolveUserId($request);

        return DB::transaction(function () use ($items, $sessionId, $userId): array {
            $results = [];

            foreach ($items as $item) {
                $product = Product::withoutGlobalScopes()->lockForUpdate()->find($item['product_id']);

                if ($product === null) {
                    continue;
                }

                $cartItem = $this->findCartItemForContext($sessionId, $userId, $product->getKey());
                $result = $this->performCartAdjustment($sessionId, $userId, $product, $cartItem, (int) $item['quantity']);
                $result['product'] = $product;

                $results[] = $result;
            }

            return $results;
        });
    }

    /**
     * Persist the adjusted quantity and pricing, removing the item when stock is unavailable.
     *
     * @return array{status:string, available:int, item:?CartItem}
     */
    private function performCartAdjustment(string $sessionId, ?int $userId, Product $product, ?CartItem $cartItem, int $desiredQuantity): array
    {
        $available = $this->resolveAvailableQuantity($product);

        if ($available <= 0) {
            if ($cartItem !== null) {
                $cartItem->forceDelete();
            }

            return [
                'status'    => 'insufficient',
                'available' => 0,
                'item'      => null,
            ];
        }

        $clampedQuantity = min($desiredQuantity, $available);
        $cartItem ??= new CartItem;
        $cartItem->session_id = $sessionId;
        $cartItem->user_id = $userId;
        $cartItem->product_id = $product->getKey();
        $cartItem->quantity = $clampedQuantity;
        $cartItem->minimum_quantity = max(1, (int) ($product->minimum_quantity ?? 1));

        $this->applyCartItemPricing($cartItem, $product);
        $cartItem->save();

        return [
            'status'    => $clampedQuantity === $desiredQuantity ? 'ok' : 'clamped',
            'available' => $available,
            'item'      => $cartItem->refresh(),
        ];
    }

    /**
     * Normalise persisted monetary attributes for a cart line item.
     */
    private function applyCartItemPricing(CartItem $cartItem, Product $product): void
    {
        $unitPrice = (float) ($product->sale_price ?? $product->price ?? 0.0);
        $regularPrice = (float) ($product->price ?? $unitPrice);

        $cartItem->unit_price = $unitPrice;
        $cartItem->price = $unitPrice;
        $cartItem->discount_amount = max(0.0, $regularPrice - $unitPrice);
        $cartItem->total_price = round($unitPrice * $cartItem->quantity, 2);
        $cartItem->product_snapshot = array_filter([
            'name'  => $product->name,
            'price' => $unitPrice,
            'sku'   => $product->sku,
            'image' => $product->getFirstMediaUrl(config('media.storage.collection_name'), 'thumb') ?: null,
        ], static fn ($value) => $value !== null && $value !== '');
    }

    /**
     * Locate a persisted cart item for the current request context.
     */
    private function findCartItemForRequest(Request $request, int $productId): ?CartItem
    {
        $sessionId = (string) $request->session()->getId();
        $userId = $this->resolveUserId($request);

        return $this->findCartItemForContext($sessionId, $userId, $productId);
    }

    /**
     * Locate the cart item row scoped to the supplied identifiers with a pessimistic lock.
     */
    private function findCartItemForContext(string $sessionId, ?int $userId, int $productId): ?CartItem
    {
        return CartItem::withoutGlobalScopes()
            ->where('product_id', $productId)
            ->whereNull('variant_id')
            ->where(static function ($query) use ($sessionId, $userId): void {
                $query->where('session_id', $sessionId);

                if ($userId !== null) {
                    $query->orWhere('user_id', $userId);
                }
            })
            ->lockForUpdate()
            ->first();
    }

    /**
     * Refresh the session-stored cart entry with trusted server-side pricing.
     */
    private function syncSessionFromCartItem(CartItem $cartItem, Product $product): void
    {
        $cart = $this->getCart();
        $key = (string) $product->getKey();

        $cart[$key] = [
            'id'         => $cartItem->getKey(),
            'product_id' => $product->getKey(),
            'name'       => $product->name,
            'sku'        => $product->sku,
            'price'      => (float) ($cartItem->unit_price ?? 0.0),
            'quantity'   => $cartItem->quantity,
            'image'      => $product->getFirstMediaUrl(config('media.storage.collection_name'), 'thumb') ?: null,
        ];

        $this->saveCart($cart);
    }

    private function removeFromSessionCart(int $productId): void
    {
        $cart = $this->getCart();
        Arr::forget($cart, (string) $productId);
        $this->saveCart($cart);
    }

    private function respondWithCart(Request $request, array $payload = [], int $status = 200): JsonResponse
    {
        $summary = $this->cartService->getSummary($this->resolveUserId($request), (string) $request->session()->getId());
        $resource = CartResource::make($summary)->resolve($request);

        $payload['cart'] ??= $resource;
        $payload['summary'] ??= [
            'item_count' => $resource['item_count'],
            'subtotal'   => $resource['totals']['subtotal'],
            'tax'        => $resource['totals']['tax'],
            'shipping'   => $resource['totals']['shipping'],
            'discount'   => $resource['totals']['discount'],
            'total'      => $resource['totals']['total'],
        ];

        return response()->json($payload, $status);
    }

    /**
     * Resolve the available quantity while protecting against negative values.
     */
    private function resolveAvailableQuantity(Product $product): int
    {
        return max(0, $product->availableQuantity());
    }

    private function buildCartSummary(Request $request): array
    {
        $summary = $this->cartService->getSummary($this->resolveUserId($request), (string) $request->session()->getId());
        $breakdown = $this->priceCalculator->breakdown(
            subtotal: $summary['subtotal'],
            discount: $summary['discount'],
            shipping: $summary['shipping'],
            vatRate: config('shared.tax.default_rate', 0.21)
        );

        return [
            'items'    => $summary['items'],
            'subtotal' => $breakdown->subtotal,
            'tax'      => $breakdown->tax,
            'shipping' => $breakdown->shipping,
            'discount' => $breakdown->discount,
            'total'    => $breakdown->total,
            'summary'  => $breakdown->toSummary() + ['item_count' => $summary['count']],
        ];
    }

    private function resolveUserId(Request $request): ?int
    {
        $identifier = $request->user()?->getAuthIdentifier();

        return $identifier === null ? null : (int) $identifier;
    }

    private function getCart(): array
    {
        return Session::get('cart', []);
    }

    private function saveCart(array $cart): void
    {
        Session::put('cart', $cart);
    }
}
