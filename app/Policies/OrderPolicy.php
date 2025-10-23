<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AdminUser;
use App\Models\Order;
use App\Models\User;
use App\Support\Authorization\AuthorizationMatrix;

final class OrderPolicy
{
    public function viewAny(AdminUser|User $user): bool
    {
        return AuthorizationMatrix::check('orders', 'viewAny', $user);
    }

    public function view(AdminUser|User $user, Order $order): bool
    {
        if ($user instanceof AdminUser) {
            return AuthorizationMatrix::check('orders', 'view', $user);
        }

        if (AuthorizationMatrix::check('orders', 'view', $user)) {
            return true;
        }

        return $order->user_id === $user->getKey();
    }

    public function create(AdminUser|User $user): bool
    {
        return AuthorizationMatrix::check('orders', 'create', $user);
    }

    public function update(AdminUser|User $user, Order $order): bool
    {
        if ($user instanceof AdminUser) {
            return AuthorizationMatrix::check('orders', 'update', $user);
        }

        if (AuthorizationMatrix::check('orders', 'update', $user)) {
            return true;
        }

        return $order->user_id === $user->getKey();
    }

    public function delete(AdminUser|User $user, Order $order): bool
    {
        return AuthorizationMatrix::check('orders', 'delete', $user);
    }

    public function restore(AdminUser|User $user, Order $order): bool
    {
        return AuthorizationMatrix::check('orders', 'update', $user);
    }

    public function forceDelete(AdminUser|User $user, Order $order): bool
    {
        return AuthorizationMatrix::check('orders', 'delete', $user);
    }
}
