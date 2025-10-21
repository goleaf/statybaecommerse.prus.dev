<?php

declare(strict_types=1);

namespace App\Support\Security\Captcha;

final class CaptchaChallenge
{
    public function __construct(
        private readonly string $question,
        private readonly string $token
    ) {}

    public function question(): string
    {
        return $this->question;
    }

    public function token(): string
    {
        return $this->token;
    }
}
