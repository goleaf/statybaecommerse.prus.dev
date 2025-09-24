<?php declare(strict_types=1);

namespace App\Policies;

use App\Models\AdminUser;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

/**
 * NotificationPolicy
 *
 * Authorization policy for NotificationPolicy access control with comprehensive permission checking and role-based access.
 */
final class NotificationPolicy
{
    use HandlesAuthorization;

    /**
     * Handle viewAny functionality with proper error handling.
     */
    public function viewAny(?AuthenticatableContract $user): bool
    {
        return $this->canAdminister($user, 'view notifications');
    }

    /**
     * Handle view functionality with proper error handling.
     */
    public function view(?AuthenticatableContract $user, Notification $notification): bool
    {
        // Users can view their own notifications
        if ($user instanceof User && $notification->notifiable_type === User::class && $notification->notifiable_id === $user->id) {
            return true;
        }

        // Admins can view all notifications
        return $this->canAdminister($user, 'view notifications');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(?AuthenticatableContract $user): bool
    {
        return $this->canAdminister($user, 'create notifications');
    }

    /**
     * Update the specified resource in storage with validation.
     */
    public function update(?AuthenticatableContract $user, Notification $notification): bool
    {
        // Users can update their own notifications (mark as read/unread)
        if ($user instanceof User && $notification->notifiable_type === User::class && $notification->notifiable_id === $user->id) {
            return true;
        }

        // Admins can update all notifications
        return $this->canAdminister($user, 'update notifications');
    }

    /**
     * Handle delete functionality with proper error handling.
     */
    public function delete(?AuthenticatableContract $user, Notification $notification): bool
    {
        // Users can delete their own notifications
        if ($user instanceof User && $notification->notifiable_type === User::class && $notification->notifiable_id === $user->id) {
            return true;
        }

        // Admins can delete all notifications
        return $this->canAdminister($user, 'delete notifications');
    }

    /**
     * Handle restore functionality with proper error handling.
     */
    public function restore(?AuthenticatableContract $user, Notification $notification): bool
    {
        return $this->canAdminister($user, 'restore notifications');
    }

    /**
     * Handle forceDelete functionality with proper error handling.
     */
    public function forceDelete(?AuthenticatableContract $user, Notification $notification): bool
    {
        return $this->canAdminister($user, 'force delete notifications');
    }

    /**
     * Handle markAsRead functionality with proper error handling.
     */
    public function markAsRead(?AuthenticatableContract $user, Notification $notification): bool
    {
        // Users can mark their own notifications as read
        if ($user instanceof User && $notification->notifiable_type === User::class && $notification->notifiable_id === $user->id) {
            return true;
        }

        // Admins can mark any notification as read
        return $this->canAdminister($user, 'update notifications');
    }

    /**
     * Handle markAsUnread functionality with proper error handling.
     */
    public function markAsUnread(?AuthenticatableContract $user, Notification $notification): bool
    {
        // Users can mark their own notifications as unread
        if ($user instanceof User && $notification->notifiable_type === User::class && $notification->notifiable_id === $user->id) {
            return true;
        }

        // Admins can mark any notification as unread
        return $this->canAdminister($user, 'update notifications');
    }

    /**
     * Handle duplicate functionality with proper error handling.
     */
    public function duplicate(?AuthenticatableContract $user, Notification $notification): bool
    {
        return $this->canAdminister($user, 'create notifications');
    }

    /**
     * Handle bulkUpdate functionality with proper error handling.
     */
    public function bulkUpdate(?AuthenticatableContract $user): bool
    {
        return $this->canAdminister($user, 'update notifications');
    }

    /**
     * Handle bulkDelete functionality with proper error handling.
     */
    public function bulkDelete(?AuthenticatableContract $user): bool
    {
        return $this->canAdminister($user, 'delete notifications');
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
            // Allow admin guard users by default
            return true;
        }

        return false;
    }
}
