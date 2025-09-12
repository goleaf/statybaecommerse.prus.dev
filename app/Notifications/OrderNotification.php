<?php declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class OrderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public array $data
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return $this->data;
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->data['title'])
            ->line($this->data['message'])
            ->when(isset($this->data['order_number']), function ($mail) {
                return $mail->line('Užsakymo numeris: ' . $this->data['order_number']);
            });
    }
}



