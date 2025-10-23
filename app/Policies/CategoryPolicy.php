<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Category;
use App\Models\User;
use App\Policies\Concerns\HandlesRolePermissions;
use Illuminate\Auth\Access\HandlesAuthorization;

final class CategoryPolicy
{
    use HandlesAuthorization;
    use HandlesRolePermissions;

    public function viewAny(User $user): bool
    {
        return $this->allows($user, 'category', 'viewAny');
    }

    public function view(User $user, Category $category): bool
    {
        return $this->allows($user, 'category', 'view');
    }

    public function create(User $user): bool
    {
        return $this->allows($user, 'category', 'create');
    }

    public function update(User $user, Category $category): bool
    {
        return $this->allows($user, 'category', 'update');
    }

    public function delete(User $user, Category $category): bool
    {
        return $this->allows($user, 'category', 'delete');
    }

    public function restore(User $user, Category $category): bool
    {
        return $this->allows($user, 'category', 'restore');
    }
}
