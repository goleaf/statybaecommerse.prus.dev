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

    public function __construct(private readonly Export $export)
    {
        $this->onQueue('exports');
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = $this->export->signedUrl();

        $expiry = $this->export->expires_at?->clone()->timezone($this->export->timezone);

        return (new MailMessage())
            ->subject(__('Your export is ready'))
            ->greeting(__('Hello :name', ['name' => $notifiable->name ?? __('there')]))
            ->line(__('The export :name has been generated successfully.', ['name' => $this->export->file_name]))
            ->action(__('Download export'), $url)
            ->line(__('This link will expire on :date.', ['date' => $expiry?->toDayDateTimeString() ?? 'N/A']))
            ->line(__('If you did not request this file please contact support.'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'export_id' => $this->export->getKey(),
            'file_name' => $this->export->file_name,
            'format' => $this->export->format->value,
            'download_url' => $this->export->signedUrl(),
            'expires_at' => optional($this->export->expires_at)?->toIso8601String(),
        ];
    }
}
