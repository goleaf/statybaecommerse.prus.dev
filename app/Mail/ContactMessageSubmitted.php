<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class ContactMessageSubmitted extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ContactMessage $contactMessage) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('mail.contact_message_subject', ['subject' => $this->contactMessage->subject])
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact.submitted',
            with: [
                'contactMessage' => $this->contactMessage,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
