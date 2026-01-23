<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ProductRequest;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

final class ProductRequestPolicy
{
    use HandlesAuthorization;

    public function viewAny(?AuthenticatableContract $user): bool
    {
        return $user !== null;
    }

    public function view(?AuthenticatableContract $user, ProductRequest $productRequest): bool
    {
        if ($this->isOwner($user, $productRequest)) {
            return true;
        }

        return $user instanceof \App\Models\AdminUser;
    }

    public function create(?AuthenticatableContract $user): bool
    {
        return $user !== null;
    }

    public function update(?AuthenticatableContract $user, ProductRequest $productRequest): bool
    {
        if ($this->isOwner($user, $productRequest)) {
            return true;
        }

        return $user instanceof \App\Models\AdminUser;
    }

    public function delete(?AuthenticatableContract $user, ProductRequest $productRequest): bool
    {
        return $user instanceof \App\Models\AdminUser;
    }

    public function respond(?AuthenticatableContract $user, ProductRequest $productRequest): bool
    {
        return $user instanceof \App\Models\AdminUser;
    }

    private function isOwner(?AuthenticatableContract $user, ProductRequest $productRequest): bool
    {
        return $user instanceof User
            && (string) $productRequest->user_id === (string) $user->getAuthIdentifier();
    }
}
