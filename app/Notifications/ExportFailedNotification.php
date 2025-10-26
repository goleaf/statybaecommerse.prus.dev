<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Export;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class ExportFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Export $export)
    {
        // Capture the failed export to surface diagnostics to the operator.
    }

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        // Persist the failure in the inbox and send an immediate email alert.
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        // Provide a helpful fallback so the copy stays readable without a stored reason.
        $reason = $this->export->failure_reason ?? __('exports.notifications.failed.unknown_reason');

        return (new MailMessage)
            ->subject(__('exports.notifications.failed.subject', ['name' => $this->export->name]))
            ->line(__('exports.notifications.failed.intro'))
            ->line(__('exports.notifications.failed.reason', ['reason' => $reason]))
            ->line(__('exports.notifications.failed.support'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        // Store the reason along with the key data for follow-up debugging.
        return [
            'export_id'      => $this->export->getKey(),
            'name'           => $this->export->name,
            'status'         => $this->export->status->value,
            'failure_reason' => $this->export->failure_reason ?? __('exports.notifications.failed.unknown_reason'),
        ];
    }
}
