<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AdminUser;
use App\Models\Brand;
use App\Models\User;
use App\Support\Authorization\AuthorizationMatrix;

final class BrandPolicy
{
    public function viewAny(AdminUser|User $user): bool
    {
        return $user instanceof AdminUser
            ? AuthorizationMatrix::check('brands', 'viewAny', $user)
            : (bool) ($user->is_admin ?? false);
    }

    public function view(AdminUser|User $user, Brand $brand): bool
    {
        return $user instanceof AdminUser
            ? AuthorizationMatrix::check('brands', 'view', $user)
            : (bool) ($user->is_admin ?? false);
    }

    public function create(AdminUser $user): bool
    {
        return AuthorizationMatrix::check('brands', 'create', $user);
    }

    public function update(AdminUser $user, Brand $brand): bool
    {
        return AuthorizationMatrix::check('brands', 'update', $user);
    }

    public function delete(AdminUser $user, Brand $brand): bool
    {
        return AuthorizationMatrix::check('brands', 'delete', $user);
    }

    public function restore(AdminUser $user, Brand $brand): bool
    {
        return AuthorizationMatrix::check('brands', 'update', $user);
    }

    public function forceDelete(AdminUser $user, Brand $brand): bool
    {
        return AuthorizationMatrix::check('brands', 'delete', $user);
    }
}
