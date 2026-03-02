<?php

declare(strict_types=1);

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Services\Payments\Exceptions\AmountMismatchException;
use App\Services\Payments\Exceptions\IllegalStateTransitionException;
use App\Services\Payments\Exceptions\InvalidProviderConfigurationException;
use App\Services\Payments\Exceptions\InvalidSignatureException;
use App\Services\Payments\Exceptions\MalformedWebhookPayloadException;
use App\Services\Payments\Exceptions\OrderNotFoundException;
use App\Services\Payments\Exceptions\StaleWebhookException;
use App\Services\Payments\Webhooks\PaymentWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * PaymentWebhookController adapts the webhook service for HTTP routing and
 * translates domain exceptions into provider-friendly responses.
 */
final class PaymentWebhookController extends Controller
{
    public function __construct(private readonly PaymentWebhookService $webhookService) {}

    /**
     * Entry point for Stripe webhooks.
     */
    public function handleStripe(Request $request): JsonResponse
    {
        return $this->handle($request, 'stripe');
    }

    /**
     * Entry point for NotchPay webhooks.
     */
    public function handleNotchPay(Request $request): JsonResponse
    {
        return $this->handle($request, 'notchpay');
    }

    /**
     * Entry point for Montonio webhooks.
     */
    public function handleMontonio(Request $request, \App\Services\Payments\MontonioService $montonioService): JsonResponse
    {
        try {
            $token = $request->input('orderToken');

            if (! $token) {
                return response()->json(['error' => 'missing_token'], 400);
            }

            $payload = $montonioService->validateToken($token);

            $orderNumber = $payload['merchantReference'] ?? null;
            $status = $payload['paymentStatus'] ?? null;

            if (! $orderNumber || ! $status) {
                return response()->json(['error' => 'invalid_payload'], 400);
            }

            $order = \App\Models\Order::query()->where('number', $orderNumber)->first();

            if (! $order) {
                return response()->json(['error' => 'order_not_found'], 404);
            }

            if ($status === 'PAID') {
                $order->payment_status = \App\Enums\PaymentStatus::PAID;
                $order->payment_state = \App\Enums\OrderPaymentState::PAID;
                if ($order->isDirty()) {
                    $order->save();
                }
            }

            return response()->json(['status' => 'success']);

        } catch (Throwable $exception) {
            Log::error('Montonio webhook handling failure.', [
                'message' => $exception->getMessage(),
            ]);

            return response()->json(['error' => 'internal_error'], 500);
        }
    }

    /**
     * Delegate webhook handling to the shared service while mapping exceptions
     * to appropriate HTTP status codes.
     */
    private function handle(Request $request, string $provider): JsonResponse
    {
        try {
            $result = $this->webhookService->handle($provider, $request);

            return response()->json($result->payload, $result->statusCode);
        } catch (InvalidSignatureException $exception) {
            return response()->json(['error' => 'invalid_signature'], 401);
        } catch (StaleWebhookException $exception) {
            return response()->json(['error' => 'stale_webhook'], 400);
        } catch (MalformedWebhookPayloadException $exception) {
            return response()->json(['error' => 'invalid_payload'], 400);
        } catch (AmountMismatchException|IllegalStateTransitionException $exception) {
            return response()->json(['error' => 'conflict'], 409);
        } catch (OrderNotFoundException $exception) {
            return response()->json(['error' => 'order_not_found'], 404);
        } catch (InvalidProviderConfigurationException $exception) {
            Log::warning('Webhook rejected due to provider misconfiguration.', [
                'provider' => $provider,
            ]);

            return response()->json(['error' => 'misconfigured_webhook'], 400);
        } catch (Throwable $exception) {
            Log::error('Unexpected webhook handling failure.', [
                'provider' => $provider,
                'message'  => $exception->getMessage(),
            ]);

            return response()->json(['error' => 'internal_error'], 500);
        }
    }
}
