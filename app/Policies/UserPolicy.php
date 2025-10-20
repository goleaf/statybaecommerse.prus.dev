<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AdminUser;
use App\Models\User;
use App\Support\Authorization\AuthorizationMatrix;

final class UserPolicy
{
    public function viewAny(AdminUser|User $user): bool
    {
        return $user instanceof AdminUser
            ? AuthorizationMatrix::check('users', 'viewAny', $user)
            : (bool) ($user->is_admin ?? false);
    }

    public function view(AdminUser|User $user, User $model): bool
    {
        if ($user instanceof AdminUser) {
            return AuthorizationMatrix::check('users', 'view', $user);
        }

        if ($user->is_admin ?? false) {
            return true;
        }

        return $user->is($model);
    }

    public function create(AdminUser $user): bool
    {
        return AuthorizationMatrix::check('users', 'create', $user);
    }

    public function update(AdminUser|User $user, User $model): bool
    {
        if ($user instanceof AdminUser) {
            return AuthorizationMatrix::check('users', 'update', $user);
        }

        if ($user->is_admin ?? false) {
            return true;
        }

        return $user->is($model);
    }

    public function delete(AdminUser $user, User $model): bool
    {
        return AuthorizationMatrix::check('users', 'delete', $user);
    }

    public function restore(AdminUser $user, User $model): bool
    {
        return AuthorizationMatrix::check('users', 'update', $user);
    }

    public function forceDelete(AdminUser $user, User $model): bool
    {
        return AuthorizationMatrix::check('users', 'delete', $user);
    }
}
