<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AdminUser;
use App\Models\SystemSetting;
use App\Models\User;

/**
 * Authorization policy for managing system settings.
 */
final class SystemSettingPolicy
{
    public function viewAny(User|AdminUser $user): bool
    {
        return $this->hasPermission($user, 'view_settings');
    }

    public function view(User|AdminUser $user, SystemSetting $setting): bool
    {
        return $this->hasPermission($user, 'view_settings');
    }

    public function create(User|AdminUser $user): bool
    {
        return $this->hasPermission($user, 'edit_settings');
    }

    public function update(User|AdminUser $user, SystemSetting $setting): bool
    {
        return $this->hasPermission($user, 'edit_settings');
    }

    public function delete(User|AdminUser $user, SystemSetting $setting): bool
    {
        return $this->hasPermission($user, 'edit_settings');
    }

    private function hasPermission(User|AdminUser $user, string $permission): bool
    {
        if (! method_exists($user, 'can')) {
            return false;
        }

        return (bool) $user->can($permission);
    }
}
