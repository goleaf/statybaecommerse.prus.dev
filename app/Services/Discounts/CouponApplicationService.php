<?php

declare(strict_types=1);

namespace App\Services\Discounts;

use App\Events\CouponApplied;
use App\Events\CouponRemoved;
use App\Models\Coupon;
use App\Models\CouponUsage;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Arr;
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

        return Coupon::query()
            ->valid()
            ->where('is_public', true)
            ->orderByDesc('is_auto_apply')
            ->orderBy('expires_at')
            ->get()
            ->filter(fn (Coupon $coupon): bool => $coupon->minimum_amount === null || $subtotal >= (float) $coupon->minimum_amount)
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
        if (! $coupon->canBeUsed($subtotal)) {
            $this->session->forget('checkout.coupon');

            return $this->failure(__('This coupon cannot be applied to your cart.'));
        }

        $userId = Arr::get($context, 'user_id');
        if ($coupon->usage_limit_per_user && $userId) {
            $usageCount = CouponUsage::query()
                ->where('coupon_id', $coupon->getKey())
                ->where('user_id', (int) $userId)
                ->count();

            if ($usageCount >= (int) $coupon->usage_limit_per_user) {
                $this->session->forget('checkout.coupon');

                return $this->failure(__('You have already used this coupon the maximum number of times.'));
            }
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
     * @return array<string, mixed>
     */
    private function failure(string $message): array
    {
        return [
            'success' => false,
            'message' => $message,
        ];
    }
}
