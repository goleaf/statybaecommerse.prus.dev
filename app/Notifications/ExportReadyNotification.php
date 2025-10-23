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
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Your export ":name" is ready', ['name' => $this->export->name]))
            ->line(__('The export you requested has finished processing.'))
            ->line(__('Format: :format', ['format' => strtoupper($this->export->format)]))
            ->action(__('Download export'), $this->downloadUrl)
            ->line(__('The link will expire in :minutes minutes.', ['minutes' => 60]));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'export_id' => $this->export->getKey(),
            'name' => $this->export->name,
            'format' => $this->export->format,
            'download_url' => $this->downloadUrl,
        ];
    }
}
