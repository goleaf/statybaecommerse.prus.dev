<?php

declare(strict_types=1);

namespace App\Support\Cache;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Observers\Concerns\ResolvesSupportedLocales;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Cache;

final class CacheInvalidator
{
    use ResolvesSupportedLocales;

    public function productChanged(Product $product): void
    {
        $categoryIds = $this->resolveCategoryIds($product);

        $productId = $product->getKey();

        if (! is_numeric($productId)) {
            return;
        }

        $productId = (int) $productId;

        $tags = [
            CacheKeys::homeTag(),
            CacheKeys::productAggregateTag(),
            CacheKeys::productTag($productId),
        ];

        if (is_numeric($product->brand_id)) {
            $tags[] = CacheKeys::brandTag((int) $product->brand_id);
        }

        foreach ($categoryIds as $categoryId) {
            $tags[] = CacheKeys::categoryTag($categoryId);
        }

        TagAwareCache::flush($tags);

        if (! Cache::supportsTags()) {
            $this->forgetProductFallbackCaches($categoryIds);
        }
    }

    public function variantChanged(ProductVariant $variant): void
    {
        $product = $variant->relationLoaded('product')
            ? $variant->getRelation('product')
            : $variant->product()->withoutGlobalScopes()->first(['id', 'brand_id']);

        if (! $product instanceof Product) {
            return;
        }

        $this->productChanged($product);
    }

    public function categoryChanged(Category $category): void
    {
        $categoryId = $category->getKey();

        if (! is_numeric($categoryId)) {
            return;
        }

        $categoryId = (int) $categoryId;

        $tags = [
            CacheKeys::homeTag(),
            CacheKeys::categoryTag($categoryId),
        ];

        TagAwareCache::flush($tags);

        if (! Cache::supportsTags()) {
            $this->forgetCategoryFallbackCaches($categoryId);
        }
    }

    /**
     * @return array<int, int>
     */
    private function resolveCategoryIds(Product $product): array
    {
        if ($product->relationLoaded('categories')) {
            $categories = $product->getRelation('categories');

            if (! $categories instanceof Collection) {
                return [];
            }

            /** @var array<int, mixed> $categoryIds */
            $categoryIds = $categories->pluck('id')->all();

            return $this->mapCategoryIds($categoryIds);
        }

        /** @var BelongsToMany<Category, Product> $categoriesRelation */
        $categoriesRelation = $product->categories();

        /** @var array<int, mixed> $categoryIds */
        $categoryIds = $categoriesRelation->pluck('categories.id')->all();

        return $this->mapCategoryIds($categoryIds);
    }

    /**
     * @param  array<int, mixed>  $ids
     * @return array<int, int>
     */
    private function mapCategoryIds(array $ids): array
    {
        $numericIds = array_filter($ids, static fn ($id): bool => is_numeric($id));

        return array_values(array_map(static fn ($id): int => (int) $id, $numericIds));
    }

    /**
     * @param  array<int, int>  $categoryIds
     */
    private function forgetProductFallbackCaches(array $categoryIds): void
    {
        Cache::forget(CacheKeys::productTotalCount());

        foreach ($this->supportedLocales() as $locale) {
            Cache::forget(CacheKeys::dashboardMetric('low_stock_items', $locale));
            Cache::forget(CacheKeys::homeStats($locale));
            Cache::forget(CacheKeys::homeFeaturedProducts($locale));
            Cache::forget(CacheKeys::homeLatestProducts($locale));
            Cache::forget(CacheKeys::homeLatestReviews($locale));
            Cache::forget(CacheKeys::homeCollections($locale));
            Cache::forget(CacheKeys::homeSliders($locale));
            Cache::forget(CacheKeys::homeCatalogueCategories($locale));
            Cache::forget(CacheKeys::homeCategoryTree($locale));

            foreach (['featured', 'latest', 'sale', 'trending'] as $preset) {
                foreach ([4, 8, 12] as $limit) {
                    Cache::forget(CacheKeys::homeShelf($preset, $limit, $locale));
                }
            }

            Cache::forget("category_accordion_tree:{$locale}");
            Cache::forget("category_tree:{$locale}");
            Cache::forget("category_nav_tree:{$locale}");
            Cache::forget("mobile_category_tree:{$locale}");
            Cache::forget('nav:header_menu:'.$locale);
            Cache::forget('nav:main_categories:'.$locale);
            Cache::forget('nav:featured_brands:'.$locale);
            Cache::forget('nav:featured_collections:'.$locale);
        }

        foreach ([6, 10] as $limit) {
            Cache::forget(CacheKeys::categoryPopularList($limit));
            Cache::forget(CacheKeys::brandTopList($limit));
        }

        Cache::forget(CacheKeys::productFeaturedList(8));
        Cache::forget(CacheKeys::categoryNavigationTree());
    }

    private function forgetCategoryFallbackCaches(int $categoryId): void
    {
        foreach ($this->supportedLocales() as $locale) {
            Cache::forget(CacheKeys::homeCategoryTree($locale));
            Cache::forget("category_accordion_tree:{$locale}");
            Cache::forget("category_tree:{$locale}");
            Cache::forget("category_nav_tree:{$locale}");
            Cache::forget("mobile_category_tree:{$locale}");
            Cache::forget('nav:main_categories:'.$locale);
        }

        Cache::forget(CacheKeys::categoryNavigationTree());
    }
}
