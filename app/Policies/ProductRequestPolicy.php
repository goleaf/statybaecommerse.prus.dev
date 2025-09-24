<?php declare(strict_types=1);

namespace App\Policies;

use App\Models\AdminUser;
use App\Models\ProductRequest;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

/**
 * ProductRequestPolicy
 *
 * Authorization policy for ProductRequestPolicy access control with comprehensive permission checking and role-based access.
 */
final class ProductRequestPolicy
{
    use HandlesAuthorization;

    /**
     * Handle viewAny functionality with proper error handling.
     */
    public function viewAny(?AuthenticatableContract $user): bool
    {
        return $this->canAdminister($user, 'view_product_requests');
    }

    /**
     * Handle view functionality with proper error handling.
     */
    public function view(?AuthenticatableContract $user, ProductRequest $productRequest): bool
    {
        if ($user instanceof User) {
            $hasPermission = false;
            if (method_exists($user, 'hasPermissionTo')) {
                try {
                    $hasPermission = (bool) $user->hasPermissionTo('view_product_requests');
                } catch (PermissionDoesNotExist $e) {
                    $hasPermission = false;
                }
            }

            return ($user->is_admin || $hasPermission || $productRequest->user_id === $user->id);
        }

        return $this->canAdminister($user, 'view_product_requests');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(?AuthenticatableContract $user): bool
    {
        return $this->canAdminister($user, 'create_product_requests');
    }

    /**
     * Update the specified resource in storage with validation.
     */
    public function update(?AuthenticatableContract $user, ProductRequest $productRequest): bool
    {
        if ($user instanceof User) {
            $hasPermission = false;
            if (method_exists($user, 'hasPermissionTo')) {
                try {
                    $hasPermission = (bool) $user->hasPermissionTo('update_product_requests');
                } catch (PermissionDoesNotExist $e) {
                    $hasPermission = false;
                }
            }

            return ($user->is_admin || $hasPermission || $productRequest->user_id === $user->id);
        }

        return $this->canAdminister($user, 'update_product_requests');
    }

    /**
     * Handle delete functionality with proper error handling.
     */
    public function delete(?AuthenticatableContract $user, ProductRequest $productRequest): bool
    {
        return $this->canAdminister($user, 'delete_product_requests');
    }

    /**
     * Handle respond functionality with proper error handling.
     */
    public function respond(?AuthenticatableContract $user, ProductRequest $productRequest): bool
    {
        return $this->canAdminister($user, 'respond_product_requests');
    }

    private function canAdminister(?AuthenticatableContract $user, string $permission): bool
    {
        if (!$user) {
            return false;
        }

        if ($user instanceof User) {
            $hasPermission = false;
            if (method_exists($user, 'hasPermissionTo')) {
                try {
                    $hasPermission = (bool) $user->hasPermissionTo($permission);
                } catch (PermissionDoesNotExist $e) {
                    $hasPermission = false;
                }
            }
            $isAdmin = (bool) ($user->is_admin ?? false);

            return $hasPermission || $isAdmin;
        }

        if ($user instanceof AdminUser) {
            return true;
        }

        return false;
    }
}
