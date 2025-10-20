<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AdminUser;
use App\Models\Product;
use App\Models\User;
use App\Support\Authorization\AuthorizationMatrix;

final class ProductPolicy
{
    public function viewAny(AdminUser|User $user): bool
    {
        if (! $user instanceof AdminUser) {
            return false;
        }

        return AuthorizationMatrix::check('products', 'viewAny', $user);
    }

    public function view(AdminUser|User $user, Product $product): bool
    {
        if (! $user instanceof AdminUser) {
            return false;
        }

        return AuthorizationMatrix::check('products', 'view', $user);
    }

    public function create(AdminUser $user): bool
    {
        return AuthorizationMatrix::check('products', 'create', $user);
    }

    public function update(AdminUser $user, Product $product): bool
    {
        return AuthorizationMatrix::check('products', 'update', $user);
    }

    public function delete(AdminUser $user, Product $product): bool
    {
        return AuthorizationMatrix::check('products', 'delete', $user);
    }

    public function restore(AdminUser $user, Product $product): bool
    {
        return AuthorizationMatrix::check('products', 'update', $user);
    }

    public function forceDelete(AdminUser $user, Product $product): bool
    {
        return AuthorizationMatrix::check('products', 'delete', $user);
    }
}
