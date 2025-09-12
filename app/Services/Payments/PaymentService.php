<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Models\Order;

final class PaymentService
{
    /**
     * Process payment for the given order.
     * For now, this is a stub that marks status as 'pending' and records a manual transaction.
     *
     * @param  array<string,mixed>  $paymentData
     * @return array{status:string, transaction:array<string,mixed>}
     */
    public function process(Order $order, array $paymentData = []): array
    {
        $provider = (string) ($paymentData['provider'] ?? $paymentData['name'] ?? 'manual');
        $txnStatus = 'authorized';
        $tx = [
            'id' => (string) (uniqid($provider.'_', true)),
            'provider' => $provider,
            'status' => $txnStatus,
            'amount' => (float) $order->grand_total_amount,
            'currency' => (string) $order->currency_code,
            'created_at' => now()->toIso8601String(),
            'meta' => $paymentData,
        ];

        return [
            'status' => 'pending',
            'transaction' => $tx,
        ];
    }
}
