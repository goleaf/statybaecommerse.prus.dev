<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AdminUser;
use App\Models\Product;
use App\Models\User;

/**
 * Authorization policy for managing products through permissions.
 */
final class ProductPolicy
{
    /**
     * Determine whether the user can view any products.
     */
    public function viewAny(User|AdminUser $user): bool
    {
        return $this->hasPermission($user, 'view_products');
    }

    /**
     * Determine whether the user can view the product.
     */
    public function view(User|AdminUser $user, Product $product): bool
    {
        return $this->hasPermission($user, 'view_products');
    }

    /**
     * Determine whether the user can create products.
     */
    public function create(User|AdminUser $user): bool
    {
        return $this->hasPermission($user, 'create_products');
    }

    /**
     * Determine whether the user can update the product.
     */
    public function update(User|AdminUser $user, Product $product): bool
    {
        return $this->hasPermission($user, 'edit_products');
    }

    /**
     * Determine whether the user can delete the product.
     */
    public function delete(User|AdminUser $user, Product $product): bool
    {
        return $this->hasPermission($user, 'delete_products');
    }

    private function hasPermission(User|AdminUser $user, string $permission): bool
    {
        if (! method_exists($user, 'can')) {
            return false;
        }

        return (bool) $user->can($permission);
    }
}
