<?php declare(strict_types=1);

namespace App\Enums;

enum PriorityEnum: int
{
    case LOW = 1;
    case NORMAL = 2;
    case HIGH = 3;
    case URGENT = 4;
    case CRITICAL = 5;

    public function getLabel(): string
    {
        return match ($this) {
            self::LOW => 'Low',
            self::NORMAL => 'Normal',
            self::HIGH => 'High',
            self::URGENT => 'Urgent',
            self::CRITICAL => 'Critical',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::LOW => 'gray',
            self::NORMAL => 'info',
            self::HIGH => 'warning',
            self::URGENT => 'danger',
            self::CRITICAL => 'danger',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::LOW => 'heroicon-o-arrow-down',
            self::NORMAL => 'heroicon-o-minus',
            self::HIGH => 'heroicon-o-arrow-up',
            self::URGENT => 'heroicon-o-exclamation-triangle',
            self::CRITICAL => 'heroicon-o-fire',
        };
    }
}
