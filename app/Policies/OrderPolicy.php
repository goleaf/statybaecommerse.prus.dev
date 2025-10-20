<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use App\Policies\Concerns\HandlesRolePermissions;
use Illuminate\Auth\Access\HandlesAuthorization;

final class OrderPolicy
{
    use HandlesAuthorization;
    use HandlesRolePermissions;

    public function viewAny(User $user): bool
    {
        return $this->allows($user, 'order', 'viewAny');
    }

    public function view(User $user, Order $order): bool
    {
        return $this->allows($user, 'order', 'view');
    }

    public function create(User $user): bool
    {
        return $this->allows($user, 'order', 'create');
    }

    public function update(User $user, Order $order): bool
    {
        return $this->allows($user, 'order', 'update');
    }

    public function delete(User $user, Order $order): bool
    {
        return $this->allows($user, 'order', 'delete');
    }

    public function restore(User $user, Order $order): bool
    {
        return $this->allows($user, 'order', 'restore');
    }
}
