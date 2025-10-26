<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * PaymentWebhookEventStatus keeps track of idempotency bookkeeping for
 * received payment webhooks so we know whether a payload was processed.
 */
enum PaymentWebhookEventStatus: string
{
    case RECEIVED = 'received';
    case PROCESSED = 'processed';
    case IGNORED = 'ignored';
    case FAILED = 'failed';
}
