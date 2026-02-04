<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * PaymentStatus
 */
enum PaymentStatus: string implements HasLabel
{
    case PENDING = 'pending';
    case AUTHORIZED = 'authorized';
    case CAPTURED = 'captured';
    case SETTLED = 'settled';
    case PAID = 'paid';
    case PARTIALLY_REFUNDED = 'partially_refunded';
    case REFUNDED = 'refunded';
    case FAILED = 'failed';

    public function getLabel(): ?string
    {
        return __('enums.payment_status.' . $this->value);
    }
}
