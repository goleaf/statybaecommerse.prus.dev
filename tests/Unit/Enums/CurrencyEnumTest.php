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
    public function it_includes_south_korean_won_case(): void
    {
        // Ensure the newly added enum case can be resolved from its ISO code.
        self::assertSame(CurrencyEnum::KRW, CurrencyEnum::tryFrom('KRW'));
        self::assertSame('₩', CurrencyEnum::KRW->getSymbol());
        self::assertSame('South Korean Won (₩)', CurrencyEnum::KRW->getLabel());
    }

    #[Test]
    public function it_delegates_decimal_place_logic_to_feature_toggle_service(): void
    {
        // Use a lightweight stub to expose deterministic zero-decimal currencies.
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

        // Verify currencies present in the zero-decimal list report no fractional digits.
        self::assertSame(0, CurrencyEnum::JPY->getDecimalPlaces());
        self::assertSame(0, CurrencyEnum::KRW->getDecimalPlaces());
        // Other currencies should default back to the standard two decimal places.
        self::assertSame(2, CurrencyEnum::USD->getDecimalPlaces());

        // Ensure the enum consulted the stub for each invocation.
        self::assertSame(3, $stub->calls);
    }
}
