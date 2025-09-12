<?php declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

final class UserNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $action,
        public readonly array $userData,
        public readonly ?string $message = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'user',
            'action' => $this->action,
            'user_id' => $this->userData['id'] ?? null,
            'user_name' => $this->userData['name'] ?? null,
            'user_email' => $this->userData['email'] ?? null,
            'title' => $this->getTitle(),
            'message' => $this->message ?? $this->getMessage(),
            'data' => $this->userData,
            'sent_at' => now()->toISOString(),
        ];
    }

    private function getTitle(): string
    {
        return match ($this->action) {
            'registered' => __('notifications.user.registered'),
            'profile_updated' => __('notifications.user.profile_updated'),
            'password_changed' => __('notifications.user.password_changed'),
            'email_verified' => __('notifications.user.email_verified'),
            'login' => __('notifications.user.login'),
            'logout' => __('notifications.user.logout'),
            'account_suspended' => __('notifications.user.account_suspended'),
            'account_activated' => __('notifications.user.account_activated'),
            default => __('notifications.user.profile_updated'),
        };
    }

    private function getMessage(): string
    {
        $userName = $this->userData['name'] ?? $this->userData['email'] ?? 'Unknown User';
        
        return match ($this->action) {
            'registered' => __('notifications.user.registered') . ": {$userName}",
            'profile_updated' => __('notifications.user.profile_updated') . ": {$userName}",
            'password_changed' => __('notifications.user.password_changed') . ": {$userName}",
            'email_verified' => __('notifications.user.email_verified') . ": {$userName}",
            'login' => __('notifications.user.login') . ": {$userName}",
            'logout' => __('notifications.user.logout') . ": {$userName}",
            'account_suspended' => __('notifications.user.account_suspended') . ": {$userName}",
            'account_activated' => __('notifications.user.account_activated') . ": {$userName}",
            default => __('notifications.user.profile_updated') . ": {$userName}",
        };
    }
}




