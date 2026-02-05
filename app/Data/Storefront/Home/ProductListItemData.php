<?php

declare(strict_types=1);

namespace App\Data\Storefront\Home;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;

use function collect;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

use function route;

/**
 * Value object describing the minimal product details required for the
 * storefront cards so Blade never needs to poke at Eloquent internals.
 */
final class ProductListItemData implements Arrayable
{
    /**
     * Specify the minimal fields required from the products table for list views.
     * This helps optimize queries by avoiding loading unnecessary large text fields.
     */
    public const REQUIRED_PRODUCT_FIELDS = [
        'products.id',
        'products.name',
        'products.slug',
        'products.short_description', // Used for short description, NOT description
        'products.price',
        'products.stock_quantity',
        'products.brand_id',
        'products.created_at', // May be needed for sorting
        'products.updated_at', // May be needed for sorting
        'products.published_at', // May be needed for sorting
        'products.is_featured', // May be needed for sorting
    ];

    /**
     * Specify the minimal fields required for brand relation in list views.
     */
    public const REQUIRED_BRAND_FIELDS = [
        'id',
        'name',
        'slug', // May be needed for URLs
    ];

    /**
     * Specify the minimal fields required for category relation in list views.
     */
    public const REQUIRED_CATEGORY_FIELDS = [
        'id',
        'name',
        'slug', // May be needed for URLs
    ];

    /**
     * @param array<int, string> $categoryLabels
     */
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $slug,
        public readonly string $detailUrl,
        public readonly ?string $brandName,
        public readonly array $categoryLabels,
        public readonly float $price,
        public readonly ?float $averageRating,
        public readonly int $reviewsCount,
        public readonly int $stockQuantity,
        public readonly ?string $imageUrl,
        public readonly string $initials,
        public readonly ?string $shortDescription,
    ) {
        // Intentionally empty: DTO only stores data and exposes typed helpers below.
    }

    /**
     * Build the DTO from a hydrated Eloquent model while sanitising relation
     * access to avoid lazy loading inside the cached payload.
     */
    public static function fromModel(Product $product, string $locale): self
    {
        $name = (string) ($product->trans('name', $locale) ?? $product->name ?? '');
        $slug = (string) ($product->trans('slug', $locale) ?? $product->slug ?? (string) $product->getKey());

        /** @var Brand|null $brand */
        $brand = $product->brand;
        $brandName = $brand instanceof Brand
            ? (string) ($brand->trans('name', $locale) ?? $brand->name ?? '')
            : null;

        /** @var Collection<int, Category> $categories */
        $categories = $product->categories instanceof Collection
            ? $product->categories
            : collect();

        $categoryLabels = $categories
            ->map(static fn (Category $category): ?string => (string) ($category->trans('name', $locale) ?? $category->name ?? ''))
            ->filter(static fn (?string $label): bool => $label !== null && $label !== '')
            ->values()
            ->all();

        $price = (float) $product->price;

        $imageUrl = null;
        if (method_exists($product, 'getMainImage')) {
            $imageUrl = $product->getMainImage('image-lg') ?? $product->getMainImage();
        }
        if ($imageUrl === null && method_exists($product, 'getFirstMediaUrl')) {
            $imageUrl = $product->getFirstMediaUrl('images', 'image-lg')
                ?: $product->getFirstMediaUrl('images');
        }

        $detailUrl = route('localized.products.show', [
            'locale'  => $locale,
            'product' => $slug !== '' ? $slug : $product->getKey(),
        ]);

        $shortDescription = (string) ($product->trans('short_description', $locale) ?? $product->short_description ?? '');
        $shortDescription = $shortDescription !== '' ? $shortDescription : null;

        return new self(
            (int) $product->getKey(),
            $name,
            $slug,
            $detailUrl,
            $brandName !== '' ? $brandName : null,
            $categoryLabels,
            $price,
            $product->average_rating !== null ? (float) $product->average_rating : null,
            (int) $product->reviews_count,
            (int) $product->stock_quantity,
            $imageUrl !== '' ? $imageUrl : null,
            Str::upper(Str::of($name)->substr(0, 2)->toString()),
            $shortDescription,
        );
    }

    /**
     * Determine whether the product is currently in stock.
     */
    public function inStock(): bool
    {
        return $this->stockQuantity > 0;
    }

    /**
     * Convenience accessor for the precomputed brand label.
     */
    public function hasBrand(): bool
    {
        return $this->brandName !== null && $this->brandName !== '';
    }

    /**
     * Provide typed access for JSON serialisation or Livewire dehydration.
     *
     * @return array{
     *     id:int,
     *     name:string,
     *     slug:string,
     *     detail_url:string,
     *     brand_name:?string,
     *     category_labels:array<int, string>,
     *     price:float,
     *     average_rating:?float,
     *     reviews_count:int,
     *     stock_quantity:int,
     *     image_url:?string,
     *     initials:string,
     *     short_description:?string
     * }
     */
    public function toArray(): array
    {
        return [
            'id'                  => $this->id,
            'name'                => $this->name,
            'slug'                => $this->slug,
            'detail_url'          => $this->detailUrl,
            'brand_name'          => $this->brandName,
            'category_labels'     => $this->categoryLabels,
            'price'               => $this->price,
            'average_rating'      => $this->averageRating,
            'reviews_count'       => $this->reviewsCount,
            'stock_quantity'      => $this->stockQuantity,
            'image_url'           => $this->imageUrl,
            'initials'            => $this->initials,
            'short_description'   => $this->shortDescription,
        ];
    }
}
