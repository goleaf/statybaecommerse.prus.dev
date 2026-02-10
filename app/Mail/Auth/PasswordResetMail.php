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

    private ?string $cachedLocale = null;

    public function __construct(
        public readonly string $resetUrl,
        public readonly int $expiresInMinutes,
        public readonly ?string $preferredLocale = null,
    ) {}

    public function envelope(): Envelope
    {
        $locale = $this->resolveLocale();
        $this->locale($locale);

        return new Envelope(
            subject: __('messages.mail', [], $locale)
        );
    }

    public function content(): Content
    {
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
        return [];
    }

    private function resolveLocale(): string
    {
        if ($this->cachedLocale !== null) {
            return $this->cachedLocale;
        }

        if (is_string($this->preferredLocale) && $this->preferredLocale !== '') {
            return $this->cachedLocale = $this->preferredLocale;
        }

        return $this->cachedLocale = app()->getLocale();
    }
}
