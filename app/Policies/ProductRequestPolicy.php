<?php declare(strict_types=1);

namespace App\Policies;

use App\Models\ProductRequest;
use App\Models\User;

final class ProductRequestPolicy
{
    /**
     * Determine whether the user can view any product requests.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_admin || $user->hasPermissionTo('view_product_requests');
    }

    /**
     * Determine whether the user can view the product request.
     */
    public function view(User $user, ProductRequest $productRequest): bool
    {
        return $user->is_admin || 
               $user->hasPermissionTo('view_product_requests') ||
               $productRequest->user_id === $user->id;
    }

    /**
     * Determine whether the user can create product requests.
     */
    public function create(User $user): bool
    {
        return $user->is_admin || $user->hasPermissionTo('create_product_requests');
    }

    /**
     * Determine whether the user can update the product request.
     */
    public function update(User $user, ProductRequest $productRequest): bool
    {
        return $user->is_admin || 
               $user->hasPermissionTo('update_product_requests') ||
               $productRequest->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the product request.
     */
    public function delete(User $user, ProductRequest $productRequest): bool
    {
        return $user->is_admin || $user->hasPermissionTo('delete_product_requests');
    }

    /**
     * Determine whether the user can respond to the product request.
     */
    public function respond(User $user, ProductRequest $productRequest): bool
    {
        return $user->is_admin || $user->hasPermissionTo('respond_product_requests');
    }
}

