<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Spatie\Permission\Models\Role;
use App\Support\Authorization\AuthorizationMatrix;

class RolePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthenticatableContract $authUser): bool
    {
        return AuthorizationMatrix::check('roles', 'viewAny', $authUser);
    }

    public function view(AuthenticatableContract $authUser, Role $role): bool
    {
        return AuthorizationMatrix::check('roles', 'view', $authUser);
    }

    public function create(AuthenticatableContract $authUser): bool
    {
        return AuthorizationMatrix::check('roles', 'create', $authUser);
    }

    public function update(AuthenticatableContract $authUser, Role $role): bool
    {
        return AuthorizationMatrix::check('roles', 'update', $authUser);
    }

    public function delete(AuthenticatableContract $authUser, Role $role): bool
    {
        return AuthorizationMatrix::check('roles', 'delete', $authUser);
    }

    public function restore(AuthenticatableContract $authUser, Role $role): bool
    {
        return AuthorizationMatrix::check('roles', 'restore', $authUser);
    }

    public function forceDelete(AuthenticatableContract $authUser, Role $role): bool
    {
        return AuthorizationMatrix::check('roles', 'forceDelete', $authUser);
    }

    public function forceDeleteAny(AuthenticatableContract $authUser): bool
    {
        return AuthorizationMatrix::check('roles', 'forceDeleteAny', $authUser);
    }

    public function restoreAny(AuthenticatableContract $authUser): bool
    {
        return AuthorizationMatrix::check('roles', 'restoreAny', $authUser);
    }

    public function replicate(AuthenticatableContract $authUser, Role $role): bool
    {
        return AuthorizationMatrix::check('roles', 'replicate', $authUser);
    }

    public function reorder(AuthenticatableContract $authUser): bool
    {
        return AuthorizationMatrix::check('roles', 'reorder', $authUser);
    }
}
