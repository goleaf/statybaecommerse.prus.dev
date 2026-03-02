<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * PaymentMethod
 */
enum PaymentMethod: string implements HasLabel
{
    case CREDIT_CARD = 'credit_card';
    case BANK_TRANSFER = 'bank_transfer';
    case CASH_ON_DELIVERY = 'cash_on_delivery';
    case PAYPAL = 'paypal';
    case STRIPE = 'stripe';
    case APPLE_PAY = 'apple_pay';
    case GOOGLE_PAY = 'google_pay';
    case MONTONIO = 'montonio';

    private const LABEL_DEFAULTS = [
        'credit_card'      => 'Credit card',
        'bank_transfer'    => 'Bank transfer',
        'cash_on_delivery' => 'Cash on delivery',
        'paypal'           => 'PayPal',
        'stripe'           => 'Stripe',
        'apple_pay'        => 'Apple Pay',
        'google_pay'       => 'Google Pay',
        'montonio'         => 'Montonio',
    ];

    public function getLabel(): ?string
    {
        $key = 'enums.payment_method.' . $this->value;
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
        return [
            self::MONTONIO->value => (string) self::MONTONIO->getLabel(),
        ];
    }
}
