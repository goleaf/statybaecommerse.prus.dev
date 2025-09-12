<?php declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Notifications\TestNotification;
use Illuminate\Support\Collection;

final class NotificationService
{
    public function sendToAdmins(string $title, string $message, string $type = 'info'): void
    {
        $adminUsers = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['administrator', 'manager']);
        })->get();

        foreach ($adminUsers as $user) {
            $user->notify(new TestNotification($title, $message, $type));
        }
    }

    public function sendToUser(User $user, string $title, string $message, string $type = 'info'): void
    {
        $user->notify(new TestNotification($title, $message, $type));
    }

    public function sendToUsers(Collection $users, string $title, string $message, string $type = 'info'): void
    {
        foreach ($users as $user) {
            $user->notify(new TestNotification($title, $message, $type));
        }
    }

    public function getUnreadCount(User $user): int
    {
        return $user->unreadNotifications()->count();
    }

    public function markAllAsRead(User $user): void
    {
        $user->unreadNotifications()->update(['read_at' => now()]);
    }

    public function getRecentNotifications(User $user, int $limit = 10): Collection
    {
        return $user->notifications()->latest()->limit($limit)->get();
    }
}
