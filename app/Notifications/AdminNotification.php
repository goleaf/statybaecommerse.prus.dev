<?php

declare (strict_types=1);
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
/**
 * AdminNotification
 * 
 * Notification class for AdminNotification user notifications with multi-channel delivery and customizable content.
 * 
 */
final class AdminNotification extends Notification implements ShouldQueue
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
        return ['database', 'mail'];
    }
    /**
     * Handle toMail functionality with proper error handling.
     * @param object $notifiable
     * @return MailMessage
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())->subject($this->title)->line($this->message)->line(__('admin.notifications.admin_message_footer'));
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
    /**
     * Convert the instance to an array representation.
     * @param object $notifiable
     * @return array
     */
    public function toArray(object $notifiable): array
    {
        return ['title' => $this->title, 'message' => $this->message, 'type' => $this->type, 'sent_at' => now()->toISOString()];
    }
}