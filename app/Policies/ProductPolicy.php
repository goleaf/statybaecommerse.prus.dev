<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use App\Policies\Concerns\HandlesRolePermissions;
use Illuminate\Auth\Access\HandlesAuthorization;

final class ProductPolicy
{
    use HandlesAuthorization;
    use HandlesRolePermissions;

    public function viewAny(User $user): bool
    {
        return $this->allows($user, 'product', 'viewAny');
    }

    public function view(User $user, Product $product): bool
    {
        return $this->allows($user, 'product', 'view');
    }

    public function create(User $user): bool
    {
        return $this->allows($user, 'product', 'create');
    }

    public function update(User $user, Product $product): bool
    {
        return $this->allows($user, 'product', 'update');
    }

    public function delete(User $user, Product $product): bool
    {
        return $this->allows($user, 'product', 'delete');
    }

    public function restore(User $user, Product $product): bool
    {
        return $this->allows($user, 'product', 'restore');
    }
}
