<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AdminUser;
use App\Models\Role;
use App\Models\User;

final class RolePolicy
{
    public function viewAny(AdminUser|User $user): bool
    {
        return $user instanceof AdminUser;
    }

    public function view(AdminUser|User $user, Role $role): bool
    {
        return $user instanceof AdminUser;
    }

    public function create(AdminUser|User $user): bool
    {
        return $user instanceof AdminUser;
    }

    public function update(AdminUser|User $user, Role $role): bool
    {
        return $user instanceof AdminUser;
    }

    public function delete(AdminUser|User $user, Role $role): bool
    {
        return $user instanceof AdminUser;
    }

    public function restore(AdminUser|User $user, Role $role): bool
    {
        return $user instanceof AdminUser;
    }

    public function forceDelete(AdminUser|User $user, Role $role): bool
    {
        return $user instanceof AdminUser;
    }
}
