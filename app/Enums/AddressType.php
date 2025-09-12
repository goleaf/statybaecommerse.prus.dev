<?php declare(strict_types=1);

namespace App\Enums;

use Illuminate\Support\Collection;

enum AddressType: string
{
    case SHIPPING = 'shipping';
    case BILLING = 'billing';
    case HOME = 'home';
    case WORK = 'work';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::SHIPPING => __('translations.address_type_shipping'),
            self::BILLING => __('translations.address_type_billing'),
            self::HOME => __('translations.address_type_home'),
            self::WORK => __('translations.address_type_work'),
            self::OTHER => __('translations.address_type_other'),
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::SHIPPING => __('translations.address_type_shipping_description'),
            self::BILLING => __('translations.address_type_billing_description'),
            self::HOME => __('translations.address_type_home_description'),
            self::WORK => __('translations.address_type_work_description'),
            self::OTHER => __('translations.address_type_other_description'),
        };
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
            default => false,
        };
    }

    public function isRequired(): bool
    {
        return match ($this) {
            self::SHIPPING, self::BILLING => true,
            default => false,
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
        return collect(self::cases())
            ->sortBy('priority')
            ->mapWithKeys(fn($case) => [$case->value => $case->label()])
            ->toArray();
    }

    public static function optionsWithDescriptions(): array
    {
        return collect(self::cases())
            ->sortBy('priority')
            ->mapWithKeys(fn($case) => [
                $case->value => [
                    'label' => $case->label(),
                    'description' => $case->description(),
                    'icon' => $case->icon(),
                    'color' => $case->color(),
                    'is_primary' => $case->isPrimary(),
                    'is_required' => $case->isRequired(),
                ]
            ])
            ->toArray();
    }

    public static function primary(): Collection
    {
        return collect(self::cases())
            ->filter(fn($case) => $case->isPrimary());
    }

    public static function required(): Collection
    {
        return collect(self::cases())
            ->filter(fn($case) => $case->isRequired());
    }

    public static function optional(): Collection
    {
        return collect(self::cases())
            ->filter(fn($case) => !$case->isRequired());
    }

    public static function ordered(): Collection
    {
        return collect(self::cases())
            ->sortBy('priority');
    }

    public static function fromLabel(string $label): ?self
    {
        return collect(self::cases())
            ->first(fn($case) => $case->label() === $label);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function labels(): array
    {
        return collect(self::cases())
            ->map(fn($case) => $case->label())
            ->toArray();
    }

    public function toArray(): array
    {
        return [
            'value' => $this->value,
            'label' => $this->label(),
            'description' => $this->description(),
            'icon' => $this->icon(),
            'color' => $this->color(),
            'is_primary' => $this->isPrimary(),
            'is_required' => $this->isRequired(),
            'priority' => $this->priority(),
        ];
    }
}
