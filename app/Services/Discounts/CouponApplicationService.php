<?php

declare(strict_types=1);

namespace App\Services\Discounts;

use App\Events\CouponApplied;
use App\Events\CouponRemoved;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Number;

final class CouponApplicationService
{
    public function __construct(
        private readonly DiscountEngine $discountEngine,
        private readonly Session $session,
        private readonly Dispatcher $events,
    ) {}

    /**
     * @param  array<string, mixed>             $context
     * @return array<int, array<string, mixed>>
     */
    public function getAvailableCoupons(array $context): array
    {
        $subtotal = (float) Arr::get($context, 'cart.subtotal', 0.0);
        $now = now();
        [$productIds, $categoryIds] = $this->resolveCartProductContext($context);

        return Coupon::query()
            ->valid()
            ->where('is_public', true)
            ->orderByDesc('is_auto_apply')
            ->orderBy('expires_at')
            ->get()
            ->filter(function (Coupon $coupon) use ($context, $subtotal, $productIds, $categoryIds): bool {
                // Reuse the contextual validation so the available list mirrors apply() behaviour.
                return $this->validateCouponForContext($coupon, $context, $subtotal, $productIds, $categoryIds) === null;
            })
            ->map(fn (Coupon $coupon): array => [
                'code'             => $coupon->code,
                'name'             => $coupon->name,
                'description'      => $coupon->description,
                'type'             => $coupon->type,
                'value'            => (float) $coupon->value,
                'minimum_amount'   => $coupon->minimum_amount !== null ? (float) $coupon->minimum_amount : null,
                'maximum_discount' => $coupon->maximum_discount !== null ? (float) $coupon->maximum_discount : null,
                'starts_at'        => optional($coupon->starts_at)->toIso8601String(),
                'expires_at'       => optional($coupon->expires_at)->toIso8601String(),
                'is_active'        => $coupon->isValid(),
                'is_usable_now'    => $coupon->starts_at === null || $coupon->starts_at->lte($now),
                'is_auto_apply'    => $coupon->is_auto_apply,
                'is_first_time'    => $coupon->is_first_time_only,
            ])
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed> $context
     * @return array<string, mixed>
     */
    public function apply(string $rawCode, array $context): array
    {
        $code = mb_strtoupper(trim($rawCode));
        if ($code === '') {
            return $this->failure(__('Please provide a coupon code.'));
        }

        /** @var Coupon|null $coupon */
        $coupon = Coupon::query()->whereRaw('UPPER(code) = ?', [$code])->first();
        if (! $coupon instanceof Coupon) {
            $this->session->forget('checkout.coupon');

            return $this->failure(__('This coupon could not be found.'));
        }

        $subtotal = (float) Arr::get($context, 'cart.subtotal', 0.0);
        [$productIds, $categoryIds] = $this->resolveCartProductContext($context);
        $validationMessage = $this->validateCouponForContext($coupon, $context, $subtotal, $productIds, $categoryIds);
        if ($validationMessage !== null) {
            $this->session->forget('checkout.coupon');

            return $this->failure($validationMessage);
        }

        $discountAmount = $coupon->calculateDiscount($subtotal);
        if ($coupon->maximum_discount !== null) {
            $discountAmount = min($discountAmount, (float) $coupon->maximum_discount);
        }

        if ($discountAmount <= 0) {
            $this->session->forget('checkout.coupon');

            return $this->failure(__('This coupon does not provide a discount for the current cart.'));
        }

        $pricing = (array) $this->discountEngine->evaluate(array_merge($context, ['code' => $code]));
        $existingDiscount = (float) Arr::get($pricing, 'discount_total_amount', 0.0);
        // Persist monetary values with a rounded float to avoid parse errors when Number::parseFloat expects strings.
        $pricing['coupon_discount_amount'] = round($discountAmount, 2);
        $pricing['discount_total_amount'] = round($existingDiscount + $discountAmount, 2);

        $payload = [
            'code'            => $coupon->code,
            'discount_amount' => $pricing['coupon_discount_amount'],
            'pricing'         => $pricing,
        ];

        $this->session->put('checkout.coupon', $payload);
        $this->events->dispatch(new CouponApplied($coupon, $pricing, $context));

        return [
            'success' => true,
            'message' => __('Coupon applied successfully.'),
            'coupon'  => $payload,
        ];
    }

    /**
     * @param  array<string, mixed> $context
     * @return array<string, mixed>
     */
    public function remove(array $context): array
    {
        /** @var array<string, mixed>|null $coupon */
        $coupon = $this->session->pull('checkout.coupon');
        $pricing = (array) $this->discountEngine->evaluate(array_merge($context, ['code' => null]));

        $result = [
            'success' => true,
            'message' => __('Coupon removed.'),
            'coupon'  => $coupon ?? ['code' => null, 'discount_amount' => 0.0],
            'pricing' => $pricing,
        ];

        $this->events->dispatch(new CouponRemoved($result['coupon']['code'] ?? null, $pricing, $context));

        return $result;
    }

    /**
     * Attempt to automatically apply the first eligible coupon flagged for auto application.
     *
     * @param  array<string, mixed> $context
     * @return array<string, mixed>
     */
    public function applyBestAutoCoupon(array $context): array
    {
        $candidate = collect($this->getAvailableCoupons($context))->firstWhere('is_auto_apply', true);

        if (! is_array($candidate)) {
            return $this->failure(__('No auto-apply coupons are currently available.'));
        }

        // Delegate to the core apply logic so validation and pricing adjustments remain consistent.
        return $this->apply((string) $candidate['code'], $context);
    }

    /**
     * @return array<string, mixed>
     */
    private function failure(string $message): array
    {
        return [
            'success' => false,
            'message' => $message,
        ];
    }

    /**
     * Validate the coupon against the current shopping context and return an error message when invalid.
     *
     * @param array<string, mixed> $context
     * @param Collection<int, int> $productIds
     * @param Collection<int, int> $categoryIds
     */
    private function validateCouponForContext(Coupon $coupon, array $context, float $subtotal, Collection $productIds, Collection $categoryIds): ?string
    {
        // Ensure all baseline checks (active flags, time windows, min spend) still pass for the current cart.
        if (! $coupon->canBeUsed($subtotal)) {
            return __('This coupon cannot be applied to your cart.');
        }

        $userId = Arr::get($context, 'user_id');
        if ($this->userHasExceededPersonalLimit($coupon, $userId)) {
            return __('You have already used this coupon the maximum number of times.');
        }

        if (! $this->userIsEligibleForFirstTimeCoupon($coupon, $userId)) {
            return __('This coupon is only available for your first order.');
        }

        $groupIds = collect(Arr::get($context, 'group_ids', []))
            ->filter(static fn ($value): bool => is_numeric($value))
            ->map(static fn ($value): int => (int) $value)
            ->values();

        if ($coupon->customer_group_id !== null && ! $groupIds->contains((int) $coupon->customer_group_id)) {
            return __('This coupon is not available for your customer group.');
        }

        $applicableProducts = $this->normaliseIdCollection($coupon->applicable_products);
        if ($applicableProducts->isNotEmpty() && ($productIds->isEmpty() || $productIds->intersect($applicableProducts)->isEmpty())) {
            return __('This coupon is not valid for the selected products.');
        }

        $applicableCategories = $this->normaliseIdCollection($coupon->applicable_categories);
        if ($applicableCategories->isNotEmpty() && ($categoryIds->isEmpty() || $categoryIds->intersect($applicableCategories)->isEmpty())) {
            return __('This coupon is not valid for the selected categories.');
        }

        return null;
    }

    /**
     * Resolve cart product and category identifiers once so repeated validations remain cheap.
     *
     * @param  array<string, mixed>                                    $context
     * @return array{0: Collection<int, int>, 1: Collection<int, int>}
     */
    private function resolveCartProductContext(array $context): array
    {
        $items = collect(Arr::get($context, 'cart.items', []));
        $productIds = $items
            ->pluck('product_id')
            ->filter(static fn ($value): bool => is_numeric($value))
            ->map(static fn ($value): int => (int) $value)
            ->unique()
            ->values();

        if ($productIds->isEmpty()) {
            return [$productIds, collect()];
        }

        // Preload category assignments for every referenced product to honour category-scoped coupons.
        $categoryIds = Product::query()
            ->whereIn('id', $productIds->all())
            ->with('categories:id')
            ->get()
            ->flatMap(static fn (Product $product): Collection => $product->categories->pluck('id'))
            ->filter(static fn ($value): bool => is_numeric($value))
            ->map(static fn ($value): int => (int) $value)
            ->unique()
            ->values();

        return [$productIds, $categoryIds];
    }

    /**
     * Determine whether the authenticated shopper has exhausted the per-user redemption limit.
     */
    private function userHasExceededPersonalLimit(Coupon $coupon, $userId): bool
    {
        if (! $coupon->usage_limit_per_user) {
            return false;
        }

        if (! $userId) {
            // Without an authenticated shopper we cannot track unique redemptions reliably.
            return true;
        }

        $usageCount = CouponUsage::query()
            ->where('coupon_id', $coupon->getKey())
            ->where('user_id', (int) $userId)
            ->count();

        return $usageCount >= (int) $coupon->usage_limit_per_user;
    }

    /**
     * Validate first-time restrictions against the shopper's historic orders.
     */
    private function userIsEligibleForFirstTimeCoupon(Coupon $coupon, $userId): bool
    {
        if (! $coupon->is_first_time_only) {
            return true;
        }

        if (! $userId) {
            return false;
        }

        // Treat any existing order as a signal the shopper is no longer a first-time customer.
        return ! Order::query()
            ->where('user_id', (int) $userId)
            ->exists();
    }

    /**
     * Normalise mixed-value identifier arrays into a clean integer collection.
     *
     * @param  array<int, mixed>|null $values
     * @return Collection<int, int>
     */
    private function normaliseIdCollection(?array $values): Collection
    {
        return collect($values ?? [])
            ->filter(static fn ($value): bool => is_numeric($value))
            ->map(static fn ($value): int => (int) $value)
            ->unique()
            ->values();
    }
}
