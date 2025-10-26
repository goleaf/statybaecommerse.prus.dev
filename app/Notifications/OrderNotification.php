<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * OrderNotification
 *
 * Notification class for OrderNotification user notifications with multi-channel delivery and customizable content.
 */
final class OrderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Initialize the class instance with required dependencies.
     */
    public function __construct(public array $data)
    {
        // Store the raw order payload for downstream formatting.
    }

    /**
     * Handle via functionality with proper error handling.
     *
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        // Persist the update in the database notification feed by default.
        return ['database'];
    }

    /**
     * Handle toDatabase functionality with proper error handling.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        // Return the provided payload verbatim for maximum flexibility.
        return $this->data;
    }

    /**
     * Handle toMail functionality with proper error handling.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // Assemble an optional transactional email mirroring the database payload.
        $mail = (new MailMessage)
            ->subject($this->data['title'])
            ->line($this->data['message']);

        if (isset($this->data['order_number'])) {
            $mail->line(__('Order number: :number', ['number' => $this->data['order_number']]));
        }

        return $mail;
    }
}
