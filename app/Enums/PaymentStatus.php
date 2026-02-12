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

    private const LABEL_DEFAULTS = [
        'pending'            => 'Pending',
        'authorized'         => 'Authorized',
        'captured'           => 'Captured',
        'settled'            => 'Settled',
        'paid'               => 'Paid',
        'partially_refunded' => 'Partially refunded',
        'refunded'           => 'Refunded',
        'failed'             => 'Failed',
    ];

    public function getLabel(): ?string
    {
        $key = 'enums.payment_status.' . $this->value;
        $translation = __($key);

        if (! is_string($translation) || $translation === $key) {
            return self::LABEL_DEFAULTS[$this->value] ?? $this->value;
        }

        return $translation;
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = (string) $case->getLabel();
        }

        return $options;
    }
}
