<?php

declare(strict_types=1);

namespace App\Enums;

enum Priority: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
    case URGENT = 'urgent';
    case CRITICAL = 'critical';

    public function getLabel(): string
    {
        return match ($this) {
            self::LOW      => __('messages.enums),
            self::MEDIUM   => __('messages.enums),
            self::HIGH     => __('messages.enums),
            self::URGENT   => __('messages.enums),
            self::CRITICAL => __('messages.enums),
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::LOW      => 'gray',
            self::MEDIUM   => 'info',
            self::HIGH     => 'warning',
            self::URGENT   => 'danger',
            self::CRITICAL => 'danger',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::LOW      => 'heroicon-o-minus-circle',
            self::MEDIUM   => 'heroicon-o-information-circle',
            self::HIGH     => 'heroicon-o-exclamation-triangle',
            self::URGENT   => 'heroicon-o-fire',
            self::CRITICAL => 'heroicon-o-bolt',
        };
    }

    public static function getOptions(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($case) => [
            $case->value => $case->getLabel(),
        ])->toArray();
    }
}
