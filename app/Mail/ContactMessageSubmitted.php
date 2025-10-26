<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class ContactMessageSubmitted extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Cache the locale to avoid resolving it multiple times per mail lifecycle.
     */
    private ?string $cachedLocale = null;

    public function __construct(public ContactMessage $contactMessage) {}

    public function envelope(): Envelope
    {
        $locale = $this->resolveLocale();
        $this->locale($locale); // Ensure translations render in the resolved language.

        $subject = (string) ($this->contactMessage->subject ?: __('mail.contact_message_subject_fallback', [], $locale));

        return new Envelope(
            subject: __('mail.contact_message_subject', ['subject' => $subject], $locale)
        );
    }

    public function content(): Content
    {
        $this->locale($this->resolveLocale()); // Keep the rendered view in sync with the subject locale.

        return new Content(
            view: 'emails.contact.submitted',
            with: [
                'contactMessage' => $this->contactMessage,
            ]
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        // Contact form submissions do not carry file uploads today, but we keep the
        // contract explicit so future attachments can hook in here.
        return [];
    }

    private function resolveLocale(): string
    {
        // The contact form is public-facing, so we default to the application locale.
        return $this->cachedLocale ??= app()->getLocale();
    }
}
