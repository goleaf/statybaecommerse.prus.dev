<?php

declare(strict_types=1);

namespace App\Mail\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

final class PasswordResetMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly string $resetUrl,
        public readonly int $expiresInMinutes,
        public readonly ?string $preferredLocale = null,
    ) {}

    public function build(): self
    {
        $locale = $this->preferredLocale ?? app()->getLocale();

        return $this
            ->locale($locale)
            ->subject(__('mail.reset_password_subject', [], $locale))
            ->markdown('emails.auth.password-reset', [
                'url' => $this->resetUrl,
                'minutes' => $this->expiresInMinutes,
            ]);
    }
}
