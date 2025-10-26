<?php

declare(strict_types=1);

namespace App\Data\Storefront\Home;

use App\Models\Collection as ProductCollection;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;

use function route;

/**
 * DTO describing the curated collection cards displayed on the storefront home.
 */
final class CollectionShowcaseItemData implements Arrayable
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $slug,
        public readonly string $url,
        public readonly ?string $description,
        public readonly int $productsCount,
        public readonly ?string $bannerUrl,
        public readonly ?string $imageUrl,
        public readonly string $initials,
    ) {
        // DTO does not execute logic; comments clarify intent.
    }

    /**
     * Build the DTO from the collection model to avoid Blade poking at relations.
     */
    public static function fromModel(ProductCollection $collection, string $locale): self
    {
        $name = (string) ($collection->getTranslatedName($locale) ?? $collection->name ?? '');
        $slug = (string) ($collection->getTranslatedSlug($locale) ?? $collection->slug ?? (string) $collection->getKey());
        $description = $collection->getTranslatedDescription($locale) ?? $collection->description;

        $bannerUrl = method_exists($collection, 'getBannerUrl')
            ? ($collection->getBannerUrl('lg') ?? $collection->getBannerUrl())
            : null;

        $imageUrl = method_exists($collection, 'getImageUrl')
            ? ($collection->getImageUrl('lg') ?? $collection->getImageUrl())
            : null;

        return new self(
            (int) $collection->getKey(),
            $name,
            $slug,
            route('frontend.collections.show', $slug !== '' ? $slug : $collection->getKey()),
            $description !== '' ? (string) $description : null,
            (int) $collection->products_count,
            $bannerUrl !== '' ? $bannerUrl : null,
            $imageUrl !== '' ? $imageUrl : null,
            Str::upper(Str::of($name)->substr(0, 2)->toString()),
        );
    }

    /**
     * Helper to ensure the view can always fetch an image for the hero slot.
     */
    public function primaryImage(): ?string
    {
        return $this->bannerUrl ?? $this->imageUrl;
    }

    /**
     * Serialise the payload for caching and Livewire dehydration.
     *
     * @return array{
     *     id:int,
     *     name:string,
     *     slug:string,
     *     url:string,
     *     description:?string,
     *     products_count:int,
     *     banner_url:?string,
     *     image_url:?string,
     *     initials:string
     * }
     */
    public function toArray(): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'slug'           => $this->slug,
            'url'            => $this->url,
            'description'    => $this->description,
            'products_count' => $this->productsCount,
            'banner_url'     => $this->bannerUrl,
            'image_url'      => $this->imageUrl,
            'initials'       => $this->initials,
        ];
    }
}
