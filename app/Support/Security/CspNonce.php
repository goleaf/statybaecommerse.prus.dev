<?php

declare(strict_types=1);

namespace App\Support\Security;

final class CspNonce
{
    private readonly string $value;

    public function __construct(?string $value = null)
    {
        $this->value = $value ?? base64_encode(random_bytes(32));
    }

    public function value(): string
    {
        return $this->value;
    }

    public function headerValue(): string
    {
        return "'nonce-{$this->value}'";
    }
}
