<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Export;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class ExportCompletedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Export $export,
        private readonly string $downloadUrl,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $expiresIn = (int) config('export.download_url_ttl', 60);

        return (new MailMessage)
            ->subject(__('exports.notifications.completed.subject', ['name' => $this->export->name]))
            ->line(__('exports.notifications.completed.intro'))
            ->line(__('exports.notifications.completed.format', ['format' => strtoupper($this->export->format)]))
            ->action(__('exports.notifications.completed.action'), $this->downloadUrl)
            ->line(__('exports.notifications.completed.expires', ['minutes' => $expiresIn]));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'export_id' => $this->export->getKey(),
            'name' => $this->export->name,
            'format' => $this->export->format,
            'download_url' => $this->downloadUrl,
            'expires_in_minutes' => (int) config('export.download_url_ttl', 60),
        ];
    }
}
