<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * MarketingEmail
 *
 * Notification class for MarketingEmail user notifications with multi-channel delivery and customizable content.
 */
final class MarketingEmail extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Initialize the class instance with required dependencies.
     */
    public function __construct(
        public string $subject,
        public string $content,
        public string $template = 'promotional',
    ) {
        // Store the marketing copy so we can reuse it across delivery channels.
    }

    /**
     * Handle via functionality with proper error handling.
     *
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        // Record the campaign in the database while also sending an email blast.
        return ['mail', 'database'];
    }

    /**
     * Handle toMail functionality with proper error handling.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // Gracefully resolve the recipient name for the greeting.
        $name = $notifiable->name ?? $notifiable->email ?? __('valued customer');

        $mail = (new MailMessage())
            ->subject($this->subject)
            ->greeting(__('notifications.greeting', ['name' => $name]));

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

        // Always include the unsubscribe reminder at the end of the email.
        return $mail->line(__('notifications.marketing_unsubscribe'));
    }

    /**
     * Convert the instance to an array representation.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        // Persist the email metadata for analytics dashboards.
        return [
            'subject' => $this->subject,
            'content' => $this->content,
            'template' => $this->template,
            'sent_at' => now()->toIso8601String(),
        ];
    }
}
