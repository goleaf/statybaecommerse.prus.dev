<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\VariantCombination;
use App\Support\Authorization\AuthorizationMatrix;

/**
 * Policy for VariantCombination model authorization.
 * 
 * This policy integrates with the AuthorizationMatrix to provide
 * consistent authorization across the application.
 */
class VariantCombinationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return AuthorizationMatrix::check('products', 'viewAny', $user);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, VariantCombination $variantCombination): bool
    {
        return AuthorizationMatrix::check('products', 'view', $user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return AuthorizationMatrix::check('products', 'create', $user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, VariantCombination $variantCombination): bool
    {
        return AuthorizationMatrix::check('products', 'update', $user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, VariantCombination $variantCombination): bool
    {
        return AuthorizationMatrix::check('products', 'delete', $user);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, VariantCombination $variantCombination): bool
    {
        return AuthorizationMatrix::check('products', 'restore', $user);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, VariantCombination $variantCombination): bool
    {
        return AuthorizationMatrix::check('products', 'delete', $user);
    }

    /**
     * Determine whether the user can replicate the model.
     */
    public function replicate(User $user, VariantCombination $variantCombination): bool
    {
        return AuthorizationMatrix::check('products', 'create', $user);
    }

    /**
     * Determine whether the user can reorder models.
     */
    public function reorder(User $user): bool
    {
        return AuthorizationMatrix::check('products', 'update', $user);
    }
}