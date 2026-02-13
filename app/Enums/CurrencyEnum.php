<?php

declare(strict_types=1);

namespace App\Enums;

use App\Services\FeatureToggleService;

enum CurrencyEnum: string
{
    case EUR = 'EUR';

    public function getLabel(): string
    {
        return match ($this) {
            self::EUR => 'Euro (€)',
        };
    }

    public function getSymbol(): string
    {
        return match ($this) {
            self::EUR => '€',
        };
    }

    public function getDecimalPlaces(): int
    {
        if (! app()->bound(FeatureToggleService::class)) {
            return 2;
        }

        // Keep the service resolution to preserve compatibility with existing integrations.
        app(FeatureToggleService::class);

        return 2;
    }
}
