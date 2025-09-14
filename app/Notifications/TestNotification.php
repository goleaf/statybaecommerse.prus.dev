<?php

declare (strict_types=1);
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
/**
 * TestNotification
 * 
 * Notification class for TestNotification user notifications with multi-channel delivery and customizable content.
 * 
 */
final class TestNotification extends Notification
{
    use Queueable;
    /**
     * Initialize the class instance with required dependencies.
     * @param string $title
     * @param string $message
     * @param string $type
     */
    public function __construct(public readonly string $title, public readonly string $message, public readonly string $type = 'info')
    {
    }
    /**
     * Handle via functionality with proper error handling.
     * @param object $notifiable
     * @return array
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }
    /**
     * Handle toDatabase functionality with proper error handling.
     * @param object $notifiable
     * @return array
     */
    public function toDatabase(object $notifiable): array
    {
        return ['title' => $this->title, 'message' => $this->message, 'type' => $this->type, 'sent_at' => now()->toISOString()];
    }
}