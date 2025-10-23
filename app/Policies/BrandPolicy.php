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
        return AuthorizationMatrix::check('brands', 'viewAny', $user);
    }

    public function view(AdminUser|User $user, Brand $brand): bool
    {
        return AuthorizationMatrix::check('brands', 'view', $user);
    }

    public function create(AdminUser|User $user): bool
    {
        return AuthorizationMatrix::check('brands', 'create', $user);
    }

    public function update(AdminUser|User $user, Brand $brand): bool
    {
        return AuthorizationMatrix::check('brands', 'update', $user);
    }

    public function delete(AdminUser|User $user, Brand $brand): bool
    {
        return AuthorizationMatrix::check('brands', 'delete', $user);
    }

    public function restore(AdminUser|User $user, Brand $brand): bool
    {
        return AuthorizationMatrix::check('brands', 'update', $user);
    }

    public function forceDelete(AdminUser|User $user, Brand $brand): bool
    {
        return AuthorizationMatrix::check('brands', 'delete', $user);
    }
}
