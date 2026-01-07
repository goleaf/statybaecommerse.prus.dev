<?php

declare(strict_types=1);

namespace App\Services\VersionCompatibility\Exceptions;

use InvalidArgumentException;
use Throwable;

/**
 * Exception thrown when file validation fails
 */
class InvalidFileException extends InvalidArgumentException
{
    public function __construct(
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null,
        private readonly ?string $filePath = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public static function fileNotFound(string $filePath): self
    {
        return new self(
            message: "File not found: {$filePath}",
            filePath: $filePath
        );
    }

    public static function invalidExtension(string $filePath, string $extension, array $allowed): self
    {
        return new self(
            message: "Invalid file extension '{$extension}' for file '{$filePath}'. Allowed: " . implode(', ', $allowed),
            filePath: $filePath
        );
    }

    public static function fileTooLarge(string $filePath, int $size, int $maxSize): self
    {
        return new self(
            message: "File '{$filePath}' is too large ({$size} bytes). Maximum allowed: {$maxSize} bytes",
            filePath: $filePath
        );
    }

    public static function pathTraversalDetected(string $filePath): self
    {
        return new self(
            message: "Path traversal detected in file path: {$filePath}",
            filePath: $filePath
        );
    }
}
