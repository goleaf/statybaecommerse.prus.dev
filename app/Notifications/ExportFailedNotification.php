<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Export;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class ExportFailedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Export $export) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('exports.notifications.failed.subject', ['name' => $this->export->name]))
            ->line(__('exports.notifications.failed.intro'))
            ->line(__('exports.notifications.failed.reason', ['reason' => $this->export->failure_reason ?? __('Unknown error')]))
            ->line(__('exports.notifications.failed.support'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'export_id' => $this->export->getKey(),
            'name' => $this->export->name,
            'status' => $this->export->status->value,
            'failure_reason' => $this->export->failure_reason,
        ];
    }
}
