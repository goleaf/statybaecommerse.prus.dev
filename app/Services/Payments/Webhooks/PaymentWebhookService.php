<?php

declare(strict_types=1);

namespace App\Services\Payments\Webhooks;

use App\Enums\OrderPaymentState;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\PaymentWebhookEventStatus;
use App\Jobs\SendOrderFulfillmentEmail;
use App\Models\Order;
use App\Models\PaymentWebhookEvent;
use App\Services\Payments\Exceptions\AmountMismatchException;
use App\Services\Payments\Exceptions\IllegalStateTransitionException;
use App\Services\Payments\Exceptions\InvalidProviderConfigurationException;
use App\Services\Payments\Exceptions\InvalidSignatureException;
use App\Services\Payments\Exceptions\MalformedWebhookPayloadException;
use App\Services\Payments\Exceptions\OrderNotFoundException;
use App\Services\Payments\Exceptions\StaleWebhookException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * PaymentWebhookService encapsulates the verification and state transition
 * logic applied to incoming payment provider webhooks.
 */
final class PaymentWebhookService
{
    /**
     * Handle a webhook callback for the given provider and return the response
     * payload plus HTTP status code that should be returned to the provider.
     */
    public function handle(string $provider, Request $request): PaymentWebhookResult
    {
        $config = $this->resolveProviderConfiguration($provider);
        $rawPayload = $request->getContent();
        $timestamp = $this->extractHeader($request, $config['timestamp_header'] ?? 'X-Webhook-Timestamp');
        $signature = $this->extractHeader($request, $config['signature_header'] ?? 'X-Webhook-Signature');
        $tolerance = (int) ($config['tolerance'] ?? 300);
        $secret = (string) ($config['secret'] ?? '');

        $this->guardAgainstStaleTimestamp($timestamp, $tolerance);
        $this->guardAgainstInvalidSignature($signature, $rawPayload, $timestamp, $secret);

        $payload = $this->decodePayload($rawPayload);
        $eventId = $this->requireString($payload, 'event_id');
        $orderNumber = $this->requireString($payload, 'order_number');
        $status = $this->requireString($payload, 'status');

        $existingEvent = PaymentWebhookEvent::query()
            ->where('provider', $provider)
            ->where('event_id', $eventId)
            ->first();

        if ($existingEvent instanceof PaymentWebhookEvent && $existingEvent->status === PaymentWebhookEventStatus::PROCESSED) {
            // Preserve the original processed state while acknowledging the replay.
            return new PaymentWebhookResult([
                'status' => PaymentWebhookEventStatus::IGNORED->value,
                'message' => 'Duplicate event ignored.',
            ], 200);
        }

        $event = $existingEvent ?? new PaymentWebhookEvent([
            'provider' => $provider,
            'event_id' => $eventId,
        ]);
        $event->payload = $payload;
        $event->status = PaymentWebhookEventStatus::RECEIVED;
        $event->processed_at = null;

        try {
            $order = $this->resolveOrder($orderNumber);
            $event->order()->associate($order);
            $event->save();

            $this->assertAmountConsistency($order, $payload);
            $targetState = $this->mapTargetState($status);
            $currentState = $order->payment_state ?? OrderPaymentState::CREATED;

            if (! $currentState->canTransitionTo($targetState)) {
                throw new IllegalStateTransitionException("Cannot transition from {$currentState->value} to {$targetState->value}.");
            }

            if ($currentState === $targetState) {
                // Still record the successful replay so observers have a timeline.
                $event->status = PaymentWebhookEventStatus::PROCESSED;
                $event->processed_at = Carbon::now();
                $event->save();

                return new PaymentWebhookResult([
                    'status' => PaymentWebhookEventStatus::PROCESSED->value,
                    'message' => 'Event already applied.',
                ], 200);
            }

            $this->applyStateTransition($order, $targetState);
            $order->payment_state = $targetState;
            $order->save();

            $event->status = PaymentWebhookEventStatus::PROCESSED;
            $event->processed_at = Carbon::now();
            $event->save();

            Log::info('Processed payment webhook event.', [
                'provider' => $provider,
                'event_id' => $eventId,
                'order_id' => $order->id,
                'target_state' => $targetState->value,
            ]);

            return new PaymentWebhookResult([
                'status' => PaymentWebhookEventStatus::PROCESSED->value,
                'message' => 'Event processed.',
            ], 200);
        } catch (Throwable $exception) {
            $event->status = PaymentWebhookEventStatus::FAILED;
            $event->processed_at = null;
            $event->save();

            throw $exception;
        }
    }

    /**
     * Resolve and validate the provider configuration array.
     *
     * @return array<string, mixed>
     */
    private function resolveProviderConfiguration(string $provider): array
    {
        $config = Config::get("payments.{$provider}.webhook");

        if (! is_array($config) || empty($config['secret'])) {
            throw new InvalidProviderConfigurationException("Webhook configuration missing for provider [{$provider}].");
        }

        return $config;
    }

    /**
     * Safely extract and normalize header values from the request.
     */
    private function extractHeader(Request $request, string $header): string
    {
        $value = (string) $request->headers->get($header, '');

        if ($value === '') {
            throw new InvalidProviderConfigurationException("Missing required webhook header [{$header}].");
        }

        return $value;
    }

    /**
     * Ensure the timestamp is within the configured tolerance window.
     */
    private function guardAgainstStaleTimestamp(string $timestamp, int $tolerance): void
    {
        if (! ctype_digit($timestamp)) {
            throw new StaleWebhookException('Webhook timestamp is invalid.');
        }

        $eventTime = Carbon::createFromTimestamp((int) $timestamp);
        $now = Carbon::now();

        if ($eventTime->diffInSeconds($now, true) > $tolerance) {
            throw new StaleWebhookException('Webhook timestamp exceeded tolerance.');
        }
    }

    /**
     * Verify the HMAC signature provided by the external payment provider.
     */
    private function guardAgainstInvalidSignature(string $signature, string $rawPayload, string $timestamp, string $secret): void
    {
        $signedPayload = $timestamp . '.' . $rawPayload;
        $computedSignature = hash_hmac('sha256', $signedPayload, $secret);

        if (! hash_equals($computedSignature, $signature)) {
            throw new InvalidSignatureException('Webhook signature verification failed.');
        }
    }

    /**
     * Decode the JSON payload while surfacing parsing errors immediately.
     *
     * @return array<string, mixed>
     */
    private function decodePayload(string $rawPayload): array
    {
        try {
            $decoded = json_decode($rawPayload, true, flags: JSON_THROW_ON_ERROR);
        } catch (Throwable $exception) {
            throw new MalformedWebhookPayloadException('Webhook payload could not be decoded.', 0, $exception);
        }

        if (! is_array($decoded)) {
            throw new MalformedWebhookPayloadException('Webhook payload must decode into an object.');
        }

        return $decoded;
    }

    /**
     * Pull a string value from the payload or raise an explicit error.
     */
    private function requireString(array $payload, string $key): string
    {
        $value = $payload[$key] ?? null;

        if (! is_string($value) || $value === '') {
            throw new MalformedWebhookPayloadException("Webhook payload missing [{$key}].");
        }

        return $value;
    }

    /**
     * Locate the order referenced by the webhook payload.
     */
    private function resolveOrder(string $orderNumber): Order
    {
        $order = Order::query()->where('number', $orderNumber)->first();

        if (! $order instanceof Order) {
            throw new OrderNotFoundException("Order [{$orderNumber}] could not be found.");
        }

        return $order;
    }

    /**
     * Ensure the reported amount and currency match the stored order totals.
     */
    private function assertAmountConsistency(Order $order, array $payload): void
    {
        $reportedAmount = $payload['amount'] ?? null;
        $reportedCurrency = $payload['currency'] ?? null;

        if (! is_string($reportedAmount) && ! is_numeric($reportedAmount)) {
            throw new MalformedWebhookPayloadException('Webhook payload must contain an amount string.');
        }

        if (! is_string($reportedCurrency) || $reportedCurrency === '') {
            throw new MalformedWebhookPayloadException('Webhook payload must contain a currency.');
        }

        $normalizedPayloadAmount = number_format((float) $reportedAmount, 2, '.', '');
        $normalizedOrderAmount = number_format((float) $order->total, 2, '.', '');

        if ($normalizedPayloadAmount !== $normalizedOrderAmount || strtoupper($reportedCurrency) !== strtoupper((string) $order->currency)) {
            throw new AmountMismatchException('Webhook amount or currency did not match the order.');
        }
    }

    /**
     * Map the provider status string into the internal lifecycle enum.
     */
    private function mapTargetState(string $status): OrderPaymentState
    {
        return match (strtolower($status)) {
            'created' => OrderPaymentState::CREATED,
            'paid' => OrderPaymentState::PAID,
            'fulfilled' => OrderPaymentState::FULFILLED,
            'partially_refunded' => OrderPaymentState::PARTIALLY_REFUNDED,
            'refunded' => OrderPaymentState::REFUNDED,
            default => throw new MalformedWebhookPayloadException("Unknown webhook status [{$status}]."),
        };
    }

    /**
     * Update the order to reflect the new lifecycle state and trigger any
     * asynchronous side effects, such as fulfillment emails.
     */
    private function applyStateTransition(Order $order, OrderPaymentState $targetState): void
    {
        switch ($targetState) {
            case OrderPaymentState::CREATED:
                $order->payment_status = PaymentStatus::PENDING;
                break;
            case OrderPaymentState::PAID:
                $order->payment_status = PaymentStatus::PAID;
                break;
            case OrderPaymentState::FULFILLED:
                $order->payment_status = PaymentStatus::PAID;
                $order->fulfillment_status = 'fulfilled';
                $order->status = OrderStatus::COMPLETED;
                SendOrderFulfillmentEmail::dispatch($order->id);
                break;
            case OrderPaymentState::PARTIALLY_REFUNDED:
                $order->payment_status = PaymentStatus::PARTIALLY_REFUNDED;
                break;
            case OrderPaymentState::REFUNDED:
                $order->payment_status = PaymentStatus::REFUNDED;
                $order->status = OrderStatus::REFUNDED;
                $order->fulfillment_status = 'refunded';
                break;
        }
    }
}
