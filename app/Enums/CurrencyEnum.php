<?php

declare(strict_types=1);

namespace App\Enums;

enum CurrencyEnum: string
{
    case EUR = 'EUR';
    case USD = 'USD';
    case GBP = 'GBP';
    case JPY = 'JPY';
    case CAD = 'CAD';
    case AUD = 'AUD';
    case CHF = 'CHF';
    case CNY = 'CNY';
    case SEK = 'SEK';
    case NOK = 'NOK';
    case DKK = 'DKK';
    case PLN = 'PLN';
    case CZK = 'CZK';
    case HUF = 'HUF';
    case RON = 'RON';
    case BGN = 'BGN';
    case HRK = 'HRK';
    case KRW = 'KRW';
    case RSD = 'RSD';
    case UAH = 'UAH';
    case RUB = 'RUB';

    public function getLabel(): string
    {
        return match ($this) {
            self::EUR => 'Euro (€)',
            self::USD => 'US Dollar ($)',
            self::GBP => 'British Pound (£)',
            self::JPY => 'Japanese Yen (¥)',
            self::CAD => 'Canadian Dollar (C$)',
            self::AUD => 'Australian Dollar (A$)',
            self::CHF => 'Swiss Franc (CHF)',
            self::CNY => 'Chinese Yuan (¥)',
            self::SEK => 'Swedish Krona (kr)',
            self::NOK => 'Norwegian Krone (kr)',
            self::DKK => 'Danish Krone (kr)',
            self::PLN => 'Polish Złoty (zł)',
            self::CZK => 'Czech Koruna (Kč)',
            self::HUF => 'Hungarian Forint (Ft)',
            self::RON => 'Romanian Leu (lei)',
            self::BGN => 'Bulgarian Lev (лв)',
            self::HRK => 'Croatian Kuna (kn)',
            self::KRW => 'South Korean Won (₩)',
            self::RSD => 'Serbian Dinar (дин)',
            self::UAH => 'Ukrainian Hryvnia (₴)',
            self::RUB => 'Russian Ruble (₽)',
        };
    }

    public function getSymbol(): string
    {
        return match ($this) {
            self::EUR => '€',
            self::USD => '$',
            self::GBP => '£',
            self::JPY => '¥',
            self::CAD => 'C$',
            self::AUD => 'A$',
            self::CHF => 'CHF',
            self::CNY => '¥',
            self::SEK => 'kr',
            self::NOK => 'kr',
            self::DKK => 'kr',
            self::PLN => 'zł',
            self::CZK => 'Kč',
            self::HUF => 'Ft',
            self::RON => 'lei',
            self::BGN => 'лв',
            self::HRK => 'kn',
            self::KRW => '₩',
            self::RSD => 'дин',
            self::UAH => '₴',
            self::RUB => '₽',
        };
    }

    public function getDecimalPlaces(): int
    {
        // Resolve the feature toggle service lazily to avoid coupling the enum
        // directly to configuration lookups when the container is unavailable.
        $featureToggleService = app(\App\Services\FeatureToggleService::class);

        // Zero-decimal currencies are defined by configuration and feature flags,
        // enabling gradual rollout without affecting every currency instantly.
        $zeroDecimalCurrencies = $featureToggleService->getZeroDecimalCurrencies();

        return in_array($this->value, $zeroDecimalCurrencies, true) ? 0 : 2;
    }
}
