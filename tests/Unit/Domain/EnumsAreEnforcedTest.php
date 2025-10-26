<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use App\Models\Order;
use Tests\TestCase;
use ValueError;

final class EnumsAreEnforcedTest extends TestCase
{
    public function test_order_status_rejects_invalid_value(): void
    {
        $order = Order::factory()->make();

        $this->expectException(ValueError::class);
        $order->status = 'definitely-not-a-valid-status';
    }

    public function test_payment_status_rejects_invalid_value(): void
    {
        $order = Order::factory()->make();

        $this->expectException(ValueError::class);
        $order->payment_status = 'unknown-payment-state';
    }

    public function test_payment_method_rejects_invalid_value(): void
    {
        $order = Order::factory()->make();

        $this->expectException(ValueError::class);
        $order->payment_method = 'totally-made-up-gateway';
    }
}
