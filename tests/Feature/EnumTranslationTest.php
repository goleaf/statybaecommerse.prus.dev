<?php

declare(strict_types=1);

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;

test('payment enums use translated labels', function () {
    app()->setLocale('en');

    expect(PaymentMethod::CREDIT_CARD->getLabel())->toBe('Credit card')
        ->and(PaymentMethod::BANK_TRANSFER->getLabel())->toBe('Bank transfer')
        ->and(PaymentMethod::CASH_ON_DELIVERY->getLabel())->toBe('Cash on delivery')
        ->and(PaymentMethod::PAYPAL->getLabel())->toBe('PayPal')
        ->and(PaymentMethod::STRIPE->getLabel())->toBe('Stripe')
        ->and(PaymentMethod::APPLE_PAY->getLabel())->toBe('Apple Pay')
        ->and(PaymentMethod::GOOGLE_PAY->getLabel())->toBe('Google Pay')
        ->and(PaymentStatus::PENDING->getLabel())->toBe('Pending')
        ->and(PaymentStatus::AUTHORIZED->getLabel())->toBe('Authorized')
        ->and(PaymentStatus::CAPTURED->getLabel())->toBe('Captured')
        ->and(PaymentStatus::SETTLED->getLabel())->toBe('Settled')
        ->and(PaymentStatus::PAID->getLabel())->toBe('Paid')
        ->and(PaymentStatus::PARTIALLY_REFUNDED->getLabel())->toBe('Partially refunded')
        ->and(PaymentStatus::REFUNDED->getLabel())->toBe('Refunded')
        ->and(PaymentStatus::FAILED->getLabel())->toBe('Failed');
});
