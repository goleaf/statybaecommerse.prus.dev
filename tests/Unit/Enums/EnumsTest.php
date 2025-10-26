<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use App\Enums\AddressType;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use PHPUnit\Framework\TestCase;

final class EnumsTest extends TestCase
{
    public function test_payment_enums_round_trip(): void
    {
        self::assertSame('stripe', PaymentMethod::tryFrom('stripe')?->value);
        self::assertSame('pending', PaymentStatus::tryFrom('pending')?->value);
        self::assertNull(PaymentStatus::tryFrom('not-a-status'));
    }

    public function test_address_type_helpers(): void
    {
        $values = AddressType::values();
        self::assertContains('shipping', $values);
        self::assertContains('billing', $values);

        $ordered = AddressType::ordered();
        self::assertNotEmpty($ordered);

        $label = AddressType::SHIPPING->label();
        self::assertIsString($label);

        $fromLabel = AddressType::fromLabel($label);
        self::assertSame(AddressType::SHIPPING, $fromLabel);
    }
}
