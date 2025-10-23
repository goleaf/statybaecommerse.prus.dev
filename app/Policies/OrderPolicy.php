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
        return $user instanceof AdminUser
            ? AuthorizationMatrix::check('orders', 'viewAny', $user)
            : (bool) ($user->is_admin ?? false);
    }

    public function view(AdminUser|User $user, Order $order): bool
    {
        if ($user instanceof AdminUser) {
            return AuthorizationMatrix::check('orders', 'view', $user);
        }

        if ($user->is_admin ?? false) {
            return true;
        }

        return $order->user_id === $user->getKey();
    }

    public function create(AdminUser $user): bool
    {
        return AuthorizationMatrix::check('orders', 'create', $user);
    }

    public function update(AdminUser|User $user, Order $order): bool
    {
        if ($user instanceof AdminUser) {
            return AuthorizationMatrix::check('orders', 'update', $user);
        }

        if ($user->is_admin ?? false) {
            return true;
        }

        return $order->user_id === $user->getKey();
    }

    public function delete(AdminUser $user, Order $order): bool
    {
        return AuthorizationMatrix::check('orders', 'delete', $user);
    }

    public function restore(AdminUser $user, Order $order): bool
    {
        return AuthorizationMatrix::check('orders', 'update', $user);
    }

    public function forceDelete(AdminUser $user, Order $order): bool
    {
        return AuthorizationMatrix::check('orders', 'delete', $user);
    }
}
