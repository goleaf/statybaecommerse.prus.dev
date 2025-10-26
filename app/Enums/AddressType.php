<?php

declare(strict_types=1);

namespace App\Enums;

use App\Contracts\EnumInterface;
use Illuminate\Support\Collection;
use Throwable;

/**
 * AddressType
 *
 * Enumeration defining a set of named constants with type safety.
 */
enum AddressType: string implements EnumInterface
{
    case SHIPPING = 'shipping';
    case BILLING = 'billing';
    case HOME = 'home';
    case WORK = 'work';
    case OTHER = 'other';

    private const LABEL_DEFAULTS = [
        'shipping' => 'Shipping',
        'billing'  => 'Billing',
        'home'     => 'Home',
        'work'     => 'Work',
        'other'    => 'Other',
    ];

    private const DESCRIPTION_DEFAULTS = [
        'shipping' => 'Primary shipping address',
        'billing'  => 'Primary billing address',
        'home'     => 'Residential address',
        'work'     => 'Workplace address',
        'other'    => 'Additional address',
    ];

    public function label(): string
    {
        $key = match ($this) {
            self::SHIPPING => 'translations.address_type_shipping',
            self::BILLING  => 'translations.address_type_billing',
            self::HOME     => 'translations.address_type_home',
            self::WORK     => 'translations.address_type_work',
            self::OTHER    => 'translations.address_type_other',
        };

        return self::translate($key, self::LABEL_DEFAULTS[$this->value] ?? $this->value);
    }

    public function description(): string
    {
        $key = match ($this) {
            self::SHIPPING => 'translations.address_type_shipping_description',
            self::BILLING  => 'translations.address_type_billing_description',
            self::HOME     => 'translations.address_type_home_description',
            self::WORK     => 'translations.address_type_work_description',
            self::OTHER    => 'translations.address_type_other_description',
        };

        return self::translate($key, self::DESCRIPTION_DEFAULTS[$this->value] ?? $this->value);
    }

    public function icon(): string
    {
        return match ($this) {
            self::SHIPPING => 'heroicon-o-truck',
            self::BILLING => 'heroicon-o-credit-card',
            self::HOME => 'heroicon-o-home',
            self::WORK => 'heroicon-o-building-office',
            self::OTHER => 'heroicon-o-map-pin',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::SHIPPING => 'blue',
            self::BILLING => 'green',
            self::HOME => 'purple',
            self::WORK => 'orange',
            self::OTHER => 'gray',
        };
    }

    public function isPrimary(): bool
    {
        return match ($this) {
            self::SHIPPING, self::BILLING => true,
            default                       => false,
        };
    }

    public function isRequired(): bool
    {
        return match ($this) {
            self::SHIPPING, self::BILLING => true,
            default                       => false,
        };
    }

    public function priority(): int
    {
        return match ($this) {
            self::SHIPPING => 1,
            self::BILLING => 2,
            self::HOME => 3,
            self::WORK => 4,
            self::OTHER => 5,
        };
    }

    public static function options(): array
    {
        return Collection::make(self::cases())
            ->sortBy(fn (self $case): int => $case->priority())
            ->mapWithKeys(fn (self $case): array => [$case->value => $case->label()])
            ->toArray();
    }

    public static function optionsWithDescriptions(): array
    {
        return Collection::make(self::cases())
            ->sortBy(fn (self $case): int => $case->priority())
            ->mapWithKeys(fn (self $case): array => [
                $case->value => [
                    'label'       => $case->label(),
                    'description' => $case->description(),
                    'icon'        => $case->icon(),
                    'color'       => $case->color(),
                    'is_primary'  => $case->isPrimary(),
                    'is_required' => $case->isRequired(),
                ],
            ])
            ->toArray();
    }

    public static function primary(): Collection
    {
        return Collection::make(self::cases())->filter(static fn (self $case): bool => $case->isPrimary());
    }

    public static function required(): Collection
    {
        return Collection::make(self::cases())->filter(static fn (self $case): bool => $case->isRequired());
    }

    public static function optional(): Collection
    {
        return Collection::make(self::cases())->filter(static fn (self $case): bool => ! $case->isRequired());
    }

    public static function ordered(): Collection
    {
        return Collection::make(self::cases())->sortBy(fn (self $case): int => $case->priority());
    }

    /**
     * Provide a collection wrapper for the enum cases to satisfy the shared contract.
     */
    public static function collection(): Collection
    {
        return Collection::make(self::cases());
    }

    public static function fromLabel(string $label): ?static
    {
        return Collection::make(self::cases())->first(static fn (self $case): bool => $case->label() === $label);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function labels(): array
    {
        return Collection::make(self::cases())
            ->map(static fn (self $case): string => $case->label())
            ->toArray();
    }

    public function toArray(): array
    {
        return [
            'value'       => $this->value,
            'label'       => $this->label(),
            'description' => $this->description(),
            'icon'        => $this->icon(),
            'color'       => $this->color(),
            'is_primary'  => $this->isPrimary(),
            'is_required' => $this->isRequired(),
            'priority'    => $this->priority(),
        ];
    }

    private static function translate(string $key, string $default): string
    {
        if (! function_exists('__')) {
            return $default;
        }

        try {
            $translated = __($key);
        } catch (Throwable) {
            return $default;
        }

        if (! is_string($translated) || $translated === $key) {
            return $default;
        }

        return $translated;
    }
}
