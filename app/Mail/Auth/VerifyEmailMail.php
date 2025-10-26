<?php

declare(strict_types=1);

namespace App\Mail\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class VerifyEmailMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * Store the resolved locale so translations remain consistent.
     */
    private ?string $cachedLocale = null;

    public function __construct(
        public readonly string $verificationUrl,
        public readonly ?string $preferredLocale = null,
    ) {}

    public function envelope(): Envelope
    {
        $locale = $this->resolveLocale();
        $this->locale($locale); // Ensure Markdown components use the same locale as the subject.

        return new Envelope(
            subject: __('mail.verify_email_subject', [], $locale)
        );
    }

    public function content(): Content
    {
        $locale = $this->resolveLocale();

        return new Content(
            markdown: 'emails.auth.verify',
            with: [
                'url' => $this->verificationUrl,
            ]
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        // Verification emails intentionally avoid attachments to keep delivery lightweight.
        return [];
    }

    private function resolveLocale(): string
    {
        // Respect preferred locales while staying resilient to null/empty values.
        if ($this->cachedLocale !== null) {
            return $this->cachedLocale;
        }

        if (is_string($this->preferredLocale) && $this->preferredLocale !== '') {
            return $this->cachedLocale = $this->preferredLocale;
        }

        return $this->cachedLocale = app()->getLocale();
    }
}
