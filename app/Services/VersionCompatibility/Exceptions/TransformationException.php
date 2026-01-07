<?php

declare(strict_types=1);

namespace App\Services\VersionCompatibility\Exceptions;

use Exception;
use Throwable;

/**
 * Exception thrown when a transformation operation fails
 */
class TransformationException extends Exception
{
    public function __construct(
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null,
        private readonly ?string $filePath = null,
        private readonly array $context = []
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public static function forFile(string $filePath, string $message, ?Throwable $previous = null): self
    {
        return new self(
            message: "Transformation failed for file '{$filePath}': {$message}",
            previous: $previous,
            filePath: $filePath
        );
    }

    public static function withContext(string $message, array $context, ?Throwable $previous = null): self
    {
        return new self(
            message: $message,
            previous: $previous,
            context: $context
        );
    }
}
