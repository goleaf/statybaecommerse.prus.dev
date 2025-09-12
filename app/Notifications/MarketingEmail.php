<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class MarketingEmail extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $subject,
        public string $content,
        public string $template = 'promotional'
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->subject)
            ->greeting(__('notifications.greeting', ['name' => $notifiable->name]));

        // Add content based on template
        match ($this->template) {
            'promotional' => $mail
                ->line($this->content)
                ->action(__('notifications.shop_now'), url('/products'))
                ->line(__('notifications.promotional_footer')),
            'newsletter' => $mail
                ->line($this->content)
                ->action(__('notifications.read_more'), url('/'))
                ->line(__('notifications.newsletter_footer')),
            'discount_offer' => $mail
                ->line($this->content)
                ->action(__('notifications.claim_discount'), url('/products'))
                ->line(__('notifications.discount_footer')),
            default => $mail->line($this->content),
        };

        return $mail->line(__('notifications.marketing_unsubscribe'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'subject' => $this->subject,
            'content' => $this->content,
            'template' => $this->template,
            'sent_at' => now()->toISOString(),
        ];
    }
}
