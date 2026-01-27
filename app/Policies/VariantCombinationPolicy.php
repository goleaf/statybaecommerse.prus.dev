<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AdminUser;
use App\Models\User;
use App\Models\VariantCombination;

final class VariantCombinationPolicy
{
    public function viewAny(AdminUser|User $user): bool
    {
        return $user instanceof AdminUser;
    }

    public function view(AdminUser|User $user, VariantCombination $variantCombination): bool
    {
        return $user instanceof AdminUser;
    }

    public function create(AdminUser|User $user): bool
    {
        return $user instanceof AdminUser;
    }

    public function update(AdminUser|User $user, VariantCombination $variantCombination): bool
    {
        return $user instanceof AdminUser;
    }

    public function delete(AdminUser|User $user, VariantCombination $variantCombination): bool
    {
        return $user instanceof AdminUser;
    }

    public function restore(AdminUser|User $user, VariantCombination $variantCombination): bool
    {
        return $user instanceof AdminUser;
    }

    public function forceDelete(AdminUser|User $user, VariantCombination $variantCombination): bool
    {
        return $user instanceof AdminUser;
    }
}
