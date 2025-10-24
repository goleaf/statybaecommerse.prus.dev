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
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\EnabledScope;
use App\Models\Scopes\PublishedScope;
use App\Models\Scopes\StatusScope;
use App\Models\Scopes\VisibleScope;
use Illuminate\Database\Eloquent\Builder;

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
            ->withoutGlobalScopes([ActiveScope::class, PublishedScope::class, VisibleScope::class])
            ->where('is_visible', true)
            ->where(static function ($builder) use ($criteria): void {
                $term = $criteria->getQuery();
                $builder->where('name', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%")
                    ->orWhere('sku', 'like', "%{$term}%");
            })
            ->with([
                'brand' => static fn ($relation) => $relation->withoutGlobalScopes([ActiveScope::class, EnabledScope::class]),
                'categories' => static fn ($relation) => $relation->withoutGlobalScopes([ActiveScope::class, EnabledScope::class, VisibleScope::class]),
                'variants' => static fn ($relation) => $relation->withoutGlobalScopes([ActiveScope::class, EnabledScope::class, StatusScope::class]),
                'media',
            ])
            ->withSum([
                // Eager-load active reservation totals so stock checks avoid N+1 aggregate queries.
                'stockReservations as reserved_stock_quantity' => static function (Builder $relation): void {
                    $relation->active();
                },
            ], 'quantity');

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
            ->withoutGlobalScopes([ActiveScope::class, PublishedScope::class, VisibleScope::class])
            ->where('is_visible', true)
            ->with([
                'brand' => static fn ($relation) => $relation->withoutGlobalScopes([ActiveScope::class, EnabledScope::class]),
                'categories' => static fn ($relation) => $relation->withoutGlobalScopes([ActiveScope::class, EnabledScope::class, VisibleScope::class]),
                'variants' => static fn ($relation) => $relation->withoutGlobalScopes([ActiveScope::class, EnabledScope::class, StatusScope::class]),
                'media',
            ])
            ->withSum([
                // Keep reservation totals consistent in catalog listings as well for parity with search results.
                'stockReservations as reserved_stock_quantity' => static function (Builder $relation): void {
                    $relation->active();
                },
            ], 'quantity');

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
            ->withSum([
                // Ensure detail views reuse the same eager-loaded reservation aggregates.
                'stockReservations as reserved_stock_quantity' => static function (Builder $relation): void {
                    $relation->active();
                },
            ], 'quantity')
            ->first();

        return $product ? $this->mapToDomainProduct($product) : null;
    }

    private function mapToDomainProduct(Product $product): DomainProduct
    {
        // Resolve translated textual fields before building the domain entity so
        // API consumers always receive locale-appropriate strings instead of
        // raw translation arrays from the JSON columns.
        $name = $this->resolveTranslatableString($product, 'name') ?? '';
        $slug = $this->resolveTranslatableString($product, 'slug') ?? '';
        $description = $this->resolveTranslatableString($product, 'description');
        $shortDescription = $this->resolveTranslatableString($product, 'short_description');

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

        // Derive inventory booleans from preloaded aggregates to avoid calling helper methods that trigger extra queries.
        $manageStock = (bool) $product->manage_stock;
        $stockQuantity = (int) ($product->stock_quantity ?? 0);
        $reservedQuantity = (int) ($product->reserved_stock_quantity ?? 0);
        $availableQuantity = $manageStock ? max($stockQuantity - $reservedQuantity, 0) : $stockQuantity;
        $isInStock = $manageStock ? $availableQuantity > 0 : true;

        return new DomainProduct(
            $product->id,
            $name,
            $slug,
            (string) $product->sku,
            (float) $product->price,
            $product->sale_price !== null ? (float) $product->sale_price : null,
            $brand,
            $category,
            (bool) $product->is_visible,
            (bool) $product->is_featured,
            $manageStock,
            $isInStock,
            $availableQuantity,
            $images,
            $variants,
            $description,
            $shortDescription,
        );
    }

    /**
     * Extract a translated string from the JSON-backed attributes with sensible fallbacks.
     */
    private function resolveTranslatableString(Product $product, string $attribute): ?string
    {
        // Access the raw attribute directly to avoid triggering additional queries during hydration.
        $value = $product->getAttribute($attribute);

        if (is_string($value) && $value !== '') {
            return $value;
        }

        if (is_array($value) && $value !== []) {
            $locale = app()->getLocale();
            $fallbackLocale = config('app.fallback_locale');

            // Prefer the currently active locale whenever it exists in the payload.
            if (isset($value[$locale]) && is_string($value[$locale]) && $value[$locale] !== '') {
                return $value[$locale];
            }

            // Fall back to the configured fallback locale if that translation exists.
            if (is_string($fallbackLocale)
                && isset($value[$fallbackLocale])
                && is_string($value[$fallbackLocale])
                && $value[$fallbackLocale] !== '') {
                return $value[$fallbackLocale];
            }

            // As a last resort, return the first non-empty string we can find.
            foreach ($value as $candidate) {
                if (is_string($candidate) && $candidate !== '') {
                    return $candidate;
                }
            }
        }

        // When nothing usable exists, signal the absence with null so callers can decide on a default.
        return null;
    }
}
