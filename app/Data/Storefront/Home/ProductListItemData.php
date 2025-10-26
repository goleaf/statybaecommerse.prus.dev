<?php

declare(strict_types=1);

namespace App\Data\Storefront\Home;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use function collect;
use function route;

/**
 * Value object describing the minimal product details required for the
 * storefront cards so Blade never needs to poke at Eloquent internals.
 */
final class ProductListItemData implements Arrayable
{
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
        public readonly ?float $salePrice,
        public readonly ?float $comparePrice,
        public readonly ?float $averageRating,
        public readonly int $reviewsCount,
        public readonly int $stockQuantity,
        public readonly ?float $discountPercentage,
        public readonly ?string $imageUrl,
        public readonly string $initials,
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
        $salePrice = $product->sale_price !== null ? (float) $product->sale_price : null;
        $comparePrice = $product->compare_price !== null ? (float) $product->compare_price : null;

        $effectivePrice = $salePrice !== null && $salePrice > 0 && $salePrice < $price
            ? $salePrice
            : $price;

        $referencePrice = null;

        if ($comparePrice !== null && $comparePrice > $effectivePrice) {
            $referencePrice = $comparePrice;
        } elseif ($salePrice !== null && $salePrice < $price) {
            $referencePrice = $price;
        }

        $discountPercentage = null;
        if ($referencePrice !== null && $referencePrice > 0 && $referencePrice > $effectivePrice) {
            $discountPercentage = round((($referencePrice - $effectivePrice) / $referencePrice) * 100, 2);
        }

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

        return new self(
            (int) $product->getKey(),
            $name,
            $slug,
            $detailUrl,
            $brandName !== '' ? $brandName : null,
            $categoryLabels,
            $price,
            $salePrice,
            $comparePrice,
            $product->average_rating !== null ? (float) $product->average_rating : null,
            (int) $product->reviews_count,
            (int) $product->stock_quantity,
            $discountPercentage,
            $imageUrl !== '' ? $imageUrl : null,
            Str::upper(Str::of($name)->substr(0, 2)->toString()),
        );
    }

    /**
     * Determine which price should be displayed to the customer.
     */
    public function currentPrice(): float
    {
        if ($this->salePrice !== null && $this->salePrice > 0 && $this->salePrice < $this->price) {
            return $this->salePrice;
        }

        return $this->price;
    }

    /**
     * Expose the price that should be crossed out when a discount is active.
     */
    public function compareAtPrice(): ?float
    {
        if ($this->comparePrice !== null && $this->comparePrice > $this->currentPrice()) {
            return $this->comparePrice;
        }

        if ($this->salePrice !== null && $this->salePrice < $this->price) {
            return $this->price;
        }

        return null;
    }

    /**
     * Flag used by the Blade template to toggle sale badges.
     */
    public function hasDiscount(): bool
    {
        return $this->compareAtPrice() !== null;
    }

    /**
     * Return a simplified integer percentage for UI badges.
     */
    public function discountBadge(): ?int
    {
        if (! $this->hasDiscount() || $this->discountPercentage === null) {
            return null;
        }

        return (int) round($this->discountPercentage);
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
     *     sale_price:?float,
     *     compare_price:?float,
     *     average_rating:?float,
     *     reviews_count:int,
     *     stock_quantity:int,
     *     discount_percentage:?float,
     *     image_url:?string,
     *     initials:string
     * }
     */
    public function toArray(): array
    {
        return [
            'id'                   => $this->id,
            'name'                 => $this->name,
            'slug'                 => $this->slug,
            'detail_url'           => $this->detailUrl,
            'brand_name'           => $this->brandName,
            'category_labels'      => $this->categoryLabels,
            'price'                => $this->price,
            'sale_price'           => $this->salePrice,
            'compare_price'        => $this->comparePrice,
            'average_rating'       => $this->averageRating,
            'reviews_count'        => $this->reviewsCount,
            'stock_quantity'       => $this->stockQuantity,
            'discount_percentage'  => $this->discountPercentage,
            'image_url'            => $this->imageUrl,
            'initials'             => $this->initials,
        ];
    }
}
