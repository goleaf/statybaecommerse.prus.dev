<?php

declare (strict_types=1);
namespace App\Enums;

use Illuminate\Support\Collection;
/**
 * PaymentType
 * 
 * Enumeration defining a set of named constants with type safety.
 */
enum PaymentType : string
{
    case Stripe = 'stripe';
    case NotchPay = 'notch-pay';
    case Cash = 'cash';
    public function label(): string
    {
        return match ($this) {
            self::Stripe => __('translations.payment_type_stripe'),
            self::NotchPay => __('translations.payment_type_notch_pay'),
            self::Cash => __('translations.payment_type_cash'),
        };
    }
    public function description(): string
    {
        return match ($this) {
            self::Stripe => __('translations.payment_type_stripe_description'),
            self::NotchPay => __('translations.payment_type_notch_pay_description'),
            self::Cash => __('translations.payment_type_cash_description'),
        };
    }
    public function icon(): string
    {
        return match ($this) {
            self::Stripe => 'heroicon-o-credit-card',
            self::NotchPay => 'heroicon-o-banknotes',
            self::Cash => 'heroicon-o-currency-dollar',
        };
    }
    public function color(): string
    {
        return match ($this) {
            self::Stripe => 'blue',
            self::NotchPay => 'green',
            self::Cash => 'gray',
        };
    }
    public function isOnline(): bool
    {
        return match ($this) {
            self::Stripe, self::NotchPay => true,
            self::Cash => false,
        };
    }
    public function isOffline(): bool
    {
        return match ($this) {
            self::Cash => true,
            default => false,
        };
    }
    public function requiresProcessing(): bool
    {
        return match ($this) {
            self::Stripe, self::NotchPay => true,
            self::Cash => false,
        };
    }
    public function supportsRefunds(): bool
    {
        return match ($this) {
            self::Stripe, self::NotchPay => true,
            self::Cash => false,
        };
    }
    public function supportsPartialRefunds(): bool
    {
        return match ($this) {
            self::Stripe, self::NotchPay => true,
            self::Cash => false,
        };
    }
    public function processingTime(): string
    {
        return match ($this) {
            self::Stripe => __('translations.payment_processing_instant'),
            self::NotchPay => __('translations.payment_processing_instant'),
            self::Cash => __('translations.payment_processing_immediate'),
        };
    }
    public function feePercentage(): float
    {
        return match ($this) {
            self::Stripe => 2.9,
            self::NotchPay => 2.5,
            self::Cash => 0.0,
        };
    }
    public function fixedFee(): float
    {
        return match ($this) {
            self::Stripe => 0.3,
            self::NotchPay => 0.25,
            self::Cash => 0.0,
        };
    }
    public function minimumAmount(): float
    {
        return match ($this) {
            self::Stripe => 0.5,
            self::NotchPay => 0.5,
            self::Cash => 0.01,
        };
    }
    public function maximumAmount(): float
    {
        return match ($this) {
            self::Stripe => 999999.99,
            self::NotchPay => 999999.99,
            self::Cash => 999999.99,
        };
    }
    public function supportedCurrencies(): array
    {
        return match ($this) {
            self::Stripe => ['EUR', 'USD', 'GBP', 'CAD', 'AUD'],
            self::NotchPay => ['EUR', 'USD', 'XOF', 'XAF'],
            self::Cash => ['EUR', 'USD', 'GBP', 'CAD', 'AUD'],
        };
    }
    public function priority(): int
    {
        return match ($this) {
            self::Stripe => 1,
            self::NotchPay => 2,
            self::Cash => 3,
        };
    }
    public function isEnabled(): bool
    {
        return match ($this) {
            self::Stripe => config('payments.stripe.enabled', true),
            self::NotchPay => config('payments.notchpay.enabled', true),
            self::Cash => config('payments.cash.enabled', true),
        };
    }
    public function getConfigKey(): string
    {
        return match ($this) {
            self::Stripe => 'stripe',
            self::NotchPay => 'notchpay',
            self::Cash => 'cash',
        };
    }
    public function getWebhookUrl(): ?string
    {
        return match ($this) {
            self::Stripe => route('webhooks.stripe'),
            self::NotchPay => route('webhooks.notchpay'),
            self::Cash => null,
        };
    }
    public static function options(): array
    {
        return collect(self::cases())->filter(fn($case) => $case->isEnabled())->sortBy('priority')->mapWithKeys(fn($case) => [$case->value => $case->label()])->toArray();
    }
    public static function optionsWithDescriptions(): array
    {
        return collect(self::cases())->filter(fn($case) => $case->isEnabled())->sortBy('priority')->mapWithKeys(fn($case) => [$case->value => ['label' => $case->label(), 'description' => $case->description(), 'icon' => $case->icon(), 'color' => $case->color(), 'is_online' => $case->isOnline(), 'is_offline' => $case->isOffline(), 'requires_processing' => $case->requiresProcessing(), 'supports_refunds' => $case->supportsRefunds(), 'supports_partial_refunds' => $case->supportsPartialRefunds(), 'processing_time' => $case->processingTime(), 'fee_percentage' => $case->feePercentage(), 'fixed_fee' => $case->fixedFee(), 'minimum_amount' => $case->minimumAmount(), 'maximum_amount' => $case->maximumAmount(), 'supported_currencies' => $case->supportedCurrencies()]])->toArray();
    }
    public static function online(): Collection
    {
        return collect(self::cases())->filter(fn($case) => $case->isOnline() && $case->isEnabled());
    }
    public static function offline(): Collection
    {
        return collect(self::cases())->filter(fn($case) => $case->isOffline() && $case->isEnabled());
    }
    public static function enabled(): Collection
    {
        return collect(self::cases())->filter(fn($case) => $case->isEnabled());
    }
    public static function withRefunds(): Collection
    {
        return collect(self::cases())->filter(fn($case) => $case->supportsRefunds() && $case->isEnabled());
    }
    public static function ordered(): Collection
    {
        return collect(self::cases())->filter(fn($case) => $case->isEnabled())->sortBy('priority');
    }
    public static function fromLabel(string $label): ?self
    {
        return collect(self::cases())->first(fn($case) => $case->label() === $label);
    }
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
    public static function labels(): array
    {
        return collect(self::cases())->map(fn($case) => $case->label())->toArray();
    }
    public function calculateFee(float $amount): float
    {
        if ($this->isOffline()) {
            return 0.0;
        }
        return $amount * $this->feePercentage() / 100 + $this->fixedFee();
    }
    public function isValidAmount(float $amount): bool
    {
        return $amount >= $this->minimumAmount() && $amount <= $this->maximumAmount();
    }
    public function supportsCurrency(string $currency): bool
    {
        return in_array(strtoupper($currency), $this->supportedCurrencies());
    }
    public function toArray(): array
    {
        return ['value' => $this->value, 'label' => $this->label(), 'description' => $this->description(), 'icon' => $this->icon(), 'color' => $this->color(), 'is_online' => $this->isOnline(), 'is_offline' => $this->isOffline(), 'requires_processing' => $this->requiresProcessing(), 'supports_refunds' => $this->supportsRefunds(), 'supports_partial_refunds' => $this->supportsPartialRefunds(), 'processing_time' => $this->processingTime(), 'fee_percentage' => $this->feePercentage(), 'fixed_fee' => $this->fixedFee(), 'minimum_amount' => $this->minimumAmount(), 'maximum_amount' => $this->maximumAmount(), 'supported_currencies' => $this->supportedCurrencies(), 'priority' => $this->priority(), 'is_enabled' => $this->isEnabled(), 'config_key' => $this->getConfigKey(), 'webhook_url' => $this->getWebhookUrl()];
    }
}