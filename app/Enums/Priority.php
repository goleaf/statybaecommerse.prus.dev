<?php declare(strict_types=1);

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
        return match($this) {
            self::LOW => __('enums.priority.low'),
            self::MEDIUM => __('enums.priority.medium'),
            self::HIGH => __('enums.priority.high'),
            self::URGENT => __('enums.priority.urgent'),
            self::CRITICAL => __('enums.priority.critical'),
        };
    }

    public function getColor(): string
    {
        return match($this) {
            self::LOW => 'gray',
            self::MEDIUM => 'info',
            self::HIGH => 'warning',
            self::URGENT => 'danger',
            self::CRITICAL => 'danger',
        };
    }

    public function getIcon(): string
    {
        return match($this) {
            self::LOW => 'heroicon-o-minus-circle',
            self::MEDIUM => 'heroicon-o-information-circle',
            self::HIGH => 'heroicon-o-exclamation-triangle',
            self::URGENT => 'heroicon-o-fire',
            self::CRITICAL => 'heroicon-o-bolt',
        };
    }

    public static function getOptions(): array
    {
        return collect(self::cases())->mapWithKeys(fn($case) => [
            $case->value => $case->getLabel()
        ])->toArray();
    }
}
