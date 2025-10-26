<?php

declare(strict_types=1);

namespace App\Data\Collection;

use Illuminate\Support\Collection;

/**
 * AttributeFilterGroupData
 *
 * Encapsulates an attribute with its available values for collection filters.
 */
final class AttributeFilterGroupData
{
    /**
     * @param Collection<int, AttributeFilterValueData> $values
     */
    public function __construct(
        public readonly int $attributeId,
        public readonly string $attributeName,
        public readonly Collection $values,
    ) {
        // The constructor keeps the collection typed and documented for reuse.
    }

    /**
     * Export the group as an array compatible with Blade iterations.
     *
     * @return array{attributeId:int,attributeName:string,values:list<array{id:int,label:string,selected:bool}>}
     */
    public function toArray(): array
    {
        return [
            'attributeId'   => $this->attributeId,
            'attributeName' => $this->attributeName,
            'values'        => $this->values
                ->map(static fn (AttributeFilterValueData $value): array => $value->toArray())
                ->values()
                ->all(),
        ];
    }
}
