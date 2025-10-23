<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Export;
use App\Services\Export\ExportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class ExportCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly string $exportId) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $export = $this->resolveExport();
        $url = app(ExportService::class)->makeSignedDownloadUrl($export);

        return (new MailMessage)
            ->subject(__('exports.notifications.subject', ['name' => $export->name]))
            ->line(__('exports.notifications.ready', ['name' => $export->name]))
            ->action(__('exports.notifications.download_action'), $url)
            ->line(__('exports.notifications.expiration', ['date' => optional($export->available_until)->toDateTimeString()]));
    }

    public function toArray(object $notifiable): array
    {
        $export = $this->resolveExport();
        $url = app(ExportService::class)->makeSignedDownloadUrl($export);

        return [
            'export_id' => $export->id,
            'name' => $export->name,
            'format' => $export->format->value,
            'download_url' => $url,
            'available_until' => optional($export->available_until)->toIso8601String(),
        ];
    }

    private function resolveExport(): Export
    {
        return Export::query()->findOrFail($this->exportId);
    }
}
