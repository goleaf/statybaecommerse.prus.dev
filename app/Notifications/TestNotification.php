<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * TestNotification
 *
 * Notification class for TestNotification user notifications with multi-channel delivery and customizable content.
 */
final class TestNotification extends Notification
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
        // The constructor simply stores the test payload.
    }

    /**
     * Handle via functionality with proper error handling.
     *
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        // Persist the message in the database so testers can verify delivery.
        return ['database'];
    }

    /**
     * Handle toDatabase functionality with proper error handling.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        // Provide a consistent payload for debugging and API inspection.
        return [
            'title'   => $this->title,
            'message' => $this->message,
            'type'    => $this->type,
            'sent_at' => now()->toIso8601String(),
        ];
    }
}
