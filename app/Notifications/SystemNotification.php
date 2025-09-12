<?php declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

final class SystemNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $action,
        public readonly array $systemData = [],
        public readonly ?string $message = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'system',
            'action' => $this->action,
            'title' => $this->getTitle(),
            'message' => $this->message ?? $this->getMessage(),
            'data' => $this->systemData,
            'sent_at' => now()->toISOString(),
        ];
    }

    private function getTitle(): string
    {
        return match ($this->action) {
            'maintenance_started' => __('notifications.system.maintenance_started'),
            'maintenance_completed' => __('notifications.system.maintenance_completed'),
            'backup_created' => __('notifications.system.backup_created'),
            'update_available' => __('notifications.system.update_available'),
            'security_alert' => __('notifications.system.security_alert'),
            'performance_issue' => __('notifications.system.performance_issue'),
            default => __('notifications.system.maintenance_started'),
        };
    }

    private function getMessage(): string
    {
        return match ($this->action) {
            'maintenance_started' => __('notifications.system.maintenance_started'),
            'maintenance_completed' => __('notifications.system.maintenance_completed'),
            'backup_created' => __('notifications.system.backup_created'),
            'update_available' => __('notifications.system.update_available'),
            'security_alert' => __('notifications.system.security_alert'),
            'performance_issue' => __('notifications.system.performance_issue'),
            default => __('notifications.system.maintenance_started'),
        };
    }
}
