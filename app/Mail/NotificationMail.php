<?php

declare (strict_types=1);
namespace App\Mail;

use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
/**
 * NotificationMail
 * 
 * Mailable class for NotificationMail email sending with template management and attachment support.
 * 
 */
final class NotificationMail extends Mailable
{
    use Queueable, SerializesModels;
    /**
     * Initialize the class instance with required dependencies.
     * @param Notification $notification
     */
    public function __construct(public Notification $notification)
    {
    }
    /**
     * Handle envelope functionality with proper error handling.
     * @return Envelope
     */
    public function envelope(): Envelope
    {
        $title = $this->notification->data['title'] ?? 'Notification';
        $urgent = $this->notification->data['urgent'] ?? false;
        return new Envelope(subject: $urgent ? '[URGENT] ' . $title : $title);
    }
    /**
     * Handle content functionality with proper error handling.
     * @return Content
     */
    public function content(): Content
    {
        return new Content(view: 'emails.notification', with: ['notification' => $this->notification, 'title' => $this->notification->data['title'] ?? 'Notification', 'message' => $this->notification->data['message'] ?? '', 'type' => $this->notification->data['type'] ?? 'general', 'urgent' => $this->notification->data['urgent'] ?? false, 'color' => $this->notification->data['color'] ?? '#3B82F6', 'tags' => $this->notification->data['tags'] ?? [], 'created_at' => $this->notification->created_at]);
    }
    /**
     * Handle attachments functionality with proper error handling.
     * @return array
     */
    public function attachments(): array
    {
        return [];
    }
}