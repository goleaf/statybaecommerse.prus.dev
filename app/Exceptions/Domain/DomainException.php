<?php

declare(strict_types=1);

namespace App\Exceptions\Domain;

use App\Support\ErrorCodes;
use Exception;

/**
 * Base exception for domain-specific errors that should be rendered as JSON responses.
 */
abstract class DomainException extends Exception
{
    private readonly string $translationKey;

    /**
     * @param  string  $errorCode  Machine readable error code (e.g. `orders.not_found`).
     * @param  string|null  $translationKey  Optional translation key; defaults to the canonical key derived from the error code.
     * @param  array<string, mixed>  $context  Placeholder replacements that will be injected into the translation string.
     * @param  int  $status  HTTP status code that best represents the failure.
     */
    public function __construct(
        private readonly string $errorCode,
        ?string $translationKey = null,
        private readonly array $context = [],
        private readonly int $status = 400,
        ?Exception $previous = null,
    ) {
        $this->translationKey = $translationKey ?? ErrorCodes::translationKey($errorCode);

        parent::__construct($this->translationKey, $status, $previous);
    }

    public function errorCode(): string
    {
        return $this->errorCode;
    }

    public function translationKey(): string
    {
        return $this->translationKey;
    }

    /**
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return $this->context;
    }

    public function status(): int
    {
        return $this->status;
    }
}
