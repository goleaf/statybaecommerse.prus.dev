<?php

declare(strict_types=1);

namespace App\Data\Common;

use Spatie\LaravelData\Data;

/**
 * Standardized service response format
 */
class ServiceResponseData extends Data
{
    public function __construct(
        public bool $success,
        public mixed $data = null,
        public string $message = '',
        public int $code = 200,
        public array $errors = [],
        public array $meta = []
    ) {}

    public static function success(mixed $data = null, string $message = ''): self
    {
        return new self(
            success: true,
            data: $data,
            message: $message,
            code: 200
        );
    }

    public static function error(string $message, mixed $data = null, int $code = 400, array $errors = []): self
    {
        return new self(
            success: false,
            data: $data,
            message: $message,
            code: $code,
            errors: $errors
        );
    }

    public function withMeta(array $meta): self
    {
        $this->meta = array_merge($this->meta, $meta);

        return $this;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function isError(): bool
    {
        return ! $this->success;
    }
}
