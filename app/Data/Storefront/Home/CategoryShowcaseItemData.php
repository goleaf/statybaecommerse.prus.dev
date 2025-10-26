<?php

declare(strict_types=1);

namespace App\Data\Storefront\Home;

use App\Models\Category;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;

use function route;

/**
 * DTO representing the information required to render a category showcase card.
 */
final class CategoryShowcaseItemData implements Arrayable
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $slug,
        public readonly string $url,
        public readonly ?string $shortDescription,
        public readonly int $productsCount,
        public readonly ?string $imageUrl,
        public readonly string $initial,
    ) {
        // DTO constructor intentionally empty.
    }

    /**
     * Hydrate the DTO from the category model while keeping localisation intact.
     */
    public static function fromModel(Category $category, string $locale): self
    {
        $name = (string) ($category->trans('name', $locale) ?? $category->name ?? '');
        $slug = (string) ($category->trans('slug', $locale) ?? $category->slug ?? (string) $category->getKey());
        $description = $category->trans('short_description', $locale) ?? $category->short_description;

        $imageUrl = method_exists($category, 'getFirstMediaUrl')
            ? ($category->getFirstMediaUrl('images', 'image-lg') ?: $category->getFirstMediaUrl('images'))
            : null;

        return new self(
            (int) $category->getKey(),
            $name,
            $slug,
            route('localized.categories.show', [
                'locale'   => $locale,
                'category' => $slug !== '' ? $slug : $category->getKey(),
            ]),
            $description !== '' ? (string) $description : null,
            (int) $category->products_count,
            $imageUrl !== '' ? $imageUrl : null,
            Str::upper(Str::of($name)->substr(0, 1)->toString()),
        );
    }

    /**
     * Allow Blade templates to render default initials when imagery is absent.
     */
    public function placeholder(): string
    {
        return $this->initial;
    }

    /**
     * Serialise the payload for Livewire dehydration.
     *
     * @return array{
     *     id:int,
     *     name:string,
     *     slug:string,
     *     url:string,
     *     short_description:?string,
     *     products_count:int,
     *     image_url:?string,
     *     initial:string
     * }
     */
    public function toArray(): array
    {
        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'slug'              => $this->slug,
            'url'               => $this->url,
            'short_description' => $this->shortDescription,
            'products_count'    => $this->productsCount,
            'image_url'         => $this->imageUrl,
            'initial'           => $this->initial,
        ];
    }
}
