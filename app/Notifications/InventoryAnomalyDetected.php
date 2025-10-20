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
     * @param  Collection<int, string>|array<int, string>  $anomalies
     */
    public function __construct(private readonly array $anomalies)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage())
            ->subject(__('Inventory anomaly report'))
            ->greeting(__('Inventory Anomalies Detected'))
            ->line(__('The scheduled reconciliation job found the following issues:'));

        Collection::make($this->anomalies)->each(fn (string $item) => $message->line("• {$item}"));

        return $message->line(__('Please investigate and correct stock levels as needed.'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'anomalies' => array_values($this->anomalies),
        ];
    }
}
