<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AdminUser;
use App\Models\User;

final class UserPolicy
{
    public function viewAny(AdminUser|User $user): bool
    {
        return $user instanceof AdminUser;
    }

    public function view(AdminUser|User $user, User $model): bool
    {
        return $user instanceof AdminUser || $user->is($model);
    }

    public function create(AdminUser|User $user): bool
    {
        return $user instanceof AdminUser;
    }

    public function update(AdminUser|User $user, User $model): bool
    {
        return $user instanceof AdminUser || $user->is($model);
    }

    public function delete(AdminUser|User $user, User $model): bool
    {
        return $user instanceof AdminUser;
    }

    public function restore(AdminUser|User $user, User $model): bool
    {
        return $user instanceof AdminUser;
    }

    public function forceDelete(AdminUser|User $user, User $model): bool
    {
        return $user instanceof AdminUser;
    }
}
