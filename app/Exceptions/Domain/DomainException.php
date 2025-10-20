<?php

declare(strict_types=1);

namespace App\Exceptions\Domain;

use Exception;

/**
 * Base exception for domain-specific errors that should be rendered as JSON responses.
 */
abstract class DomainException extends Exception
{
    /**
     * @param  string  $errorCode  Machine readable error code (e.g. `orders.not_found`).
     * @param  string  $translationKey  Translation key used to localize the human readable message.
     * @param  array<string, mixed>  $context  Placeholder replacements that will be injected into the translation string.
     * @param  int  $status  HTTP status code that best represents the failure.
     */
    public function __construct(
        private readonly string $errorCode,
        private readonly string $translationKey,
        private readonly array $context = [],
        private readonly int $status = 400,
        ?Exception $previous = null,
    ) {
        parent::__construct($translationKey, $status, $previous);
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
