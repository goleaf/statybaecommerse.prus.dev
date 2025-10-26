<?php

declare(strict_types=1);

namespace App\Data\Storefront\System;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Simple DTO describing a public system setting entry for the storefront diagnostics widget.
 */
final class SystemSettingEntryData implements Arrayable
{
    public function __construct(
        public readonly string $key,
        public readonly mixed $value,
    ) {
        // Values may be scalars or arrays; they are passed through verbatim for display purposes.
    }

    /**
     * Hydrate the DTO from a cached array payload to avoid duplicating normalisation logic.
     *
     * @param array{key:string, value:mixed} $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            (string) ($payload['key'] ?? ''),
            $payload['value'] ?? null,
        );
    }

    /**
     * @return array{key:string, value:mixed}
     */
    public function toArray(): array
    {
        return [
            'key'   => $this->key,
            'value' => $this->value,
        ];
    }
}
