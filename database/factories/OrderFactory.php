<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\OrderPaymentState;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Channel;
use App\Models\Country;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Schema;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * Maintain a simple sequence counter so deterministic placeholder data remains readable across tests.
     */
    private static int $sequence = 1;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $sequence = $this->nextSequence();

        $subtotal = round(random_int(1000, 100000) / 100, 2);
        $taxAmount = round($subtotal * 0.21, 2);  // 21% VAT
        $shippingAmount = round(random_int(0, 2000) / 100, 2);
        $discountAmount = round($subtotal * (random_int(0, 10) / 100), 2);  // Max 10% discount
        $total = round($subtotal + $taxAmount + $shippingAmount - $discountAmount, 2);

        $scopedStatuses = [
            OrderStatus::PENDING->value,
            OrderStatus::PROCESSING->value,
            OrderStatus::SHIPPED->value,
            OrderStatus::DELIVERED->value,
        ];

        // Cache the enum values locally so deterministic indexing stays readable in the attribute payloads.
        $paymentStatuses = collect(PaymentStatus::cases())
            ->map(fn (PaymentStatus $status): string => $status->value)
            ->values()
            ->all();

        $paymentMethods = ['credit_card', 'bank_transfer', 'paypal', 'cash_on_delivery'];
        $billingCities = ['Vilnius', 'Kaunas', 'Klaipėda'];
        $shippingCities = ['Vilnius', 'Kaunas', 'Šiauliai'];
        $now = now();
        $shippedAt = $sequence % 4 === 0 ? null : $now->clone()->subDays(max(1, $sequence % 30));
        $deliveredAt = $shippedAt ? $shippedAt->clone()->addDays(max(1, $sequence % 5)) : null;

        $attributes = [
            // Compose a predictable numeric suffix using the internal counter to avoid depending on removed Faker helpers.
            'number'     => 'ORD-' . strtoupper(sprintf('%06d', $sequence)),
            'user_id'    => null,
            'channel_id' => null,
            'country_id' => null,
            'partner_id' => null,
            // Cycle through lifecycle statuses deterministically so fixtures cover each supported state without random helpers.
            'status' => $scopedStatuses[($sequence - 1) % count($scopedStatuses)],
            // Keep payment statuses aligned with the enum so factories exercise every supported lifecycle case.
            'payment_status' => $paymentStatuses[($sequence - 1) % count($paymentStatuses)],
            'payment_state'  => OrderPaymentState::CREATED->value,
            'payment_method' => $paymentMethods[($sequence - 1) % count($paymentMethods)],
            // Mirror the previous optional reference logic without Faker by skipping every fifth record.
            'payment_reference' => $sequence % 5 === 0 ? null : sprintf('PAY-%08d', $sequence),
            'subtotal'          => $subtotal,
            'tax_amount'        => $taxAmount,
            'shipping_amount'   => $shippingAmount,
            'discount_amount'   => $discountAmount,
            'total'             => $total,
            'currency'          => 'EUR',
            'billing_address'   => [
                'name'        => 'Customer ' . $sequence,
                'email'       => sprintf('customer%02d@example.test', $sequence),
                'phone'       => sprintf('+370600%04d', $sequence % 10000),
                'address'     => 'Example Street ' . $sequence,
                'city'        => $billingCities[$sequence % count($billingCities)],
                'postal_code' => sprintf('%05d', ($sequence * 37) % 100000),
                'country'     => 'Lithuania',
            ],
            'shipping_address' => [
                'name'        => 'Recipient ' . $sequence,
                'email'       => sprintf('recipient%02d@example.test', $sequence),
                'phone'       => sprintf('+370612%04d', $sequence % 10000),
                'address'     => 'Warehouse Avenue ' . $sequence,
                'city'        => $shippingCities[$sequence % count($shippingCities)],
                'postal_code' => sprintf('%05d', ($sequence * 53) % 100000),
                'country'     => 'Lithuania',
            ],
            'notes'        => $sequence % 3 === 0 ? null : 'Order note #' . $sequence,
            'shipped_at'   => $shippedAt,
            'delivered_at' => $deliveredAt,
        ];

        return $this->ensureOptionalColumns($attributes);
    }

    /**
     * Provide deterministic incremental values without relying on Faker's unique() helper.
     */
    private function nextSequence(): int
    {
        return self::$sequence++;
    }

    /**
     * Indicate that the order is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => $this->ensureOptionalColumns([
            'status'         => OrderStatus::PENDING->value,
            'payment_status' => PaymentStatus::PENDING->value,
            'payment_state'  => OrderPaymentState::CREATED->value,
            'shipped_at'     => null,
            'delivered_at'   => null,
        ]));
    }

    /**
     * Indicate that the order is processing.
     */
    public function processing(): static
    {
        return $this->state(fn (array $attributes) => $this->ensureOptionalColumns([
            'status'         => OrderStatus::PROCESSING->value,
            'payment_status' => PaymentStatus::PAID->value,
            'payment_state'  => OrderPaymentState::PAID->value,
            'shipped_at'     => null,
            'delivered_at'   => null,
        ]));
    }

    /**
     * Indicate that the order is confirmed.
     */
    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => $this->ensureOptionalColumns([
            'status'         => OrderStatus::PROCESSING->value,
            'payment_status' => PaymentStatus::PAID->value,
            'payment_state'  => OrderPaymentState::PAID->value,
            'shipped_at'     => null,
            'delivered_at'   => null,
        ]));
    }

    /**
     * Indicate that the order is shipped.
     */
    public function shipped(): static
    {
        // Apply a short delivery pipeline timeline that mirrors the production happy-path without depending on Faker.
        return $this->state(fn (array $attributes) => $this->ensureOptionalColumns([
            'status'         => OrderStatus::SHIPPED->value,
            'payment_status' => PaymentStatus::PAID->value,
            'payment_state'  => OrderPaymentState::PAID->value,
            'shipped_at'     => now()->subDays(3),
            'delivered_at'   => null,
        ]));
    }

    /**
     * Indicate that the order is delivered.
     */
    public function delivered(): static
    {
        // Anchor delivery dates relative to now() so downstream assertions inherit consistent intervals in every run.
        return $this->state(fn (array $attributes) => $this->ensureOptionalColumns([
            'status'         => OrderStatus::DELIVERED->value,
            'payment_status' => PaymentStatus::PAID->value,
            'payment_state'  => OrderPaymentState::PAID->value,
            'shipped_at'     => now()->subDays(5),
            'delivered_at'   => now()->subDays(1),
        ]));
    }

    /**
     * Indicate that the order is completed.
     */
    public function completed(): static
    {
        // Completed orders extend the delivery window slightly to capture post-delivery workflows in analytics tests.
        return $this->state(fn (array $attributes) => $this->ensureOptionalColumns([
            // Map legacy "completed" into the delivered enum case to stay aligned with the new status set.
            'status'         => OrderStatus::DELIVERED->value,
            'payment_status' => PaymentStatus::PAID->value,
            'payment_state'  => OrderPaymentState::PAID->value,
            'shipped_at'     => now()->subDays(10),
            'delivered_at'   => now()->subDays(4),
        ]));
    }

    /**
     * Indicate that the order is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => $this->ensureOptionalColumns([
            'status'         => OrderStatus::CANCELLED->value,
            'payment_status' => PaymentStatus::PENDING->value,
            'payment_state'  => OrderPaymentState::CREATED->value,
            'shipped_at'     => null,
            'delivered_at'   => null,
        ]));
    }

    /**
     * Indicate that the order is paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => $this->ensureOptionalColumns([
            'payment_status' => PaymentStatus::PAID->value,
            'payment_state'  => OrderPaymentState::PAID->value,
        ]));
    }

    /**
     * Indicate that the order has failed payment.
     */
    public function paymentFailed(): static
    {
        return $this->state(fn (array $attributes) => $this->ensureOptionalColumns([
            'payment_status' => PaymentStatus::FAILED->value,
            'status'         => OrderStatus::PENDING->value,
            'payment_state'  => OrderPaymentState::CREATED->value,
        ]));
    }

    /**
     * Indicate that the order has been refunded.
     */
    public function refunded(): static
    {
        return $this->state(fn (array $attributes) => $this->ensureOptionalColumns([
            'payment_status' => PaymentStatus::REFUNDED->value,
            'payment_state'  => OrderPaymentState::REFUNDED->value,
        ]));
    }

    public function configure(): static
    {
        return $this
            ->afterMaking(function (Order $order): void {
                if ($order->user_id === null && ! $order->relationLoaded('user')) {
                    $order->setRelation('user', User::factory()->make());
                }

                if ($order->channel_id === null && ! $order->relationLoaded('channel')) {
                    $order->setRelation('channel', Channel::factory()->make());
                }

                if ($order->country_id === null && ! $order->relationLoaded('country')) {
                    $order->setRelation('country', Country::factory()->make());
                }
            })
            ->afterCreating(function (Order $order): void {
                $dirty = false;
                $userTable = (new User)->getTable();
                $channelTable = (new Channel)->getTable();
                $countryTable = (new Country)->getTable();

                if ($order->user_id === null && Schema::hasTable($userTable)) {
                    $order->user()->associate(User::factory()->create());
                    $dirty = true;
                }

                if ($order->channel_id === null && Schema::hasTable($channelTable)) {
                    $order->channel()->associate(Channel::factory()->create());
                    $dirty = true;
                }

                if ($order->country_id === null && Schema::hasTable($countryTable)) {
                    $order->country()->associate(Country::factory()->create());
                    $dirty = true;
                }

                if ($dirty) {
                    $order->save();
                }
            });
    }

    /**
     * Indicate that the order has high value.
     */
    public function highValue(): static
    {
        // Supply a deterministic high-value basket so average order value assertions can reuse the same fixtures every run.
        return $this->state(fn (array $attributes) => [
            'subtotal'        => 1250.00,
            'tax_amount'      => 262.50,
            'shipping_amount' => 19.99,
            'discount_amount' => 75.00,
            'total'           => 1457.49,
        ]);
    }

    /**
     * Indicate that the order has low value.
     */
    public function lowValue(): static
    {
        // Mirror the high value helper with a predictable small cart profile for regression coverage.
        return $this->state(fn (array $attributes) => [
            'subtotal'        => 25.00,
            'tax_amount'      => 5.25,
            'shipping_amount' => 2.99,
            'discount_amount' => 0,
            'total'           => 33.24,
        ]);
    }

    /**
     * Indicate that the order is recent.
     */
    public function recent(): static
    {
        // Pin the created_at timestamp close to now() so “recent orders” dashboards receive deterministic fixtures.
        return $this->state(fn (array $attributes) => [
            'created_at' => now()->subDays(2),
        ]);
    }

    /**
     * Indicate that the order is old.
     */
    public function old(): static
    {
        // Provide a long-tail history timestamp for archives without invoking Faker date helpers.
        return $this->state(fn (array $attributes) => [
            'created_at' => now()->subDays(90),
        ]);
    }

    /**
     * Remove optional payment attributes when the backing column is missing.
     *
     * @param  array<string, mixed> $attributes
     * @return array<string, mixed>
     */
    private function ensureOptionalColumns(array $attributes): array
    {
        if (! $this->ordersTableHasColumn('payment_status')) {
            unset($attributes['payment_status']);
        }

        if (! $this->ordersTableHasColumn('payment_state')) {
            unset($attributes['payment_state']);
        }

        return $attributes;
    }

    private function ordersTableHasColumn(string $column): bool
    {
        $table = (new Order)->getTable();

        if (! Schema::hasTable($table)) {
            return false;
        }

        return Schema::hasColumn($table, $column);
    }
}
