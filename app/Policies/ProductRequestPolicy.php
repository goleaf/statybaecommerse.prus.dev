<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ProductRequest;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use App\Support\Authorization\AuthorizationMatrix;

final class ProductRequestPolicy
{
    use HandlesAuthorization;

    public function viewAny(?AuthenticatableContract $user): bool
    {
        return AuthorizationMatrix::check('product_requests', 'viewAny', $user);
    }

    public function view(?AuthenticatableContract $user, ProductRequest $productRequest): bool
    {
        if ($this->isOwner($user, $productRequest)) {
            return true;
        }

        return AuthorizationMatrix::check('product_requests', 'view', $user);
    }

    public function create(?AuthenticatableContract $user): bool
    {
        return AuthorizationMatrix::check('product_requests', 'create', $user);
    }

    public function update(?AuthenticatableContract $user, ProductRequest $productRequest): bool
    {
        if ($this->isOwner($user, $productRequest)) {
            return true;
        }

        return AuthorizationMatrix::check('product_requests', 'update', $user);
    }

    public function delete(?AuthenticatableContract $user, ProductRequest $productRequest): bool
    {
        return AuthorizationMatrix::check('product_requests', 'delete', $user);
    }

    public function respond(?AuthenticatableContract $user, ProductRequest $productRequest): bool
    {
        return AuthorizationMatrix::check('product_requests', 'respond', $user);
    }

    private function isOwner(?AuthenticatableContract $user, ProductRequest $productRequest): bool
    {
        return $user instanceof User
            && (string) $productRequest->user_id === (string) $user->getAuthIdentifier();
    }
}
