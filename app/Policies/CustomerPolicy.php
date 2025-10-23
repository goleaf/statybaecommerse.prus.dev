<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AdminUser;
use App\Models\Customer;
use App\Models\User;

/**
 * Authorization policy for managing customers through permissions.
 */
final class CustomerPolicy
{
    public function viewAny(User|AdminUser $user): bool
    {
        return $this->hasPermission($user, 'view_customers');
    }

    public function view(User|AdminUser $user, Customer $customer): bool
    {
        if ($user instanceof User && ! ($user->is_admin ?? false)) {
            return $user->id === $customer->user_id;
        }

        return $this->hasPermission($user, 'view_customers');
    }

    public function create(User|AdminUser $user): bool
    {
        if ($user instanceof User && ! ($user->is_admin ?? false)) {
            return true;
        }

        return $this->hasPermission($user, 'create_customers');
    }

    public function update(User|AdminUser $user, Customer $customer): bool
    {
        if ($user instanceof User && ! ($user->is_admin ?? false)) {
            return $user->id === $customer->user_id;
        }

        return $this->hasPermission($user, 'edit_customers');
    }

    public function delete(User|AdminUser $user, Customer $customer): bool
    {
        if ($user instanceof User && ! ($user->is_admin ?? false)) {
            return $user->id === $customer->user_id;
        }

        return $this->hasPermission($user, 'delete_customers');
    }

    private function hasPermission(User|AdminUser $user, string $permission): bool
    {
        if (! method_exists($user, 'can')) {
            return false;
        }

        return (bool) $user->can($permission);
    }
}
