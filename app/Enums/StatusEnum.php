<?php declare(strict_types=1);

namespace App\Enums;

enum StatusEnum: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case PENDING = 'pending';
    case SUSPENDED = 'suspended';
    case ARCHIVED = 'archived';

    public function getLabel(): string
    {
        return match($this) {
            self::ACTIVE => 'Active',
            self::INACTIVE => 'Inactive',
            self::PENDING => 'Pending',
            self::SUSPENDED => 'Suspended',
            self::ARCHIVED => 'Archived',
        };
    }

    public function getColor(): string
    {
        return match($this) {
            self::ACTIVE => 'success',
            self::INACTIVE => 'gray',
            self::PENDING => 'warning',
            self::SUSPENDED => 'danger',
            self::ARCHIVED => 'secondary',
        };
    }

    public function getIcon(): string
    {
        return match($this) {
            self::ACTIVE => 'heroicon-o-check-circle',
            self::INACTIVE => 'heroicon-o-x-circle',
            self::PENDING => 'heroicon-o-clock',
            self::SUSPENDED => 'heroicon-o-pause-circle',
            self::ARCHIVED => 'heroicon-o-archive-box',
        };
    }
}
