<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * PaymentStatus
 */
enum PaymentStatus: string
{
    case PENDING = 'pending';
    case AUTHORIZED = 'authorized';
    case CAPTURED = 'captured';
    case SETTLED = 'settled';
    case PAID = 'paid';
    case PARTIALLY_REFUNDED = 'partially_refunded';
    case REFUNDED = 'refunded';
    case FAILED = 'failed';
}
