<?php

declare(strict_types=1);

namespace App\Mail\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

final class VerifyEmailMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly string $verificationUrl,
        public readonly ?string $preferredLocale = null,
    ) {}

    public function build(): self
    {
        $locale = $this->preferredLocale ?? app()->getLocale();

        return $this
            ->locale($locale)
            ->subject(__('mail.verify_email_subject', [], $locale))
            ->markdown('emails.auth.verify', [
                'url' => $this->verificationUrl,
            ]);
    }
}
