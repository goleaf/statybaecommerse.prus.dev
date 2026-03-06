<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use Tests\TestCase;

final class EnumsAreEnforcedTest extends TestCase
{
    public function test_order_status_rejects_invalid_value(): void
    {
        $order = Order::factory()->make();

        $order->status = 'definitely-not-a-valid-status';

        $this->assertSame(OrderStatus::PENDING->value, $order->getAttributes()['status']);
        $this->assertSame(OrderStatus::PENDING, $order->status);
    }

    public function test_payment_status_rejects_invalid_value(): void
    {
        $order = Order::factory()->make();

        $order->payment_status = 'unknown-payment-state';

        $this->assertSame(PaymentStatus::PENDING->value, $order->getAttributes()['payment_status']);
        $this->assertSame(PaymentStatus::PENDING, $order->payment_status);
    }

    public function test_payment_method_rejects_invalid_value(): void
    {
        $order = Order::factory()->make();

        $order->payment_method = 'totally-made-up-gateway';

        $this->assertNull($order->getAttributes()['payment_method']);
        $this->assertNull($order->payment_method);
    }
}
