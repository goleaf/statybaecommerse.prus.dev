<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * AdminNotification
 *
 * Notification class for AdminNotification user notifications with multi-channel delivery and customizable content.
 */
final class AdminNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Initialize the class instance with required dependencies.
     */
    public function __construct(
        public readonly string $title,
        public readonly string $message,
        public readonly string $type = 'info',
    ) {
        // The constructor stores the payload for later channel formatting.
    }

    /**
     * Handle via functionality with proper error handling.
     *
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        // Deliver the notification to the database and inbox for maximum visibility.
        return ['database', 'mail'];
    }

    /**
     * Handle toMail functionality with proper error handling.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // Compose a short transactional email with a localized footer.
        return (new MailMessage())
            ->subject($this->title)
            ->line($this->message)
            ->line(__('admin.notifications.admin_message_footer'));
    }

    /**
     * Handle toDatabase functionality with proper error handling.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        // Persist the structured payload so the notification center can render it consistently.
        return [
            'title' => $this->title,
            'message' => $this->message,
            'type' => $this->type,
            'sent_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Convert the instance to an array representation.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        // Mirror the database payload for API consumers.
        return [
            'title' => $this->title,
            'message' => $this->message,
            'type' => $this->type,
            'sent_at' => now()->toIso8601String(),
        ];
    }
}
