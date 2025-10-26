<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Export;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class ExportCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Export $export,
        private readonly string $downloadUrl,
    ) {
        // Hold onto the export metadata so it can be re-used across channels.
    }

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        // Notify through email and the in-app notification center.
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        // Respect the configured TTL so the email mirrors the database payload.
        $expiresIn = (int) config('export.download_url_ttl', 60);

        return (new MailMessage)
            ->subject(__('exports.notifications.completed.subject', ['name' => $this->export->name]))
            ->line(__('exports.notifications.completed.intro'))
            ->line(__('exports.notifications.completed.format', ['format' => strtoupper($this->export->format)]))
            ->action(__('exports.notifications.completed.action'), $this->downloadUrl)
            ->line(__('exports.notifications.completed.expires', ['minutes' => $expiresIn]));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        // Persist enough context for administrators to audit finished exports.
        return [
            'export_id'          => $this->export->getKey(),
            'name'               => $this->export->name,
            'format'             => $this->export->format,
            'download_url'       => $this->downloadUrl,
            'expires_in_minutes' => (int) config('export.download_url_ttl', 60),
        ];
    }
}
