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
        $key = 'enums.authorization_role.' . $this->value;
        $translation = __($key);

        if (! is_string($translation) || $translation === $key) {
            return str($this->value)->replace('_', ' ')->title()->toString();
        }

        return $translation;
    }
}
