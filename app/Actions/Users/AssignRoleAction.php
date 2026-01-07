<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Models\User;

/**
 * Single responsibility action for assigning roles to users
 */
final readonly class AssignRoleAction
{
    public function execute(User $user, string $roleName): User
    {
        // Remove existing roles and assign new one
        $user->syncRoles([$roleName]);

        return $user->fresh();
    }
}
