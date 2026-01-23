<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AdminUser;
use App\Models\Order;
use App\Models\User;

final class OrderPolicy
{
    public function viewAny(AdminUser|User $user): bool
    {
        return true;
    }

    public function view(AdminUser|User $user, Order $order): bool
    {
        return $user instanceof AdminUser || $order->user_id === $user->getKey();
    }

    public function create(AdminUser|User $user): bool
    {
        return true;
    }

    public function update(AdminUser|User $user, Order $order): bool
    {
        return $user instanceof AdminUser || $order->user_id === $user->getKey();
    }

    public function delete(AdminUser|User $user, Order $order): bool
    {
        return $user instanceof AdminUser;
    }

    public function restore(AdminUser|User $user, Order $order): bool
    {
        return $user instanceof AdminUser;
    }

    public function forceDelete(AdminUser|User $user, Order $order): bool
    {
        return $user instanceof AdminUser;
    }
}
