<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Payments;

use App\Enums\OrderPaymentState;
use App\Jobs\SendOrderFulfillmentEmail;
use App\Models\Order;
use App\Services\Payments\Webhooks\PaymentWebhookService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use ReflectionClass;
use Tests\TestCase;

final class PaymentWebhookServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_fulfilled_webhook_marks_order_as_delivered(): void
    {
        // Fake the job bus to capture fulfillment notifications without executing them.
        Bus::fake();

        // Create a processing order that mirrors the state prior to a fulfillment webhook.
        $order = Order::factory()->create([
            'status'          => 'processing',
            'payment_status'  => null,
            'delivered_at'    => null,
        ]);

        $service = new PaymentWebhookService();

        // Call the private applyStateTransition helper via reflection so we can
        // verify the side effects that occur when a provider reports fulfillment.
        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('applyStateTransition');
        $method->setAccessible(true);
        $method->invoke($service, $order, OrderPaymentState::FULFILLED);

        $order->refresh();

        // Ensure the order lifecycle advanced to delivered with a paid status and timestamp.
        $this->assertSame('delivered', (string) $order->status);
        $this->assertSame('paid', method_exists($order->payment_status, 'value') ? $order->payment_status->value : (string) $order->payment_status);
        $this->assertNotNull($order->delivered_at);

        // Confirm the fulfillment notification was dispatched for asynchronous processing.
        Bus::assertDispatched(SendOrderFulfillmentEmail::class);
    }
}
