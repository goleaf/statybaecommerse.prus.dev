<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Export;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class ExportReadyNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Export $export,
        private readonly string $downloadUrl,
        private readonly int $ttlMinutes,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('exports.notifications.ready.subject', ['name' => $this->export->name]))
            ->line(__('exports.notifications.ready.intro'))
            ->action(__('exports.notifications.ready.action'), $this->downloadUrl)
            ->line(__('exports.notifications.ready.expires', ['minutes' => $this->ttlMinutes]));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'export_id' => $this->export->getKey(),
            'name' => $this->export->name,
            'format' => $this->export->format,
            'download_url' => $this->downloadUrl,
            'expires_in_minutes' => $this->ttlMinutes,
        ];
    }
}
