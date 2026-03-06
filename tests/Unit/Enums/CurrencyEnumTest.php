<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use App\Enums\CurrencyEnum;
use App\Services\FeatureToggleService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class CurrencyEnumTest extends TestCase
{
    #[Test]
    public function it_only_exposes_eur_case(): void
    {
        self::assertSame([CurrencyEnum::EUR], CurrencyEnum::cases());
        self::assertSame(CurrencyEnum::EUR, CurrencyEnum::tryFrom('EUR'));
        self::assertNull(CurrencyEnum::tryFrom('USD'));
        self::assertSame('€', CurrencyEnum::EUR->getSymbol());
        self::assertSame(__('enums.currency.eur'), CurrencyEnum::EUR->getLabel());
    }

    #[Test]
    public function it_always_uses_two_decimal_places(): void
    {
        $stub = new class
        {
            public int $calls = 0;

            public function getZeroDecimalCurrencies(): array
            {
                $this->calls++;

                return ['JPY', 'KRW'];
            }

            public function isEnabled(string $featureKey, array $context = []): bool
            {
                // Keep feature checks deterministic for the enum contract test.
                return true;
            }
        };

        $this->app->instance(FeatureToggleService::class, $stub);

        self::assertSame(2, CurrencyEnum::EUR->getDecimalPlaces());
        self::assertSame(0, $stub->calls);
    }
}
