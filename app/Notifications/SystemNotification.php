<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * SystemNotification
 *
 * Notification class for SystemNotification user notifications with multi-channel delivery and customizable content.
 */
final class SystemNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Initialize the class instance with required dependencies.
     */
    public function __construct(public array $data)
    {
        // Preserve the system payload for the downstream channels.
    }

    /**
     * Handle via functionality with proper error handling.
     *
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        // Store the message in the in-app notification feed by default.
        return ['database'];
    }

    /**
     * Handle toDatabase functionality with proper error handling.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        // Persist the system payload exactly as it was provided.
        return $this->data;
    }

    /**
     * Handle toMail functionality with proper error handling.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // Provide a simple email containing the system broadcast.
        return (new MailMessage)
            ->subject($this->data['title'])
            ->line($this->data['message'])
            ->line(__('This message was sent from the system administration console.'));
    }
}
