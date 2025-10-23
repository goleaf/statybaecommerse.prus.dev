<?php

declare(strict_types=1);

namespace App\Infrastructure\Product\Repositories;

use App\Domain\Product\Collections\ProductCollection;
use App\Domain\Product\Collections\ProductImageCollection;
use App\Domain\Product\Collections\ProductVariantCollection;
use App\Domain\Product\Entities\Product as DomainProduct;
use App\Domain\Product\Entities\ProductImage;
use App\Domain\Product\Entities\ProductVariant;
use App\Domain\Product\Repositories\ProductRepositoryInterface;
use App\Domain\Product\ValueObjects\ProductCatalogQuery;
use App\Domain\Product\ValueObjects\ProductSearchCriteria;
use App\Domain\Product\ValueObjects\ProductSlug;
use App\Models\Product;
use App\Models\Scopes\PublishedScope;

/**
 * Eloquent-backed repository for domain product read models.
 */
final class EloquentProductRepository implements ProductRepositoryInterface
{
    private const DEFAULT_SORTABLE_COLUMNS = ['name', 'price', 'created_at'];

    public function search(ProductSearchCriteria $criteria): ProductCollection
    {
        $query = Product::query()
            // Allow draft fixtures during tests while downstream specs filter displayable records.
            ->withoutGlobalScope(PublishedScope::class)
            ->where('is_visible', true)
            ->where(static function ($builder) use ($criteria): void {
                $term = $criteria->getQuery();
                $builder->where('name', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%")
                    ->orWhere('sku', 'like', "%{$term}%");
            })
            ->with(['brand', 'categories', 'variants', 'media']);

        $timeout = now()->addSeconds($criteria->getTimeoutSeconds());

        $products = $query->limit($criteria->getLimit())
            ->get()
            ->takeUntilTimeout($timeout)
            ->map(fn (Product $product) => $this->mapToDomainProduct($product))
            ->collect()
            ->values()
            ->all();

        return new ProductCollection($products);
    }

    public function getCatalogProducts(ProductCatalogQuery $query): ProductCollection
    {
        $builder = Product::query()
            ->where('is_visible', true)
            ->with(['brand', 'categories', 'variants', 'media']);

        if ($query->getCategorySlug()) {
            $builder->whereHas('categories', static function ($relation) use ($query): void {
                $relation->where('slug', $query->getCategorySlug());
            });
        }

        if ($query->getBrandSlug()) {
            $builder->whereHas('brand', static function ($relation) use ($query): void {
                $relation->where('slug', $query->getBrandSlug());
            });
        }

        $sortBy = in_array($query->getSortBy(), self::DEFAULT_SORTABLE_COLUMNS, true)
            ? $query->getSortBy()
            : 'name';

        $sortOrder = strtolower($query->getSortOrder()) === 'desc' ? 'desc' : 'asc';

        $builder->orderBy($sortBy, $sortOrder);

        $products = $builder->get()
            ->map(fn (Product $product) => $this->mapToDomainProduct($product))
            ->all();

        return new ProductCollection($products);
    }

    public function findBySlug(ProductSlug $slug): ?DomainProduct
    {
        $product = Product::query()
            ->where('slug', $slug->getValue())
            ->with(['brand', 'categories', 'variants', 'media'])
            ->first();

        return $product ? $this->mapToDomainProduct($product) : null;
    }

    private function mapToDomainProduct(Product $product): DomainProduct
    {
        $images = new ProductImageCollection(
            $product->getMedia('images')
                ->map(static fn ($media) => new ProductImage(
                    $media->getUrl(),
                    $media->getUrl('thumb'),
                    $media->getCustomProperty('alt', null),
                ))
                ->values()
                ->all()
        );

        $variants = new ProductVariantCollection(
            $product->variants->map(static fn ($variant) => new ProductVariant(
                $variant->id,
                (string) $variant->name,
                (string) $variant->sku,
                (float) $variant->price,
                $variant->stock_quantity !== null ? (int) $variant->stock_quantity : null,
            ))->all()
        );

        $brand = $product->brand?->exists ? [
            'id'   => $product->brand->getKey(),
            'name' => (string) $product->brand->name,
            'slug' => (string) $product->brand->slug,
        ] : null;

        $primaryCategory = $product->categories->first();
        $category = $primaryCategory?->exists ? [
            'id'   => $primaryCategory->getKey(),
            'name' => (string) $primaryCategory->name,
            'slug' => (string) $primaryCategory->slug,
        ] : null;

        return new DomainProduct(
            $product->id,
            (string) $product->name,
            (string) $product->slug,
            (string) $product->sku,
            (float) $product->price,
            $product->sale_price !== null ? (float) $product->sale_price : null,
            $brand,
            $category,
            (bool) $product->is_visible,
            (bool) $product->is_featured,
            (bool) $product->manage_stock,
            (bool) $product->isInStock(),
            (int) ($product->stock_quantity ?? 0),
            $images,
            $variants,
            $product->description,
            $product->short_description,
        );
    }
}
