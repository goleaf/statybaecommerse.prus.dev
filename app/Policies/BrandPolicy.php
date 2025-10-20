<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AdminUser;
use App\Models\Brand;
use App\Models\User;

/**
 * Authorization policy for managing brands through permissions.
 */
final class BrandPolicy
{
    public function viewAny(User|AdminUser $user): bool
    {
        return $this->hasPermission($user, 'view_brands');
    }

    public function view(User|AdminUser $user, Brand $brand): bool
    {
        return $this->hasPermission($user, 'view_brands');
    }

    public function create(User|AdminUser $user): bool
    {
        return $this->hasPermission($user, 'create_brands');
    }

    public function update(User|AdminUser $user, Brand $brand): bool
    {
        return $this->hasPermission($user, 'edit_brands');
    }

    public function delete(User|AdminUser $user, Brand $brand): bool
    {
        return $this->hasPermission($user, 'delete_brands');
    }

    private function hasPermission(User|AdminUser $user, string $permission): bool
    {
        if (! method_exists($user, 'can')) {
            return false;
        }

        return (bool) $user->can($permission);
    }
}
