<?php

declare(strict_types=1);

namespace App\Actions;

use App\Mail\OrderPlaced;
use App\Models\AdminUser;
use App\Models\Country;
use App\Models\Order;
use App\Services\Cart\CartLifecycleService;
use App\Services\Pricing\PriceCalculator;
use Darryldecode\Cart\Facades\CartFacade;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Number;
use Throwable;

/**
 * CreateOrder
 *
 * Action class for CreateOrder single-purpose operations with validation, error handling, and result reporting.
 */
final class CreateOrder
{
    public function __construct(
        private readonly DiscountEngine $discountEngine,
        private readonly PaymentService $paymentService,
        private readonly TaxCalculator $taxCalculator,
    ) {}

    public function handle(): Order
    {
        /** @var array<string, mixed>|null $checkout */
        $checkout = session()->get('checkout');
        if (! is_array($checkout)) {
            throw new RuntimeException('Checkout session payload is missing.');
        }

        return DB::transaction(function () use ($checkout, $sessionId, $customer) {
            /** @var OrderAddress $shippingAddress */
            $shippingAddress = OrderAddress::query()->create([
                'customer_id'         => data_get($checkout, 'shipping_address.user_id'),
                'last_name'           => data_get($checkout, 'shipping_address.last_name'),
                'first_name'          => data_get($checkout, 'shipping_address.first_name'),
                'street_address'      => data_get($checkout, 'shipping_address.street_address'),
                'street_address_plus' => data_get($checkout, 'shipping_address.street_address_plus'),
                'city'                => data_get($checkout, 'shipping_address.city'),
                'postal_code'         => data_get($checkout, 'shipping_address.postal_code'),
                'phone'               => data_get($checkout, 'shipping_address.phone_number'),
                // @phpstan-ignore-next-line
                'country_name' => Country::query()->find(data_get($checkout, 'shipping_address.country_id'))->name,
            ]);
            /** @var OrderAddress $billingAddress */
            $billingAddress = ! data_get($checkout, 'same_as_shipping') ? OrderAddress::query()->create([
                'customer_id'         => data_get($checkout, 'billing_address.user_id'),
                'last_name'           => data_get($checkout, 'billing_address.last_name'),
                'first_name'          => data_get($checkout, 'billing_address.first_name'),
                'street_address'      => data_get($checkout, 'billing_address.street_address'),
                'street_address_plus' => data_get($checkout, 'billing_address.street_address_plus'),
                'city'                => data_get($checkout, 'billing_address.city'),
                'postal_code'         => data_get($checkout, 'billing_address.postal_code'),
                'phone'               => data_get($checkout, 'billing_address.phone_number'),
                // @phpstan-ignore-next-line
                'country_name' => Country::query()->find(data_get($checkout, 'billing_address.country_id'))->name,
            ]) : $shippingAddress;
            // Totals
            // @phpstan-ignore-next-line
            $subtotal = Number::parseFloat(CartFacade::session($sessionId)->getSubTotal());
            $shippingTotal = Number::parseFloat(data_get($checkout, 'shipping_option.0.price', 0));
            $couponCode = strtoupper((string) data_get($checkout, 'coupon.code'));
            // Validate coupon limits if provided
            $codeRow = null;
            if ($couponCode) {
                $codeRow = DB::table('discount_codes')->whereRaw('UPPER(code) = ?', [$couponCode])->first();
                if ($codeRow) {
                    $now = now();
                    if ($codeRow->expires_at && $now->greaterThan($codeRow->expires_at) || $codeRow->max_uses !== null && $codeRow->usage_count >= $codeRow->max_uses) {
                        $codeRow = null;
                        // invalidate
                    }
                }
            }
            $engine = app(\App\Services\Discounts\DiscountEngine::class);
            $result = $engine->evaluate(['currency_code' => current_currency(), 'channel_id' => null, 'user_id' => optional($customer)->id, 'now' => now(), 'code' => $codeRow ? $couponCode : null, 'cart' => ['subtotal' => $subtotal, 'items' => []]]);
            $discountTotal = Number::parseFloat(data_get($result, 'discount_total_amount', 0));
            $shippingDiscount = Number::parseFloat(data_get($result, 'shipping.discount_amount', 0));
            $shippingAmount = max(0.0, $shippingTotal - $shippingDiscount);
            $breakdown = app(PriceCalculator::class)->breakdown($subtotal, $discountTotal, $shippingAmount);
            $grandTotal = $breakdown->total;
            /** @var Order $order */
            $order = Order::query()->create(['number' => generate_number(), 'customer_id' => $customer->id, 'currency_code' => current_currency(), 'shipping_address_id' => $shippingAddress->id, 'billing_address_id' => $billingAddress->id, 'shipping_option_id' => data_get($checkout, 'shipping_option')[0]['id'], 'payment_method_id' => data_get($checkout, 'payment')[0]['id'], 'payment_method' => (string) data_get($checkout, 'payment')[0]['name'], 'subtotal_amount' => round($breakdown->subtotal, 2), 'discount_total_amount' => round($breakdown->discount, 2), 'tax_total_amount' => round($breakdown->tax, 2), 'shipping_total_amount' => round($breakdown->shipping, 2), 'grand_total_amount' => $grandTotal]);
            // Items
            foreach (CartFacade::session($sessionId)->getContent() as $item) {
                OrderItem::query()->create(['order_id' => $order->id, 'quantity' => $item->quantity, 'unit_price_amount' => $item->price, 'name' => $item->name, 'sku' => $item->associatedModel->sku, 'product_id' => $item->associatedModel->id, 'product_type' => $item->associatedModel->getMorphClass()]);
            }
            // Persist redemptions
            foreach ((array) data_get($result, 'applied', []) as $applied) {
                $discountId = (int) ($applied['id'] ?? 0);
                if ($discountId <= 0) {
                    continue;
                }
                // Enforce per-customer limit
                $perCustomer = DB::table('discounts')->where('id', $discountId)->value('per_customer_limit');
                if ($perCustomer) {
                    $used = DB::table('discount_redemptions')->where('discount_id', $discountId)->where('user_id', $customer->id)->count();
                    if ($used >= $perCustomer) {
                        continue;
                        // skip redemption
                    }
                }
                $codeId = null;
                if ($codeRow && (int) $codeRow->discount_id === $discountId) {
                    $codeId = (int) $codeRow->id;
                    // increment usage_count
                    DB::table('discount_codes')->where('id', $codeId)->increment('usage_count');
                }
                DB::table('discount_redemptions')->insert(['discount_id' => $discountId, 'code_id' => $codeId, 'order_id' => $order->id, 'user_id' => $customer->id, 'amount_saved' => round((float) ($applied['amount'] ?? 0), 2), 'currency_code' => current_currency(), 'redeemed_at' => now(), 'created_at' => now(), 'updated_at' => now()]);
            }
            // Process payment (stub)
            try {
                $payment = app(\App\Services\Payments\PaymentService::class)->process($order, (array) data_get($checkout, 'payment.0', []));
                $order->payment_status = (string) ($payment['status'] ?? 'pending');
                $existing = (array) json_decode((string) $order->transactions ?? '[]', true);
                $existing[] = (array) ($payment['transaction'] ?? []);
                $order->transactions = $existing;
                $order->save();
            } catch (Throwable $e) {
                // ignore payment errors in stub
            }
            // Clear cart
            app(CartLifecycleService::class)->clearAfterCheckout(
                $customer?->id,
                $sessionId,
                $order->payment_status ?? null
            );
            // Queue order confirmation email with user's preferred locale
            try {
                $mailable = new OrderPlaced($order);
                if (! empty($customer->preferred_locale)) {
                    $mailable->locale($customer->preferred_locale);
                }
                Mail::to($customer->email)->queue($mailable);
            } catch (Throwable $e) {
                // swallow mail errors to not block checkout
            }

            return $order;
        });
    }

    /**
     * @param  array<string, mixed>  $checkout
     * @return array<string, mixed>
     */
    private function buildAddressPayload(array $checkout, string $key): array
    {
        $raw = data_get($checkout, $key, []);
        if (! is_array($raw)) {
            throw new RuntimeException(sprintf('Checkout %s data is missing.', $key));
        }

        $countryId = $this->nullableInt($raw['country_id'] ?? null);

        return [
            'user_id' => $this->nullableInt($raw['user_id'] ?? null),
            'last_name' => $this->stringOrNull($raw['last_name'] ?? null),
            'first_name' => $this->stringOrNull($raw['first_name'] ?? null),
            'street_address' => $this->stringOrNull($raw['street_address'] ?? null),
            'street_address_plus' => $this->stringOrNull($raw['street_address_plus'] ?? null),
            'city' => $this->stringOrNull($raw['city'] ?? null),
            'postal_code' => $this->stringOrNull($raw['postal_code'] ?? null),
            'phone' => $this->stringOrNull($raw['phone_number'] ?? null),
            'country_id' => $countryId,
            'country_name' => $this->resolveCountryName($countryId),
        ];
    }

    /**
     * @param  array<string, mixed>  $checkout
     */
    private function shouldReuseShippingAddress(array $checkout): bool
    {
        $value = $checkout['same_as_shipping'] ?? false;

        return filter_var($value, FILTER_VALIDATE_BOOLEAN) || $value === 1 || $value === '1';
    }

    private function normalizeCouponCode(mixed $code): ?string
    {
        if (! is_string($code)) {
            return null;
        }

        $normalized = strtoupper(trim($code));

        return $normalized !== '' ? $normalized : null;
    }

    private function resolveCountryName(?int $countryId): ?string
    {
        if ($countryId === null) {
            return null;
        }

        return Country::query()->find($countryId)?->name;
    }

    /**
     * @param  array<string, mixed>  $checkout
     * @return array{id: int, name: string, price: float}
     */
    private function resolveShippingOption(array $checkout): array
    {
        $raw = data_get($checkout, 'shipping_option.0', []);
        if (! is_array($raw)) {
            throw new RuntimeException('Checkout shipping option is missing.');
        }

        $id = $this->nullableInt($raw['id'] ?? null);
        if ($id === null) {
            throw new RuntimeException('Invalid shipping option selected.');
        }

        return [
            'id' => $id,
            'name' => $this->stringOrNull($raw['name'] ?? null) ?? 'Shipping',
            'price' => $this->nullableFloat($raw['price'] ?? null) ?? 0.0,
        ];
    }

    /**
     * @param  array<string, mixed>  $checkout
     * @return array{id: int|null, name: string, reference: string|null, raw: array<string, mixed>}
     */
    private function resolvePaymentMethod(array $checkout): array
    {
        $raw = data_get($checkout, 'payment.0', []);
        if (! is_array($raw)) {
            throw new RuntimeException('Checkout payment information is missing.');
        }

        $id = $this->nullableInt($raw['id'] ?? null);
        $name = $this->stringOrNull($raw['name'] ?? null) ?? 'manual';
        $reference = array_key_exists('reference', $raw) ? $this->stringOrNull($raw['reference']) : null;

        $normalizedRaw = [];
        foreach ($raw as $key => $value) {
            $normalizedRaw[(string) $key] = $value;
        }

        return [
            'id' => $id,
            'name' => $name,
            'reference' => $reference,
            'raw' => $normalizedRaw,
        ];
    }

    /**
     * @param  iterable<int, mixed>  $items
     * @return array<int, array{product_id: int|null, variant_id: int|null, quantity: int, unit_price: float}>
     */
    private function mapCartItems(iterable $items): array
    {
        $mapped = [];
        foreach ($items as $item) {
            if (! $item instanceof CartItem) {
                continue;
            }

            $associated = is_object($item->associatedModel) ? $item->associatedModel : null;
            $quantity = $this->nullableInt($item->quantity) ?? 0;
            $unitPrice = $this->nullableFloat($item->price) ?? 0.0;

            $mapped[] = [
                'product_id' => $this->extractIntProperty($associated, 'id'),
                'variant_id' => $this->extractIntProperty($associated, 'variant_id'),
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
            ];
        }

        return $mapped;
    }

    /**
     * @param  iterable<int, mixed>  $items
     */
    private function persistItems(Order $order, iterable $items): void
    {
        foreach ($items as $item) {
            if (! $item instanceof CartItem) {
                continue;
            }

            $associated = is_object($item->associatedModel) ? $item->associatedModel : null;
            $unitPrice = $this->nullableFloat($item->price) ?? 0.0;
            $quantity = $this->nullableInt($item->quantity) ?? 0;

            OrderItem::query()->create([
                'order_id' => $order->getKey(),
                'product_id' => $this->extractIntProperty($associated, 'id'),
                'product_variant_id' => $this->extractIntProperty($associated, 'variant_id'),
                'name' => (string) $item->name,
                'sku' => $this->extractStringProperty($associated, 'sku'),
                'quantity' => $quantity,
                'unit_price' => round($unitPrice, 2),
                'price' => round($unitPrice, 2),
                'total' => round($unitPrice * $quantity, 2),
            ]);
        }
    }

    /**
     * @param  array<int, array{id?: int, amount?: float|int|string|null}>  $appliedDiscounts
     * @param  array{id: int, discount_id: int}|null  $codeRow
     */
    private function recordRedemptions(Order $order, int $customerId, array $appliedDiscounts, ?array $codeRow): void
    {
        foreach ($appliedDiscounts as $discount) {
            if (! isset($discount['id']) || ! is_numeric($discount['id'])) {
                continue;
            }

            $discountId = (int) $discount['id'];
            if ($discountId <= 0) {
                continue;
            }

            $perCustomer = DB::table('discounts')->where('id', $discountId)->value('per_customer_limit');
            if ($perCustomer !== null && is_numeric($perCustomer)) {
                $perCustomerLimit = (int) $perCustomer;
                $used = DB::table('discount_redemptions')
                    ->where('discount_id', $discountId)
                    ->where('user_id', $customerId)
                    ->count();

                if ($used >= $perCustomerLimit) {
                    continue;
                }
            }

            $codeId = null;
            if ($codeRow && $codeRow['discount_id'] === $discountId) {
                $codeId = $codeRow['id'];
                DB::table('discount_codes')->where('id', $codeId)->increment('usage_count');
            }

            $amount = isset($discount['amount']) && is_numeric($discount['amount'])
                ? (float) $discount['amount']
                : 0.0;

            DB::table('discount_redemptions')->insert([
                'discount_id' => $discountId,
                'code_id' => $codeId,
                'order_id' => $order->getKey(),
                'user_id' => $customerId,
                'amount_saved' => round($amount, 2),
                'currency_code' => current_currency(),
                'redeemed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * @return array{id: int, discount_id: int}|null
     */
    private function findValidDiscountCode(string $couponCode): ?array
    {
        $row = DB::table('discount_codes')
            ->select(['id', 'discount_id', 'expires_at', 'max_uses', 'usage_count'])
            ->whereRaw('UPPER(code) = ?', [$couponCode])
            ->first();

        if (! $row instanceof \stdClass || ! isset($row->id, $row->discount_id)) {
            return null;
        }

        $expiresAtValue = $row->expires_at ?? null;
        $expiresAt = null;
        if ($expiresAtValue instanceof CarbonInterface) {
            $expiresAt = $expiresAtValue;
        } elseif (is_string($expiresAtValue) && $expiresAtValue !== '') {
            $expiresAt = Carbon::parse($expiresAtValue);
        }

        if ($expiresAt instanceof CarbonInterface && now()->greaterThan($expiresAt)) {
            return null;
        }

        if ($row->max_uses !== null && is_numeric($row->usage_count) && is_numeric($row->max_uses) && (int) $row->usage_count >= (int) $row->max_uses) {
            return null;
        }

        if (! is_numeric($row->id) || ! is_numeric($row->discount_id)) {
            return null;
        }

        return ['id' => (int) $row->id, 'discount_id' => (int) $row->discount_id];
    }

    /**
     * @param  array<string, mixed>  $discountResult
     * @return array<int, array{id?: int, amount?: float|int|string|null}>
     */
    private function extractAppliedDiscounts(array $discountResult): array
    {
        $applied = $discountResult['applied'] ?? [];
        if ($applied instanceof Collection) {
            $applied = $applied->all();
        } elseif ($applied instanceof Arrayable) {
            $applied = $applied->toArray();
        } elseif ($applied instanceof \stdClass) {
            $applied = (array) $applied;
        }

        if (! is_iterable($applied)) {
            return [];
        }

        $normalized = [];
        foreach ($applied as $row) {
            if ($row instanceof Collection) {
                $row = $row->all();
            } elseif ($row instanceof Arrayable) {
                $row = $row->toArray();
            } elseif ($row instanceof \stdClass) {
                $row = (array) $row;
            }

            if (is_array($row)) {
                /** @var array{id?: int, amount?: float|int|string|null} $row */
                $normalized[] = $row;
            }
        }

        return $normalized;
    }

    /**
     * @param  array<string, mixed>  $paymentData
     */
    private function processPayment(Order $order, array $paymentData): void
    {
        try {
            $payment = $this->paymentService->process($order, $paymentData);
            $status = $payment['status'] ?? 'pending';
            if (! is_string($status)) {
                $status = is_scalar($status) ? (string) $status : 'pending';
            }
            $order->payment_status = $status;

            $transactions = $order->transactions;
            if (! is_array($transactions)) {
                $transactions = [];
            }

            if (isset($payment['transaction'])) {
                $transaction = $payment['transaction'];
                if ($transaction instanceof Arrayable) {
                    $transactions[] = $transaction->toArray();
                } elseif ($transaction instanceof Collection) {
                    $transactions[] = $transaction->all();
                } else {
                    $transactions[] = (array) $transaction;
                }
            }

            $order->transactions = $transactions;
            $order->save();
        } catch (Throwable) {
            // Ignore payment failures for this stub implementation.
        }
    }

    private function queueConfirmationMail(Order $order, AdminUser|User $customer): void
    {
        try {
            $mailable = new OrderPlaced($order);
            if ($customer instanceof User) {
                $locale = $this->stringOrNull($customer->preferred_locale ?? null);
                if ($locale) {
                    $mailable->locale($locale);
                }
            }

            Mail::to($customer->email)->queue($mailable);
        } catch (Throwable) {
            // Swallow mail exceptions to avoid blocking checkout completion.
        }
    }

    private function generateOrderNumber(): string
    {
        return 'ORD-'.Str::upper(Str::random(10));
    }

    private function nullableInt(mixed $value): ?int
    {
        if (is_numeric($value)) {
            return (int) $value;
        }

        return null;
    }

    private function nullableFloat(mixed $value): ?float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        return null;
    }

    private function stringOrNull(mixed $value): ?string
    {
        if (is_string($value)) {
            $trimmed = trim($value);

            return $trimmed !== '' ? $trimmed : null;
        }

        if (is_numeric($value)) {
            return (string) $value;
        }

        return null;
    }

    private function extractIntProperty(?object $source, string $property): ?int
    {
        if ($source === null || ! isset($source->{$property}) || ! is_numeric($source->{$property})) {
            return null;
        }

        return (int) $source->{$property};
    }

    private function extractStringProperty(?object $source, string $property): ?string
    {
        if ($source === null || ! isset($source->{$property})) {
            return null;
        }

        return $this->stringOrNull($source->{$property});
    }
}
