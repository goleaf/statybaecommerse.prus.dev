<?php

declare(strict_types=1);

namespace App\Data\Storefront\Home;

use App\Models\Category;
use Illuminate\Contracts\Support\Arrayable;

/**
 * Lightweight representation of category filter options for the catalogue Livewire page.
 */
final class CategoryFilterOptionData implements Arrayable
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
    ) {
        // DTO constructor intentionally void for clarity.
    }

    /**
     * Map the category model to a simple filter option payload.
     */
    public static function fromModel(Category $category, string $locale): self
    {
        $name = (string) ($category->trans('name', $locale) ?? $category->name ?? '');

        return new self((int) $category->getKey(), $name);
    }

    /**
     * Allow select components to consume the DTO via array casting.
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
