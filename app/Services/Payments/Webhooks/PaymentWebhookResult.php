<?php

declare(strict_types=1);

namespace App\Services\Payments\Webhooks;

/**
 * PaymentWebhookResult bundles a response payload with the HTTP status so the
 * controller can translate service outcomes without duplicating logic.
 */
final class PaymentWebhookResult
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        public readonly array $payload,
        public readonly int $statusCode,
    ) {}
}
