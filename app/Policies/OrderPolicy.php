<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AdminUser;
use App\Models\Order;
use App\Models\User;

/**
 * Authorization policy for managing orders through permissions while
 * still allowing customers to manage their own purchases.
 */
final class OrderPolicy
{
    public function viewAny(User|AdminUser $user): bool
    {
        if ($this->isBackofficeUser($user)) {
            return $this->hasPermission($user, 'view_orders');
        }

        return true;
    }

    public function view(User|AdminUser $user, Order $order): bool
    {
        if ($this->isBackofficeUser($user)) {
            return $this->hasPermission($user, 'view_orders');
        }

        return $user instanceof User && $order->user_id === $user->id;
    }

    public function create(User|AdminUser $user): bool
    {
        if ($this->isBackofficeUser($user)) {
            return $this->hasPermission($user, 'create_orders');
        }

        return true;
    }

    public function update(User|AdminUser $user, Order $order): bool
    {
        if ($this->isBackofficeUser($user)) {
            return $this->hasPermission($user, 'edit_orders');
        }

        return $user instanceof User && $order->user_id === $user->id;
    }

    public function delete(User|AdminUser $user, Order $order): bool
    {
        if ($this->isBackofficeUser($user)) {
            return $this->hasPermission($user, 'delete_orders');
        }

        return $user instanceof User && $order->user_id === $user->id;
    }

    private function hasPermission(User|AdminUser $user, string $permission): bool
    {
        if (! method_exists($user, 'can')) {
            return false;
        }

        return (bool) $user->can($permission);
    }

    private function isBackofficeUser(User|AdminUser $user): bool
    {
        if ($user instanceof AdminUser) {
            return true;
        }

        return $user instanceof User && (bool) ($user->is_admin ?? false);
    }
}
