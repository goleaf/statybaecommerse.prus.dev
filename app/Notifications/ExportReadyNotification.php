<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Export;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class ExportReadyNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Export $export,
        private readonly string $downloadUrl,
        private readonly int $ttlMinutes,
    ) {
        // Keep the expiry window handy so every channel shows consistent data.
    }

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        // Notify through the two most important operator channels.
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        // Highlight the availability and expiry in the email copy.
        return (new MailMessage)
            ->subject(__('exports.notifications.ready.subject', ['name' => $this->export->name]))
            ->line(__('exports.notifications.ready.intro'))
            ->action(__('exports.notifications.ready.action'), $this->downloadUrl)
            ->line(__('exports.notifications.ready.expires', ['minutes' => $this->ttlMinutes]));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        // Persist metadata for dashboards and audit logs.
        return [
            'export_id'          => $this->export->getKey(),
            'name'               => $this->export->name,
            'format'             => $this->export->format,
            'download_url'       => $this->downloadUrl,
            'expires_in_minutes' => $this->ttlMinutes,
        ];
    }
}
