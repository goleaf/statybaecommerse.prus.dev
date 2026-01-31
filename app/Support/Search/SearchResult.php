<?php

declare(strict_types=1);

namespace App\Support\Search;

use Illuminate\Contracts\Support\Arrayable;

final class SearchResult implements Arrayable
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        private readonly string $value,
        private readonly string $label,
        private array $data = []
    ) {}

    public static function make(string $value, string $label): self
    {
        return new self($value, $label);
    }

    /**
     * @param array{value: string, label?: string, data?: array<string, mixed>} $array
     */
    public static function fromArray(array $array): self
    {
        return new self(
            (string) ($array['value'] ?? ''),
            (string) ($array['label'] ?? ''),
            (array) ($array['data'] ?? [])
        );
    }

    public function value(): string
    {
        return $this->value;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function withData(string $key, mixed $value): self
    {
        $this->data[$key] = $value;

        return $this;
    }

    public function get(string $key): mixed
    {
        return $this->data[$key] ?? null;
    }

    /**
     * @return array{value: string, label: string, data: array<string, mixed>}
     */
    public function toArray(): array
    {
        return [
            'value' => $this->value,
            'label' => $this->label,
            'data'  => $this->data,
        ];
    }
}
