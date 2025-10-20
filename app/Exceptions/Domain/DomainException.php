<?php

declare(strict_types=1);

namespace App\Exceptions\Domain;

use App\Support\ErrorCode;
use Exception;

/**
 * Base exception for domain-specific errors that should be rendered as JSON responses.
 */
abstract class DomainException extends Exception
{
    /**
     * @param  ErrorCode  $errorCode  Machine readable error code that describes the failure.
     * @param  array<string, mixed>  $context  Placeholder replacements that will be injected into the translation string.
     * @param  int  $status  HTTP status code that best represents the failure.
     */
    public function __construct(
        private readonly ErrorCode $errorCode,
        private readonly array $context = [],
        private readonly int $status = 400,
        ?Exception $previous = null,
    ) {
        parent::__construct($errorCode->translationKey(), $status, $previous);
    }

    public function errorCode(): ErrorCode
    {
        return $this->errorCode;
    }

    public function translationKey(): string
    {
        return $this->errorCode->translationKey();
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
