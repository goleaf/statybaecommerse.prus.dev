<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AdminUser;
use App\Models\Legal;
use App\Models\User;

/**
 * Authorization policy for managing legal documents through permissions.
 */
final class LegalPolicy
{
    public function viewAny(User|AdminUser $user): bool
    {
        return $this->hasPermission($user, 'view_legals');
    }

    public function view(User|AdminUser $user, Legal $legal): bool
    {
        if ($user instanceof User && ! ($user->is_admin ?? false)) {
            return true;
        }

        return $this->hasPermission($user, 'view_legals');
    }

    public function create(User|AdminUser $user): bool
    {
        return $this->hasPermission($user, 'create_legals');
    }

    public function update(User|AdminUser $user, Legal $legal): bool
    {
        return $this->hasPermission($user, 'edit_legals');
    }

    public function delete(User|AdminUser $user, Legal $legal): bool
    {
        return $this->hasPermission($user, 'delete_legals');
    }

    private function hasPermission(User|AdminUser $user, string $permission): bool
    {
        if (! method_exists($user, 'can')) {
            return false;
        }

        return (bool) $user->can($permission);
    }
}
