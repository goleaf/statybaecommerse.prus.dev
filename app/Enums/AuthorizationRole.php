<?php

declare(strict_types=1);

namespace App\Enums;

enum AuthorizationRole: string
{
    case SUPER_ADMIN = 'super_admin';
    case ADMIN = 'admin';
    case ADMINISTRATOR = 'administrator';
    case MANAGER = 'manager';
    case EDITOR = 'editor';
    case SUPPORT = 'support';
    case VIEWER = 'viewer';
    case USER = 'user';

    /**
     * Human readable label for UI contexts.
     */
    public function label(): string
    {
        return match ($this) {
            self::SUPER_ADMIN   => 'Super Admin',
            self::ADMIN         => 'Admin',
            self::ADMINISTRATOR => 'Administrator',
            self::MANAGER       => 'Manager',
            self::EDITOR        => 'Editor',
            self::SUPPORT       => 'Support',
            self::VIEWER        => 'Viewer',
            self::USER          => 'User',
        };
    }
}
