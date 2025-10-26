<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Enums\OrderPaymentState;
use App\Enums\PaymentStatus;
use App\Enums\PaymentWebhookEventStatus;
use App\Http\Middleware\TestingLegalResourceStub;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

final class PaymentWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Disable the legal resource testing stub so webhook responses can
        // return JsonResponse instances without type coercion errors.
        $this->withoutMiddleware([
            TestingLegalResourceStub::class,
        ]);
    }

    /**
     * Ensure tampered signatures are rejected with an unauthorized response.
     */
    public function test_invalid_signature_is_rejected(): void
    {
        Config::set('payments.notchpay.webhook.secret', 'test_secret');
        Config::set('payments.notchpay.webhook.signature_header', 'X-Notchpay-Signature');
        Config::set('payments.notchpay.webhook.timestamp_header', 'X-Notchpay-Timestamp');
        Config::set('payments.notchpay.webhook.tolerance', 300);

        $order = Order::factory()->pending()->create([
            'total' => 199.50,
            'currency' => 'EUR',
        ]);

        $payload = [
            'event_id' => 'evt_invalid',
            'order_number' => $order->number,
            'amount' => '199.50',
            'currency' => 'EUR',
            'status' => 'paid',
        ];

        $timestamp = (string) Carbon::now()->timestamp;

        $response = $this->withHeaders([
            'X-Notchpay-Timestamp' => $timestamp,
            'X-Notchpay-Signature' => 'invalid-signature',
        ])->postJson(route('webhooks.notchpay'), $payload);

        $response->assertStatus(401)->assertExactJson(['error' => 'invalid_signature']);
        $this->assertDatabaseCount('payment_webhook_events', 0);
    }

    /**
     * Verify that replayed events do not trigger duplicate state transitions.
     */
    public function test_replay_event_is_ignored_after_initial_processing(): void
    {
        Queue::fake();

        $this->configureNotchPay();

        $order = Order::factory()->pending()->create([
            'total' => 150.00,
            'currency' => 'EUR',
        ]);

        $payload = [
            'event_id' => 'evt_paid',
            'order_number' => $order->number,
            'amount' => '150.00',
            'currency' => 'EUR',
            'status' => 'paid',
        ];

        $timestamp = Carbon::now()->timestamp;
        $signature = $this->signPayload($payload, 'notchpay_secret', $timestamp);

        $firstResponse = $this->withHeaders([
            'X-Notchpay-Timestamp' => (string) $timestamp,
            'X-Notchpay-Signature' => $signature,
        ])->postJson(route('webhooks.notchpay'), $payload);

        $firstResponse->assertOk()->assertJson([
            'status' => PaymentWebhookEventStatus::PROCESSED->value,
        ]);

        $order->refresh();
        $this->assertSame(PaymentStatus::PAID, $order->payment_status);
        $this->assertSame(OrderPaymentState::PAID, $order->payment_state);

        $secondResponse = $this->withHeaders([
            'X-Notchpay-Timestamp' => (string) $timestamp,
            'X-Notchpay-Signature' => $signature,
        ])->postJson(route('webhooks.notchpay'), $payload);

        $secondResponse->assertOk()->assertJson([
            'status' => PaymentWebhookEventStatus::IGNORED->value,
        ]);

        $this->assertDatabaseCount('payment_webhook_events', 1);
        $order->refresh();
        $this->assertSame(OrderPaymentState::PAID, $order->payment_state);
        Queue::assertNothingPushed();
    }

    /**
     * Confirm partial refunds progress the order to the correct lifecycle state.
     */
    public function test_partial_refund_updates_payment_state(): void
    {
        Queue::fake();

        $this->configureNotchPay();

        $order = Order::factory()->pending()->create([
            'total' => 300.00,
            'currency' => 'EUR',
        ]);

        $paidPayload = [
            'event_id' => 'evt_paid_state',
            'order_number' => $order->number,
            'amount' => '300.00',
            'currency' => 'EUR',
            'status' => 'paid',
        ];

        $timestamp = Carbon::now()->timestamp;
        $paidSignature = $this->signPayload($paidPayload, 'notchpay_secret', $timestamp);

        $this->withHeaders([
            'X-Notchpay-Timestamp' => (string) $timestamp,
            'X-Notchpay-Signature' => $paidSignature,
        ])->postJson(route('webhooks.notchpay'), $paidPayload)->assertOk();

        $partialRefundPayload = [
            'event_id' => 'evt_partial_refund',
            'order_number' => $order->number,
            'amount' => '300.00',
            'currency' => 'EUR',
            'status' => 'partially_refunded',
        ];

        $refundTimestamp = Carbon::now()->timestamp;
        $refundSignature = $this->signPayload($partialRefundPayload, 'notchpay_secret', $refundTimestamp);

        $response = $this->withHeaders([
            'X-Notchpay-Timestamp' => (string) $refundTimestamp,
            'X-Notchpay-Signature' => $refundSignature,
        ])->postJson(route('webhooks.notchpay'), $partialRefundPayload);

        $response->assertOk()->assertJson([
            'status' => PaymentWebhookEventStatus::PROCESSED->value,
        ]);

        $order->refresh();
        $this->assertSame(OrderPaymentState::PARTIALLY_REFUNDED, $order->payment_state);
        $this->assertSame(PaymentStatus::PARTIALLY_REFUNDED, $order->payment_status);
        $this->assertDatabaseCount('payment_webhook_events', 2);
        Queue::assertNothingPushed();
    }

    /**
     * Configure consistent headers and secrets for NotchPay webhook tests.
     */
    private function configureNotchPay(): void
    {
        Config::set('payments.notchpay.webhook.secret', 'notchpay_secret');
        Config::set('payments.notchpay.webhook.signature_header', 'X-Notchpay-Signature');
        Config::set('payments.notchpay.webhook.timestamp_header', 'X-Notchpay-Timestamp');
        Config::set('payments.notchpay.webhook.tolerance', 300);
    }

    /**
     * Generate a HMAC signature matching the webhook verification strategy.
     *
     * @param array<string, mixed> $payload
     */
    private function signPayload(array $payload, string $secret, int $timestamp): string
    {
        $body = json_encode($payload, JSON_THROW_ON_ERROR);

        return hash_hmac('sha256', $timestamp . '.' . $body, $secret);
    }
}
