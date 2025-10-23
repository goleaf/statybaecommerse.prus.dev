<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Brand;
use App\Models\User;
use App\Policies\Concerns\HandlesRolePermissions;
use Illuminate\Auth\Access\HandlesAuthorization;

final class BrandPolicy
{
    use HandlesAuthorization;
    use HandlesRolePermissions;

    public function viewAny(User $user): bool
    {
        return $this->allows($user, 'brand', 'viewAny');
    }

    public function view(User $user, Brand $brand): bool
    {
        return $this->allows($user, 'brand', 'view');
    }

    public function create(User $user): bool
    {
        return $this->allows($user, 'brand', 'create');
    }

    public function update(User $user, Brand $brand): bool
    {
        return $this->allows($user, 'brand', 'update');
    }

    public function delete(User $user, Brand $brand): bool
    {
        return $this->allows($user, 'brand', 'delete');
    }

    public function restore(User $user, Brand $brand): bool
    {
        return $this->allows($user, 'brand', 'restore');
    }
}
