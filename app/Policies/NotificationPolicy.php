<?php

declare (strict_types=1);
namespace App\Policies;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
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
     * @return Response
     */
    public function viewAny(User $user): Response
    {
        return ($user->hasPermissionTo('view notifications') || $user->is_admin)
            ? Response::allow()
            : Response::deny(__('policy.notification.view_any_denied'));
    }
    /**
     * Handle view functionality with proper error handling.
     * @param User $user
     * @param Notification $notification
     * @return Response
     */
    public function view(User $user, Notification $notification): Response
    {
        if ($notification->notifiable_type === User::class && $notification->notifiable_id === $user->id) {
            return Response::allow();
        }
        return ($user->hasPermissionTo('view notifications') || $user->is_admin)
            ? Response::allow()
            : Response::deny(__('policy.notification.view_denied'));
    }
    /**
     * Show the form for creating a new resource.
     * @param User $user
     * @return Response
     */
    public function create(User $user): Response
    {
        return ($user->hasPermissionTo('create notifications') || $user->is_admin)
            ? Response::allow()
            : Response::deny(__('policy.notification.create_denied'));
    }
    /**
     * Update the specified resource in storage with validation.
     * @param User $user
     * @param Notification $notification
     * @return Response
     */
    public function update(User $user, Notification $notification): Response
    {
        if ($notification->notifiable_type === User::class && $notification->notifiable_id === $user->id) {
            return Response::allow();
        }
        return ($user->hasPermissionTo('update notifications') || $user->is_admin)
            ? Response::allow()
            : Response::deny(__('policy.notification.update_denied'));
    }
    /**
     * Handle delete functionality with proper error handling.
     * @param User $user
     * @param Notification $notification
     * @return Response
     */
    public function delete(User $user, Notification $notification): Response
    {
        if ($notification->notifiable_type === User::class && $notification->notifiable_id === $user->id) {
            return Response::allow();
        }
        return ($user->hasPermissionTo('delete notifications') || $user->is_admin)
            ? Response::allow()
            : Response::deny(__('policy.notification.delete_denied'));
    }
    /**
     * Handle restore functionality with proper error handling.
     * @param User $user
     * @param Notification $notification
     * @return Response
     */
    public function restore(User $user, Notification $notification): Response
    {
        return ($user->hasPermissionTo('restore notifications') || $user->is_admin)
            ? Response::allow()
            : Response::deny(__('policy.notification.restore_denied'));
    }
    /**
     * Handle forceDelete functionality with proper error handling.
     * @param User $user
     * @param Notification $notification
     * @return Response
     */
    public function forceDelete(User $user, Notification $notification): Response
    {
        return ($user->hasPermissionTo('force delete notifications') || $user->is_admin)
            ? Response::allow()
            : Response::deny(__('policy.notification.force_delete_denied'));
    }
    /**
     * Handle markAsRead functionality with proper error handling.
     * @param User $user
     * @param Notification $notification
     * @return Response
     */
    public function markAsRead(User $user, Notification $notification): Response
    {
        if ($notification->notifiable_type === User::class && $notification->notifiable_id === $user->id) {
            return Response::allow();
        }
        return ($user->hasPermissionTo('update notifications') || $user->is_admin)
            ? Response::allow()
            : Response::deny(__('policy.notification.mark_as_read_denied'));
    }
    /**
     * Handle markAsUnread functionality with proper error handling.
     * @param User $user
     * @param Notification $notification
     * @return Response
     */
    public function markAsUnread(User $user, Notification $notification): Response
    {
        if ($notification->notifiable_type === User::class && $notification->notifiable_id === $user->id) {
            return Response::allow();
        }
        return ($user->hasPermissionTo('update notifications') || $user->is_admin)
            ? Response::allow()
            : Response::deny(__('policy.notification.mark_as_unread_denied'));
    }
    /**
     * Handle duplicate functionality with proper error handling.
     * @param User $user
     * @param Notification $notification
     * @return Response
     */
    public function duplicate(User $user, Notification $notification): Response
    {
        return ($user->hasPermissionTo('create notifications') || $user->is_admin)
            ? Response::allow()
            : Response::deny(__('policy.notification.duplicate_denied'));
    }
    /**
     * Handle bulkUpdate functionality with proper error handling.
     * @param User $user
     * @return Response
     */
    public function bulkUpdate(User $user): Response
    {
        return ($user->hasPermissionTo('update notifications') || $user->is_admin)
            ? Response::allow()
            : Response::deny(__('policy.notification.bulk_update_denied'));
    }
    /**
     * Handle bulkDelete functionality with proper error handling.
     * @param User $user
     * @return Response
     */
    public function bulkDelete(User $user): Response
    {
        return ($user->hasPermissionTo('delete notifications') || $user->is_admin)
            ? Response::allow()
            : Response::deny(__('policy.notification.bulk_delete_denied'));
    }
}