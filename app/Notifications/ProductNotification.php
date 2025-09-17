<?php declare(strict_types=1);

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
     * @param array $data
     */
    public function __construct(
        public array $data
    ) {}

    /**
     * Handle via functionality with proper error handling.
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable): array
    {
        return ['database'];
    }

    /**
     * Handle toDatabase functionality with proper error handling.
     * @param mixed $notifiable
     * @return array
     */
    public function toDatabase($notifiable): array
    {
        return $this->data;
    }

    /**
     * Handle toMail functionality with proper error handling.
     * @param mixed $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        $locale = method_exists($notifiable, 'preferredLocale') ? ($notifiable->preferredLocale() ?: app()->getLocale()) : app()->getLocale();

        return (new MailMessage())
            ->subject($this->data['title'])
            ->line($this->data['message'])
            ->when(isset($this->data['product_name']), function ($mail) use ($locale) {
                return $mail->line(__('notifications.product.name_line', ['name' => $this->data['product_name']], $locale));
            });
    }
}
