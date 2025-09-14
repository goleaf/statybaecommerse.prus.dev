<?php

declare (strict_types=1);
namespace App\Policies;

use App\Models\Address;
use App\Models\User;
use Illuminate\Auth\Access\Response;
/**
 * AddressPolicy
 * 
 * Authorization policy for AddressPolicy access control with comprehensive permission checking and role-based access.
 * 
 */
final class AddressPolicy
{
    /**
     * Handle viewAny functionality with proper error handling.
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return true;
    }
    /**
     * Handle view functionality with proper error handling.
     * @param User $user
     * @param Address $address
     * @return bool
     */
    public function view(User $user, Address $address): bool
    {
        return $user->id === $address->user_id || $user->is_admin;
    }
    /**
     * Show the form for creating a new resource.
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return true;
    }
    /**
     * Update the specified resource in storage with validation.
     * @param User $user
     * @param Address $address
     * @return bool
     */
    public function update(User $user, Address $address): bool
    {
        return $user->id === $address->user_id || $user->is_admin;
    }
    /**
     * Handle delete functionality with proper error handling.
     * @param User $user
     * @param Address $address
     * @return bool
     */
    public function delete(User $user, Address $address): bool
    {
        return $user->id === $address->user_id || $user->is_admin;
    }
    /**
     * Handle restore functionality with proper error handling.
     * @param User $user
     * @param Address $address
     * @return bool
     */
    public function restore(User $user, Address $address): bool
    {
        return $user->is_admin;
    }
    /**
     * Handle forceDelete functionality with proper error handling.
     * @param User $user
     * @param Address $address
     * @return bool
     */
    public function forceDelete(User $user, Address $address): bool
    {
        return $user->is_admin;
    }
}