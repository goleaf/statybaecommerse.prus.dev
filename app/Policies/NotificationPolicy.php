<?php

declare (strict_types=1);
namespace App\Policies;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
/**
 * NotificationPolicy
 * 
 * Authorization policy for NotificationPolicy access control with comprehensive permission checking and role-based access.
 * 
 */
final class NotificationPolicy
{
    use HandlesAuthorization;
    /**
     * Handle viewAny functionality with proper error handling.
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view notifications') || $user->is_admin;
    }
    /**
     * Handle view functionality with proper error handling.
     * @param User $user
     * @param Notification $notification
     * @return bool
     */
    public function view(User $user, Notification $notification): bool
    {
        // Users can view their own notifications
        if ($notification->notifiable_type === User::class && $notification->notifiable_id === $user->id) {
            return true;
        }
        // Admins can view all notifications
        return $user->hasPermissionTo('view notifications') || $user->is_admin;
    }
    /**
     * Show the form for creating a new resource.
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create notifications') || $user->is_admin;
    }
    /**
     * Update the specified resource in storage with validation.
     * @param User $user
     * @param Notification $notification
     * @return bool
     */
    public function update(User $user, Notification $notification): bool
    {
        // Users can update their own notifications (mark as read/unread)
        if ($notification->notifiable_type === User::class && $notification->notifiable_id === $user->id) {
            return true;
        }
        // Admins can update all notifications
        return $user->hasPermissionTo('update notifications') || $user->is_admin;
    }
    /**
     * Handle delete functionality with proper error handling.
     * @param User $user
     * @param Notification $notification
     * @return bool
     */
    public function delete(User $user, Notification $notification): bool
    {
        // Users can delete their own notifications
        if ($notification->notifiable_type === User::class && $notification->notifiable_id === $user->id) {
            return true;
        }
        // Admins can delete all notifications
        return $user->hasPermissionTo('delete notifications') || $user->is_admin;
    }
    /**
     * Handle restore functionality with proper error handling.
     * @param User $user
     * @param Notification $notification
     * @return bool
     */
    public function restore(User $user, Notification $notification): bool
    {
        return $user->hasPermissionTo('restore notifications') || $user->is_admin;
    }
    /**
     * Handle forceDelete functionality with proper error handling.
     * @param User $user
     * @param Notification $notification
     * @return bool
     */
    public function forceDelete(User $user, Notification $notification): bool
    {
        return $user->hasPermissionTo('force delete notifications') || $user->is_admin;
    }
    /**
     * Handle markAsRead functionality with proper error handling.
     * @param User $user
     * @param Notification $notification
     * @return bool
     */
    public function markAsRead(User $user, Notification $notification): bool
    {
        // Users can mark their own notifications as read
        if ($notification->notifiable_type === User::class && $notification->notifiable_id === $user->id) {
            return true;
        }
        // Admins can mark any notification as read
        return $user->hasPermissionTo('update notifications') || $user->is_admin;
    }
    /**
     * Handle markAsUnread functionality with proper error handling.
     * @param User $user
     * @param Notification $notification
     * @return bool
     */
    public function markAsUnread(User $user, Notification $notification): bool
    {
        // Users can mark their own notifications as unread
        if ($notification->notifiable_type === User::class && $notification->notifiable_id === $user->id) {
            return true;
        }
        // Admins can mark any notification as unread
        return $user->hasPermissionTo('update notifications') || $user->is_admin;
    }
    /**
     * Handle duplicate functionality with proper error handling.
     * @param User $user
     * @param Notification $notification
     * @return bool
     */
    public function duplicate(User $user, Notification $notification): bool
    {
        return $user->hasPermissionTo('create notifications') || $user->is_admin;
    }
    /**
     * Handle bulkUpdate functionality with proper error handling.
     * @param User $user
     * @return bool
     */
    public function bulkUpdate(User $user): bool
    {
        return $user->hasPermissionTo('update notifications') || $user->is_admin;
    }
    /**
     * Handle bulkDelete functionality with proper error handling.
     * @param User $user
     * @return bool
     */
    public function bulkDelete(User $user): bool
    {
        return $user->hasPermissionTo('delete notifications') || $user->is_admin;
    }
}