<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

final class NotificationPolicy
{
    use HandlesAuthorization;

    public function viewAny(?AuthenticatableContract $user): bool
    {
        return $user !== null;
    }

    public function view(?AuthenticatableContract $user, Notification $notification): bool
    {
        if ($this->isOwner($user, $notification)) {
            return true;
        }

        return $user instanceof \App\Models\AdminUser;
    }

    public function create(?AuthenticatableContract $user): bool
    {
        return $user instanceof \App\Models\AdminUser;
    }

    public function update(?AuthenticatableContract $user, Notification $notification): bool
    {
        if ($this->isOwner($user, $notification)) {
            return true;
        }

        return $user instanceof \App\Models\AdminUser;
    }

    public function delete(?AuthenticatableContract $user, Notification $notification): bool
    {
        if ($this->isOwner($user, $notification)) {
            return true;
        }

        return $user instanceof \App\Models\AdminUser;
    }

    public function restore(?AuthenticatableContract $user, Notification $notification): bool
    {
        return $user instanceof \App\Models\AdminUser;
    }

    public function forceDelete(?AuthenticatableContract $user, Notification $notification): bool
    {
        return $user instanceof \App\Models\AdminUser;
    }

    public function markAsRead(?AuthenticatableContract $user, Notification $notification): bool
    {
        if ($this->isOwner($user, $notification)) {
            return true;
        }

        return $user instanceof \App\Models\AdminUser;
    }

    public function markAsUnread(?AuthenticatableContract $user, Notification $notification): bool
    {
        if ($this->isOwner($user, $notification)) {
            return true;
        }

        return $user instanceof \App\Models\AdminUser;
    }

    public function duplicate(?AuthenticatableContract $user, Notification $notification): bool
    {
        return $user instanceof \App\Models\AdminUser;
    }

    public function bulkUpdate(?AuthenticatableContract $user): bool
    {
        return $user instanceof \App\Models\AdminUser;
    }

    public function bulkDelete(?AuthenticatableContract $user): bool
    {
        return $user instanceof \App\Models\AdminUser;
    }

    private function isOwner(?AuthenticatableContract $user, Notification $notification): bool
    {
        return $user instanceof User
            && $notification->notifiable_type === User::class
            && (string) $notification->notifiable_id === (string) $user->getAuthIdentifier();
    }
}
