<?php

declare(strict_types=1);

namespace App\Services\Cart;

use App\Models\CartItem;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Session\Session as SessionStore;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

final class CartService
{
    private const SESSION_KEY = 'cart';

    public function __construct(private readonly SessionStore $session) {}

    public function clear(?int $userId, string $sessionId, ?string $fallbackSessionId = null): void
    {
        if ($fallbackSessionId === null) {
            // Use the injected session store so tests relying on in-memory sessions remain in sync with production behaviour.
            $storedSessionId = $this->session->get('cart_session_id');
            $fallbackSessionId = is_string($storedSessionId) ? $storedSessionId : null;
        }

        $sessionIds = $this->normalizeSessionIds($sessionId, $fallbackSessionId);

        $this->clearCartSessions($sessionIds);
        $this->clearCartStorage($userId, $sessionIds);
        $this->clearSessionPayload();
        $this->forgetCachedSummary($userId, $sessionIds);

        if (function_exists('debug_cart')) {
            debug_cart('clear', [
                'session_id'  => $sessionId,
                'user_id'     => $userId,
                'session_ids' => $sessionIds,
            ]);
        }
    }

    /**
     * @return array{
     *     items: array<int, array{
     *         id:int|null,
     *         product_id:int|null,
     *         variant_id:int|null,
     *         name:string,
     *         price:float,
     *         quantity:int,
     *         total:float,
     *         image:?string,
     *         attributes: array<string, mixed>
     *     }>,
     *     count:int,
     *     subtotal:float,
     *     tax:float,
     *     shipping:float,
     *     discount:float,
     *     total:float
     * }
     */
    public function getSummary(?int $userId, string $sessionId): array
    {
        // Persist the session identifier on the injected store to avoid depending on the static facade in tests.
        $this->session->put('cart_session_id', $sessionId);

        if ($userId !== null) {
            // Prioritise persisted records for authenticated customers so stale session
            // payloads from previous guests do not override the canonical database state.
            $databaseSummary = $this->buildSummaryFromDatabase($userId, $sessionId);

            if ($this->summaryHasLineItems($databaseSummary)) {
                return $databaseSummary;
            }

            // Fall back to transient sources only when the database is empty, allowing
            // the caller to surface any guest cart items that still need to be merged.
            $facadeSummary = $this->buildSummaryFromFacade($sessionId);

            if ($this->summaryHasLineItems($facadeSummary)) {
                return $facadeSummary;
            }

            $sessionSummary = $this->buildSummaryFromSession();

            if ($this->summaryHasLineItems($sessionSummary)) {
                return $sessionSummary;
            }

            // Return the empty database payload so downstream consumers receive the
            // normalized structure expected by the caller when nothing was found.
            return $databaseSummary;
        }

        $summary = $this->buildSummaryFromFacade($sessionId);

        if ($this->summaryHasLineItems($summary)) {
            return $summary;
        }

        $summary = $this->buildSummaryFromSession();

        if ($this->summaryHasLineItems($summary)) {
            return $summary;
        }

        return $this->buildSummaryFromDatabase($userId, $sessionId);
    }

    /**
     * Determine whether the provided summary contains any cart line items.
     */
    private function summaryHasLineItems(array $summary): bool
    {
        // Inspect both the precomputed count and the raw items array so partially
        // hydrated payloads remain eligible even if a caller forgot to propagate the count.
        return ($summary['count'] ?? 0) > 0 || ! empty($summary['items'] ?? []);
    }

    public function getCount(?int $userId, string $sessionId): int
    {
        $summary = $this->getSummary($userId, $sessionId);

        return (int) $summary['count'];
    }

    public function getSessionCount(): int
    {
        $userIdentifier = Auth::id();
        $userId = is_numeric($userIdentifier) ? (int) $userIdentifier : null;

        return $this->getCount($userId, $this->session->getId());
    }

    /**
     * Clear all candidate cart sessions tracked for the user.
     *
     * Iterating through all normalized session identifiers avoids a conflict where
     * the fallback guest session retains items after a merge into an authenticated session.
     *
     * @param array<int, string> $sessionIds
     */
    private function clearCartSessions(array $sessionIds): void
    {
        if (! class_exists(\Darryldecode\Cart\Facades\CartFacade::class)) {
            return;
        }

        foreach ($sessionIds as $normalizedSessionId) {
            // Defensive guard in case downstream callers inject unexpected empty values.
            if ($normalizedSessionId === '') {
                continue;
            }

            try {
                \Darryldecode\Cart\Facades\CartFacade::session($normalizedSessionId)->clear();
            } catch (Throwable $throwable) {
                // Report the throwable but continue clearing the remaining sessions to ensure all cart states are reset.
                report($throwable);
            }
        }
    }

    /**
     * @param array<int, string> $sessionIds
     */
    private function clearCartStorage(?int $userId, array $sessionIds): void
    {
        if ($sessionIds === []) {
            return;
        }

        CartItem::withoutGlobalScopes()
            ->where(function (Builder $query) use ($sessionIds, $userId): void {
                $query->whereIn('session_id', $sessionIds);

                if ($userId !== null) {
                    $query->orWhere('user_id', $userId);
                }
            })
            ->get()
            ->each(static function (CartItem $item): void {
                $item->forceDelete();
            });

        DB::table('cart_items')
            ->whereIn('session_id', $sessionIds)
            ->when($userId !== null, static fn ($query) => $query->orWhere('user_id', $userId))
            ->delete();
    }

    private function clearSessionPayload(): void
    {
        // Forget all cart-related keys on the injected session store to ensure parity across runtime contexts.
        $this->session->forget(['cart', 'cart_discount', 'cart_session_id']);
    }

    /**
     * @param array<int, string> $sessionIds
     */
    private function forgetCachedSummary(?int $userId, array $sessionIds): void
    {
        $keys = [];

        foreach ($sessionIds as $id) {
            $keys[] = $this->summaryCacheKey($userId, $id);

            if ($userId !== null) {
                $keys[] = $this->summaryCacheKey(null, $id);
            }
        }

        foreach (array_unique($keys) as $key) {
            Cache::forget($key);
        }
    }

    /**
     * @return array<int, string>
     */
    private function normalizeSessionIds(string $sessionId, ?string $fallbackSessionId): array
    {
        $ids = [];

        foreach ([$sessionId, $fallbackSessionId] as $id) {
            if (is_string($id) && $id !== '') {
                $ids[] = $id;
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * @return array{
     *     items: array<int, array{
     *         id:int|null,
     *         product_id:int|null,
     *         variant_id:int|null,
     *         name:string,
     *         price:float,
     *         quantity:int,
     *         total:float,
     *         image:?string,
     *         attributes: array<string, mixed>
     *     }>,
     *     count:int,
     *     subtotal:float,
     *     tax:float,
     *     shipping:float,
     *     discount:float,
     *     total:float
     * }
     */
    private function buildSummaryFromFacade(string $sessionId): array
    {
        if (! class_exists(\Darryldecode\Cart\Facades\CartFacade::class)) {
            return $this->emptySummary();
        }

        try {
            $cart = \Darryldecode\Cart\Facades\CartFacade::session($sessionId);
            $items = [];
            $subtotal = 0.0;
            $count = 0;

            foreach ($cart->getContent() as $item) {
                $quantity = (int) ($item->quantity ?? 0);
                $price = (float) ($item->price ?? 0.0);
                $total = $quantity * $price;

                $associatedModel = $item->associatedModel ?? null;
                $productId = $this->extractNullableInt(
                    is_object($associatedModel) && method_exists($associatedModel, 'getKey')
                        ? $associatedModel->getKey()
                        : null,
                );
                $attributes = $this->normalizeAttributes($item->attributes ?? []);
                $variantId = $this->extractNullableInt(
                    $attributes['variant_id'] ?? (
                        is_object($associatedModel) && method_exists($associatedModel, 'getAttribute')
                            ? $associatedModel->getAttribute('variant_id')
                            : null
                    ),
                );

                $image = null;
                if (is_object($associatedModel) && method_exists($associatedModel, 'getFirstMediaUrl')) {
                    $image = $associatedModel->getFirstMediaUrl('images');
                }

                $items[] = [
                    'id'         => $this->extractNullableInt($item->id ?? null),
                    'product_id' => $productId,
                    'variant_id' => $variantId,
                    'name'       => is_string($item->name ?? null) ? (string) $item->name : '',
                    'price'      => round($price, 2),
                    'quantity'   => $quantity,
                    'total'      => round($total, 2),
                    'image'      => $image,
                    'attributes' => $this->purgeVariantIdentifier($attributes),
                ];

                $subtotal += $total;
                $count += $quantity;
            }

            return $this->finalizeSummary($items, $count, $subtotal);
        } catch (Throwable $throwable) {
            report($throwable);

            return $this->emptySummary();
        }
    }

    /**
     * @return array{
     *     items: array<int, array{
     *         id:int|null,
     *         product_id:int|null,
     *         variant_id:int|null,
     *         name:string,
     *         price:float,
     *         quantity:int,
     *         total:float,
     *         image:?string,
     *         attributes: array<string, mixed>
     *     }>,
     *     count:int,
     *     subtotal:float,
     *     tax:float,
     *     shipping:float,
     *     discount:float,
     *     total:float
     * }
     */
    private function buildSummaryFromSession(): array
    {
        // Pull the raw cart payload directly from the injected session implementation for better testability.
        $cart = $this->session->get('cart', []);
        $items = [];
        $subtotal = 0.0;
        $count = 0;

        if (! is_array($cart)) {
            $cart = [];
        }

        foreach ($cart as $item) {
            if (! is_array($item)) {
                continue;
            }

            $quantity = $this->extractPositiveInt($item['quantity'] ?? 0);
            $price = $this->extractFloat($item['price'] ?? 0.0);
            $total = $quantity * $price;

            $items[] = [
                'id'         => $this->extractNullableInt($item['id'] ?? null),
                'product_id' => $this->extractNullableInt($item['product_id'] ?? null),
                'variant_id' => $this->extractNullableInt($item['variant_id'] ?? null),
                'name'       => isset($item['name']) && is_string($item['name']) ? $item['name'] : '',
                'price'      => round($price, 2),
                'quantity'   => $quantity,
                'total'      => round($total, 2),
                'image'      => isset($item['image']) && is_string($item['image']) ? $item['image'] : null,
                'attributes' => $this->normalizeAttributes($item['attributes'] ?? $item['options'] ?? []),
            ];

            $subtotal += $total;
            $count += $quantity;
        }

        return $this->finalizeSummary($items, $count, $subtotal);
    }

    /**
     * @return array{
     *     items: array<int, array{
     *         id:int|null,
     *         product_id:int|null,
     *         variant_id:int|null,
     *         name:string,
     *         price:float,
     *         quantity:int,
     *         total:float,
     *         image:?string,
     *         attributes: array<string, mixed>
     *     }>,
     *     count:int,
     *     subtotal:float,
     *     tax:float,
     *     shipping:float,
     *     discount:float,
     *     total:float
     * }
     */
    private function buildSummaryFromDatabase(?int $userId, string $sessionId): array
    {
        $items = [];
        $subtotal = 0.0;
        $count = 0;

        $cartItems = CartItem::withoutGlobalScopes()
            ->where(function (Builder $query) use ($sessionId, $userId): void {
                $query->where('session_id', $sessionId);

                if ($userId !== null) {
                    $query->orWhere('user_id', $userId);
                }
            })
            ->get();

        foreach ($cartItems as $item) {
            $quantity = (int) $item->quantity;
            $price = (float) ($item->price ?? $item->unit_price ?? 0.0);
            $total = $quantity * $price;

            $snapshot = is_array($item->product_snapshot) ? $item->product_snapshot : [];
            $name = Arr::get($snapshot, 'name');
            $name = is_string($name) ? $name : '';
            $image = Arr::get($snapshot, 'image');
            $image = is_string($image) ? $image : null;
            $attributesSource = $item->getAttribute('attributes');
            if (! is_array($attributesSource) || $attributesSource === []) {
                $attributesSource = Arr::get($snapshot, 'attributes', []);
            }
            $attributes = $this->normalizeAttributes($attributesSource);

            $items[] = [
                'id'         => $this->extractNullableInt($item->getKey()),
                'product_id' => $this->extractNullableInt($item->product_id),
                'variant_id' => $this->extractNullableInt($item->variant_id ?? $item->product_variant_id),
                'name'       => $name,
                'price'      => round($price, 2),
                'quantity'   => $quantity,
                'total'      => round($total, 2),
                'image'      => $image,
                'attributes' => $this->purgeVariantIdentifier($attributes),
            ];

            $subtotal += $total;
            $count += $quantity;
        }

        return $this->finalizeSummary($items, $count, $subtotal);
    }

    /**
     * @param  array<int, array{
     *     id:int|null,
     *     product_id:int|null,
     *     variant_id:int|null,
     *     name:string,
     *     price:float,
     *     quantity:int,
     *     total:float,
     *     image:?string,
     *     attributes: array<string, mixed>
     * }> $items
     * @return array{
     *     items: array<int, array{
     *         id:int|null,
     *         product_id:int|null,
     *         variant_id:int|null,
     *         name:string,
     *         price:float,
     *         quantity:int,
     *         total:float,
     *         image:?string,
     *         attributes: array<string, mixed>
     *     }>,
     *     count:int,
     *     subtotal:float,
     *     tax:float,
     *     shipping:float,
     *     discount:float,
     *     total:float
     * }
     */
    private function finalizeSummary(array $items, int $count, float $subtotal): array
    {
        // Resolve the discount from the same session store we mutate elsewhere to maintain consistent state.
        $discountRaw = $this->session->get('cart_discount', 0.0);
        $discount = is_numeric($discountRaw) ? max(0.0, (float) $discountRaw) : 0.0;
        $taxRateRaw = config('shared.tax.default_rate', 0.21);
        $taxRate = is_numeric($taxRateRaw) ? max(0.0, (float) $taxRateRaw) : 0.21;
        $shippingThresholdRaw = config('shared.shipping.free_threshold', 50.0);
        $shippingThreshold = is_numeric($shippingThresholdRaw) ? max(0.0, (float) $shippingThresholdRaw) : 50.0;
        $shippingCostRaw = config('shared.shipping.flat_rate', 5.99);
        $shippingCost = is_numeric($shippingCostRaw) ? max(0.0, (float) $shippingCostRaw) : 5.99;

        // Ensure downstream totals never exceed the positive subtotal after accounting for discounts.
        $effectiveSubtotal = max(0.0, $subtotal);
        $discount = min($discount, $effectiveSubtotal);
        $netSubtotal = $effectiveSubtotal - $discount;

        // Calculate tax on the discounted subtotal so promotional adjustments reduce tax exposure accurately.
        $tax = $netSubtotal * $taxRate;

        // Free shipping applies once the discounted subtotal reaches the configured threshold.
        $shipping = ($count === 0 || $netSubtotal <= 0.0)
            ? 0.0
            : ($netSubtotal >= $shippingThreshold ? 0.0 : $shippingCost);

        $total = $netSubtotal + $tax + $shipping;

        return [
            'items'    => $items,
            'count'    => $count,
            'subtotal' => round($subtotal, 2),
            'tax'      => round($tax, 2),
            'shipping' => round($shipping, 2),
            'discount' => round($discount, 2),
            'total'    => round($total, 2),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeAttributes(mixed $attributes): array
    {
        if ($attributes instanceof Collection) {
            return $attributes->toArray();
        }

        if ($attributes instanceof Arrayable) {
            return $attributes->toArray();
        }

        if (is_string($attributes)) {
            // Allow legacy JSON blobs to hydrate into associative arrays for downstream consumers.
            $decoded = json_decode($attributes, true);

            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return is_array($attributes) ? $attributes : [];
    }

    /**
     * Strip duplicated variant identifiers from the attribute bag once elevated to the dedicated column.
     *
     * @param  array<string, mixed> $attributes
     * @return array<string, mixed>
     */
    private function purgeVariantIdentifier(array $attributes): array
    {
        unset($attributes['variant_id']);

        return $attributes;
    }

    /**
     * @return array{
     *     items: array<int, array{
     *         id:int|null,
     *         product_id:int|null,
     *         variant_id:int|null,
     *         name:string,
     *         price:float,
     *         quantity:int,
     *         total:float,
     *         image:?string,
     *         attributes: array<string, mixed>
     *     }>,
     *     count:int,
     *     subtotal:float,
     *     tax:float,
     *     shipping:float,
     *     discount:float,
     *     total:float
     * }
     */
    private function emptySummary(): array
    {
        return $this->finalizeSummary([], 0, 0.0);
    }

    private function summaryCacheKey(?int $userId, string $sessionId): string
    {
        return 'cart.summary.' . md5($sessionId . '|' . ($userId ?? 'guest'));
    }

    private function extractNullableInt(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_string($value) && is_numeric($value)) {
            return (int) $value;
        }

        return null;
    }

    private function extractPositiveInt(mixed $value): int
    {
        $minimum = max(0, (int) config('shared.cart.min_quantity', 1));
        $maximumCandidate = (int) config('shared.cart.max_quantity', max($minimum, 50));
        $maximum = $maximumCandidate < $minimum ? $minimum : $maximumCandidate;

        if (! is_numeric($value)) {
            // Fallback to the minimum so callers never see zero-quantity line items.
            return $minimum;
        }

        $quantity = (int) $value;

        if ($quantity < $minimum) {
            return $minimum;
        }

        if ($quantity > $maximum) {
            return $maximum;
        }

        return $quantity;
    }

    private function extractFloat(mixed $value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        return 0.0;
    }
}
