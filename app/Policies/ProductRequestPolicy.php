<?php

declare (strict_types=1);
namespace App\Policies;

use App\Models\ProductRequest;
use App\Models\User;
use Illuminate\Auth\Access\Response;
/**
 * ProductRequestPolicy
 * 
 * Authorization policy for ProductRequestPolicy access control with comprehensive permission checking and role-based access.
 * 
 */
final class ProductRequestPolicy
{
    /**
     * Handle viewAny functionality with proper error handling.
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->is_admin || $user->hasPermissionTo('view_product_requests');
    }
    /**
     * Handle view functionality with proper error handling.
     * @param User $user
     * @param ProductRequest $productRequest
     * @return Response
     */
    public function view(User $user, ProductRequest $productRequest): Response
    {
        return ($user->is_admin || $user->hasPermissionTo('view_product_requests') || $productRequest->user_id === $user->id)
            ? Response::allow()
            : Response::deny(__('policy.product_request.view_denied'));
    }
    /**
     * Show the form for creating a new resource.
     * @param User $user
     * @return Response
     */
    public function create(User $user): Response
    {
        return ($user->is_admin || $user->hasPermissionTo('create_product_requests'))
            ? Response::allow()
            : Response::deny(__('policy.product_request.create_denied'));
    }
    /**
     * Update the specified resource in storage with validation.
     * @param User $user
     * @param ProductRequest $productRequest
     * @return Response
     */
    public function update(User $user, ProductRequest $productRequest): Response
    {
        return ($user->is_admin || $user->hasPermissionTo('update_product_requests') || $productRequest->user_id === $user->id)
            ? Response::allow()
            : Response::deny(__('policy.product_request.update_denied'));
    }
    /**
     * Handle delete functionality with proper error handling.
     * @param User $user
     * @param ProductRequest $productRequest
     * @return Response
     */
    public function delete(User $user, ProductRequest $productRequest): Response
    {
        return ($user->is_admin || $user->hasPermissionTo('delete_product_requests'))
            ? Response::allow()
            : Response::deny(__('policy.product_request.delete_denied'));
    }
    /**
     * Handle respond functionality with proper error handling.
     * @param User $user
     * @param ProductRequest $productRequest
     * @return Response
     */
    public function respond(User $user, ProductRequest $productRequest): Response
    {
        return ($user->is_admin || $user->hasPermissionTo('respond_product_requests'))
            ? Response::allow()
            : Response::deny(__('policy.product_request.respond_denied'));
    }
}