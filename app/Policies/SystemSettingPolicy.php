<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AdminUser;
use App\Models\SystemSetting;
use App\Models\User;
use App\Support\Authorization\AuthorizationMatrix;

final class SystemSettingPolicy
{
    public function viewAny(User|AdminUser $user): bool
    {
        return AuthorizationMatrix::check('system_settings', 'viewAny', $user);
    }

    public function view(User|AdminUser $user, SystemSetting $setting): bool
    {
        return AuthorizationMatrix::check('system_settings', 'view', $user);
    }

    public function create(User|AdminUser $user): bool
    {
        return AuthorizationMatrix::check('system_settings', 'create', $user);
    }

    public function update(User|AdminUser $user, SystemSetting $setting): bool
    {
        return AuthorizationMatrix::check('system_settings', 'update', $user);
    }

    public function delete(User|AdminUser $user, SystemSetting $setting): bool
    {
        return AuthorizationMatrix::check('system_settings', 'delete', $user);
    }
}
