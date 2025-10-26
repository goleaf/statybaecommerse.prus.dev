<?php

declare(strict_types=1);

namespace App\Data\Collection;

/**
 * AttributeFilterValueData
 *
 * Typed data structure representing a selectable attribute value within
 * collection filters.
 */
final class AttributeFilterValueData
{
    /**
     * Create a new immutable attribute value representation.
     */
    public function __construct(
        public readonly int $id,
        public readonly string $label,
        public readonly bool $selected,
    ) {
        // Value objects remain comment-friendly for future maintainers.
    }

    /**
     * Transform the value object into a simple array for view hydration.
     *
     * @return array{id:int,label:string,selected:bool}
     */
    public function toArray(): array
    {
        return [
            'id'       => $this->id,
            'label'    => $this->label,
            'selected' => $this->selected,
        ];
    }
}
