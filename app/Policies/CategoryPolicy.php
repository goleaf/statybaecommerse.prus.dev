<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AdminUser;
use App\Models\Category;
use App\Models\User;
use App\Support\Authorization\AuthorizationMatrix;

final class CategoryPolicy
{
    public function viewAny(AdminUser|User $user): bool
    {
        return AuthorizationMatrix::check('categories', 'viewAny', $user);
    }

    public function view(AdminUser|User $user, Category $category): bool
    {
        return AuthorizationMatrix::check('categories', 'view', $user);
    }

    public function create(AdminUser|User $user): bool
    {
        return AuthorizationMatrix::check('categories', 'create', $user);
    }

    public function update(AdminUser|User $user, Category $category): bool
    {
        return AuthorizationMatrix::check('categories', 'update', $user);
    }

    public function delete(AdminUser|User $user, Category $category): bool
    {
        return AuthorizationMatrix::check('categories', 'delete', $user);
    }

    public function restore(AdminUser|User $user, Category $category): bool
    {
        return AuthorizationMatrix::check('categories', 'update', $user);
    }

    public function forceDelete(AdminUser|User $user, Category $category): bool
    {
        return AuthorizationMatrix::check('categories', 'delete', $user);
    }
}
