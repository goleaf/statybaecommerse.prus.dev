<?php

declare(strict_types=1);

namespace App\Services\Pricing\Exceptions;

use RuntimeException;

final class RateTamperingException extends RuntimeException
{
    public function __construct(private readonly string $field)
    {
        parent::__construct('Client supplied rate details do not match the authoritative configuration.');
    }

    public static function forField(string $field): self
    {
        // Expose a concise named constructor so callers can communicate which field failed validation.
        return new self($field);
    }

    public function field(): string
    {
        return $this->field;
    }
}
