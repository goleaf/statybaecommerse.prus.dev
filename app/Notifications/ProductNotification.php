<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * ProductNotification
 *
 * Notification class for ProductNotification user notifications with multi-channel delivery and customizable content.
 */
final class ProductNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Initialize the class instance with required dependencies.
     */
    public function __construct(public array $data)
    {
        // Carry the raw product data so it is available for every channel.
    }

    /**
     * Handle via functionality with proper error handling.
     *
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        // Default to the database notification channel.
        return ['database'];
    }

    /**
     * Handle toDatabase functionality with proper error handling.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        // Return the prepared payload directly for flexible rendering.
        return $this->data;
    }

    /**
     * Handle toMail functionality with proper error handling.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // Provide a brief summary email describing the product event.
        $mail = (new MailMessage)
            ->subject($this->data['title'])
            ->line($this->data['message']);

        if (isset($this->data['product_name'])) {
            $mail->line(__('Product: :name', ['name' => $this->data['product_name']]));
        }

        return $mail;
    }
}
