<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Address;
use App\Models\User;
use Illuminate\Auth\Access\Response;

final class AddressPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Address $address): bool
    {
        return $user->id === $address->user_id || $user->is_admin;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Address $address): bool
    {
        return $user->id === $address->user_id || $user->is_admin;
    }

    public function delete(User $user, Address $address): bool
    {
        return $user->id === $address->user_id || $user->is_admin;
    }

    public function restore(User $user, Address $address): bool
    {
        return $user->is_admin;
    }

    public function forceDelete(User $user, Address $address): bool
    {
        return $user->is_admin;
    }
}
