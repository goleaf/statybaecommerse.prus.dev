<?php declare(strict_types=1);

namespace App\Enums;

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

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->label()])
            ->toArray();
    }
}
