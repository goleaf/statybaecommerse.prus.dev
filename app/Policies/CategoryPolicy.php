<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AdminUser;
use App\Models\Category;
use App\Models\User;

/**
 * Authorization policy for managing categories through permissions.
 */
final class CategoryPolicy
{
    public function viewAny(User|AdminUser $user): bool
    {
        return $this->hasPermission($user, 'view_categories');
    }

    public function view(User|AdminUser $user, Category $category): bool
    {
        return $this->hasPermission($user, 'view_categories');
    }

    public function create(User|AdminUser $user): bool
    {
        return $this->hasPermission($user, 'create_categories');
    }

    public function update(User|AdminUser $user, Category $category): bool
    {
        return $this->hasPermission($user, 'edit_categories');
    }

    public function delete(User|AdminUser $user, Category $category): bool
    {
        return $this->hasPermission($user, 'delete_categories');
    }

    private function hasPermission(User|AdminUser $user, string $permission): bool
    {
        if (! method_exists($user, 'can')) {
            return false;
        }

        return (bool) $user->can($permission);
    }
}
