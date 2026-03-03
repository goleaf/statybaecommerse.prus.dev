<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AdminUser;
use App\Models\Brochure;
use App\Models\User;

final class BrochurePolicy
{
    public function viewAny(User|AdminUser $user): bool
    {
        return $this->hasPermission($user, 'view_brochures');
    }

    public function view(User|AdminUser $user, Brochure $brochure): bool
    {
        return $this->hasPermission($user, 'view_brochures');
    }

    public function create(User|AdminUser $user): bool
    {
        return $this->hasPermission($user, 'create_brochures');
    }

    public function update(User|AdminUser $user, Brochure $brochure): bool
    {
        return $this->hasPermission($user, 'edit_brochures');
    }

    public function delete(User|AdminUser $user, Brochure $brochure): bool
    {
        return $this->hasPermission($user, 'delete_brochures');
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
