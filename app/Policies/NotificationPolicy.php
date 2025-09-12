<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

final class NotificationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any notifications.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view notifications') || $user->is_admin;
    }

    /**
     * Determine whether the user can view the notification.
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
     * Determine whether the user can create notifications.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create notifications') || $user->is_admin;
    }

    /**
     * Determine whether the user can update the notification.
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
     * Determine whether the user can delete the notification.
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
     * Determine whether the user can restore the notification.
     */
    public function restore(User $user, Notification $notification): bool
    {
        return $user->hasPermissionTo('restore notifications') || $user->is_admin;
    }

    /**
     * Determine whether the user can permanently delete the notification.
     */
    public function forceDelete(User $user, Notification $notification): bool
    {
        return $user->hasPermissionTo('force delete notifications') || $user->is_admin;
    }

    /**
     * Determine whether the user can mark notification as read.
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
     * Determine whether the user can mark notification as unread.
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
     * Determine whether the user can duplicate the notification.
     */
    public function duplicate(User $user, Notification $notification): bool
    {
        return $user->hasPermissionTo('create notifications') || $user->is_admin;
    }

    /**
     * Determine whether the user can bulk update notifications.
     */
    public function bulkUpdate(User $user): bool
    {
        return $user->hasPermissionTo('update notifications') || $user->is_admin;
    }

    /**
     * Determine whether the user can bulk delete notifications.
     */
    public function bulkDelete(User $user): bool
    {
        return $user->hasPermissionTo('delete notifications') || $user->is_admin;
    }
}
