<?php

declare(strict_types=1);

namespace App\Data\Storefront\Collection;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Immutable view model describing a filter attribute that can be toggled on the collection page.
 *
 * The data object is intentionally lightweight so it can be cached safely and quickly serialized
 * when Livewire needs to hydrate component state between requests.
 */
final class CollectionFilterAttributeData implements Arrayable
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
    ) {
        // Store minimal metadata for the attribute so downstream consumers can render labels reliably.
    }

    /**
     * Convert the attribute payload into a primitive array for JSON serialization.
     *
     * @return array{id:int, name:string}
     */
    public function toArray(): array
    {
        return [
            'id'   => $this->id,
            'name' => $this->name,
        ];
    }
}
