<?php

declare(strict_types=1);

namespace App\Support\Security;

/**
 * Simple value object that carries the Content-Security-Policy nonce for the current request.
 */
final class CspNonce
{
    private readonly string $value;

    public function __construct(?string $value = null)
    {
        // Generate a cryptographically secure 32-byte token when no value has been pre-provided.
        $this->value = $value ?? base64_encode(random_bytes(32));
    }

    public function value(): string
    {
        return $this->value;
    }

    public function headerValue(): string
    {
        // CSP headers expect the nonce to be wrapped in single quotes with the nonce- prefix.
        return "'nonce-{$this->value}'";
    }
}
