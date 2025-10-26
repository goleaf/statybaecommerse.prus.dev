<?php

declare(strict_types=1);

namespace App\Data\Storefront\Collection;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

/**
 * Bundles an attribute and its available values for use on the collection filter sidebar.
 */
final class CollectionFilterGroupData implements Arrayable
{
    /**
     * @param Collection<int, CollectionFilterValueData> $values
     */
    public function __construct(
        public readonly CollectionFilterAttributeData $attribute,
        public readonly Collection $values,
    ) {
        // The constructor accepts typed dependencies so that downstream code can rely on predictable shapes.
    }

    /**
     * Export the filter group to primitive arrays for Livewire serialization/caching.
     *
     * @return array{attribute:array{id:int,name:string}, values:array<int, array{id:int,label:string,selected:bool}>}
     */
    public function toArray(): array
    {
        return [
            'attribute' => $this->attribute->toArray(),
            'values'    => $this->values
                ->map(static fn (CollectionFilterValueData $value): array => $value->toArray())
                ->values()
                ->all(),
        ];
    }

    /**
     * Hydrate the group from a cached array payload so the data class remains the single source of truth.
     *
     * @param array{attribute:array{id:int,name:string}, values:array<int, array{id:int,label:string,selected?:bool}>} $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            new CollectionFilterAttributeData(
                (int) ($payload['attribute']['id'] ?? 0),
                (string) ($payload['attribute']['name'] ?? ''),
            ),
            collect($payload['values'] ?? [])
                ->map(static fn (array $value): CollectionFilterValueData => CollectionFilterValueData::fromArray($value)),
        );
    }

    /**
     * Clone the filter group while updating which values are marked as selected.
     *
     * @param array<int, int> $selectedIds
     */
    public function withSelected(array $selectedIds): self
    {
        $selectedIds = array_map(static fn (int $id): int => $id, $selectedIds);

        return new self(
            $this->attribute,
            $this->values->map(
                static fn (CollectionFilterValueData $value): CollectionFilterValueData => $value->withSelected(
                    in_array($value->id, $selectedIds, true)
                )
            ),
        );
    }
}
