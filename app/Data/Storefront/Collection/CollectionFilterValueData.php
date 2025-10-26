<?php

declare(strict_types=1);

namespace App\Data\Storefront\Collection;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Immutable value object describing a single selectable attribute value on the collection page.
 */
final class CollectionFilterValueData implements Arrayable
{
    public function __construct(
        public readonly int $id,
        public readonly string $label,
        public readonly bool $selected,
    ) {
        // Persist a simple representation so cached payloads remain lightweight for Livewire hydration.
    }

    /**
     * Factory helper that normalizes primitive arrays (for example cached payloads) into typed values.
     *
     * @param array{id:int, label:string, selected?:bool} $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            (int) ($payload['id'] ?? 0),
            (string) ($payload['label'] ?? ''),
            (bool) ($payload['selected'] ?? false),
        );
    }

    /**
     * Export the value metadata for JSON serialization and cache storage.
     *
     * @return array{id:int, label:string, selected:bool}
     */
    public function toArray(): array
    {
        return [
            'id'       => $this->id,
            'label'    => $this->label,
            'selected' => $this->selected,
        ];
    }

    /**
     * Build a clone of the current instance with a different selection flag so cached data can be reused safely.
     */
    public function withSelected(bool $selected): self
    {
        return new self($this->id, $this->label, $selected);
    }
}
