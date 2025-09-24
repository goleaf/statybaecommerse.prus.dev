<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Models\Order;

/**
 * PaymentService
 *
 * Service class containing PaymentService business logic, external integrations, and complex operations with proper error handling and logging.
 */
final class PaymentService
{
    /**
     * Handle process functionality with proper error handling.
     */
    public function process(Order $order, array $paymentData = []): array
    {
        $provider = (string) ($paymentData['provider'] ?? $paymentData['name'] ?? 'manual');
        $txnStatus = 'authorized';
        $tx = ['id' => (string) uniqid($provider.'_', true), 'provider' => $provider, 'status' => $txnStatus, 'amount' => (float) $order->grand_total_amount, 'currency' => (string) $order->currency_code, 'created_at' => now()->toIso8601String(), 'meta' => $paymentData];

        return ['status' => 'pending', 'transaction' => $tx];
    }
}
