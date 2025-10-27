<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use App\Support\Authorization\AuthorizationMatrix;

final class NotificationPolicy
{
    use HandlesAuthorization;

    public function viewAny(?AuthenticatableContract $user): bool
    {
        if ($user instanceof User) {
            return true;
        }

        return AuthorizationMatrix::check('notifications', 'viewAny', $user);
    }

    public function view(?AuthenticatableContract $user, Notification $notification): bool
    {
        if ($this->isOwner($user, $notification)) {
            return true;
        }

        return AuthorizationMatrix::check('notifications', 'view', $user);
    }

    public function create(?AuthenticatableContract $user): bool
    {
        return AuthorizationMatrix::check('notifications', 'create', $user);
    }

    public function update(?AuthenticatableContract $user, Notification $notification): bool
    {
        if ($this->isOwner($user, $notification)) {
            return true;
        }

        return AuthorizationMatrix::check('notifications', 'update', $user);
    }

    public function delete(?AuthenticatableContract $user, Notification $notification): bool
    {
        if ($this->isOwner($user, $notification)) {
            return true;
        }

        return AuthorizationMatrix::check('notifications', 'delete', $user);
    }

    public function restore(?AuthenticatableContract $user, Notification $notification): bool
    {
        return AuthorizationMatrix::check('notifications', 'update', $user);
    }

    public function forceDelete(?AuthenticatableContract $user, Notification $notification): bool
    {
        return AuthorizationMatrix::check('notifications', 'delete', $user);
    }

    public function markAsRead(?AuthenticatableContract $user, Notification $notification): bool
    {
        if ($this->isOwner($user, $notification)) {
            return true;
        }

        return AuthorizationMatrix::check('notifications', 'markAsRead', $user);
    }

    public function markAsUnread(?AuthenticatableContract $user, Notification $notification): bool
    {
        if ($this->isOwner($user, $notification)) {
            return true;
        }

        return AuthorizationMatrix::check('notifications', 'markAsUnread', $user);
    }

    public function duplicate(?AuthenticatableContract $user, Notification $notification): bool
    {
        return AuthorizationMatrix::check('notifications', 'duplicate', $user);
    }

    public function bulkUpdate(?AuthenticatableContract $user): bool
    {
        return AuthorizationMatrix::check('notifications', 'bulkUpdate', $user);
    }

    public function bulkDelete(?AuthenticatableContract $user): bool
    {
        return AuthorizationMatrix::check('notifications', 'bulkDelete', $user);
    }

    private function isOwner(?AuthenticatableContract $user, Notification $notification): bool
    {
        return $user instanceof User
            && $notification->notifiable_type === User::class
            && (string) $notification->notifiable_id === (string) $user->getAuthIdentifier();
    }
}
