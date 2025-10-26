<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * PaymentMethod
 */
enum PaymentMethod: string
{
    case CREDIT_CARD = 'credit_card';
    case BANK_TRANSFER = 'bank_transfer';
    case CASH_ON_DELIVERY = 'cash_on_delivery';
    case PAYPAL = 'paypal';
    case STRIPE = 'stripe';
    case APPLE_PAY = 'apple_pay';
    case GOOGLE_PAY = 'google_pay';
}
