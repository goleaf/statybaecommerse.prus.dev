<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

final class InventoryAnomalyDetected extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param Collection<int, string>|array<int, string> $anomalies
     */
    public function __construct(private readonly array $anomalies)
    {
        // Store the anomalies so each channel can surface the same bullet list.
    }

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        // Email is the quickest way to alert operators of reconciliation problems.
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        // Build a simple summary and append each anomaly as its own line item.
        $message = (new MailMessage)
            ->subject(__('messages.inventory_anomaly_report'))
            ->greeting(__('messages.inventory_anomalies_detected'))
            ->line(__('messages.the_scheduled_reconciliation_job_found_the_following_issues'));

        Collection::make($this->anomalies)->each(
            static fn (string $item) => $message->line("• {$item}")
        );

        return $message->line(__('messages.please_investigate_and_correct_stock_levels_as_needed'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        // Persist a clean array for UI components that render the bullet list.
        return [
            'anomalies' => array_values($this->anomalies),
        ];
    }
}
