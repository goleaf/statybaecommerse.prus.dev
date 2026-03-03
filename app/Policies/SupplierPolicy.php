<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AdminUser;
use App\Models\Supplier;
use App\Models\User;

final class SupplierPolicy
{
    public function viewAny(User|AdminUser $user): bool
    {
        return $this->hasPermission($user, 'view_suppliers');
    }

    public function view(User|AdminUser $user, Supplier $supplier): bool
    {
        return $this->hasPermission($user, 'view_suppliers');
    }

    public function create(User|AdminUser $user): bool
    {
        return $this->hasPermission($user, 'create_suppliers');
    }

    public function update(User|AdminUser $user, Supplier $supplier): bool
    {
        return $this->hasPermission($user, 'edit_suppliers');
    }

    public function delete(User|AdminUser $user, Supplier $supplier): bool
    {
        return $this->hasPermission($user, 'delete_suppliers');
    }

    private function hasPermission(User|AdminUser $user, string $permission): bool
    {
        if (method_exists($user, 'getAttribute') && (bool) $user->getAttribute('is_admin')) {
            return true;
        }

        if (isset($user->is_admin) && (bool) $user->is_admin) {
            return true;
        }

        if (! method_exists($user, 'can')) {
            return false;
        }

        return (bool) $user->can($permission);
    }
}
