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

final class PasswordResetMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * Cache the resolved locale to avoid recomputation.
     */
    private ?string $cachedLocale = null;

    public function __construct(
        public readonly string $resetUrl,
        public readonly int $expiresInMinutes,
        public readonly ?string $preferredLocale = null,
    ) {}

    public function envelope(): Envelope
    {
        $locale = $this->resolveLocale();
        $this->locale($locale); // Ensure translations respect the intended locale.

        return new Envelope(
            subject: __('mail.reset_password_subject', [], $locale)
        );
    }

    public function content(): Content
    {
        $locale = $this->resolveLocale();

        return new Content(
            markdown: 'emails.auth.password-reset',
            with: [
                'url'     => $this->resetUrl,
                'minutes' => $this->expiresInMinutes,
            ]
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        // Auth transactional emails do not include attachments, but we keep the signature explicit.
        return [];
    }

    private function resolveLocale(): string
    {
        // Prefer the caller-provided locale while gracefully falling back to the app default.
        if ($this->cachedLocale !== null) {
            return $this->cachedLocale;
        }

        if (is_string($this->preferredLocale) && $this->preferredLocale !== '') {
            return $this->cachedLocale = $this->preferredLocale;
        }

        return $this->cachedLocale = app()->getLocale();
    }
}
