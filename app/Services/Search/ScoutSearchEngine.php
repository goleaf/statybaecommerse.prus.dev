<?php

declare(strict_types=1);

namespace App\Services\Search;

use App\Data\SearchQueryData;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Number;
use Laravel\Scout\Builder as ScoutBuilder;

final class ScoutSearchEngine
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function searchProducts(SearchQueryData $queryData, int $limit): array
    {
        $results = $this->builderFor(Product::class, $queryData->query(), $limit)->get();
        $results->load('brand');

        $payload = [];

        foreach ($results as $productModel) {
            if (! $productModel instanceof Product) {
                continue;
            }

            $product = $productModel;
            $price = (float) ($product->price ?? 0);

            $payload[] = [
                'id'              => $product->getKey(),
                'type'            => 'product',
                'title'           => $product->name,
                'subtitle'        => $product->brand?->name,
                'description'     => $product->short_description ?: ($product->description ?: null),
                'price'           => $price,
                'formatted_price' => (string) Number::currency($price, current_currency(), app()->getLocale()),
                'image'           => $product->thumbnail ?? $product->main_image,
                'url'             => route('products.show', $product->slug),
                'relevance_score' => 1.0,
                'sales_count'     => (int) ($product->sales_count ?? 0),
                'reviews_count'   => (int) ($product->reviews_count ?? 0),
                'average_rating'  => (float) ($product->average_rating ?? 0),
                'is_featured'     => (bool) $product->is_featured,
            ];
        }

        return $payload;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function searchCategories(SearchQueryData $queryData, int $limit): array
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, Category> $results */
        $results = $this->builderFor(Category::class, $queryData->query(), $limit)->get();
        $payload = [];

        foreach ($results as $categoryModel) {
            if (! $categoryModel instanceof Category) {
                continue;
            }

            $category = $categoryModel;
            $productsCount = $category->products()
                ->where('is_visible', true)
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->count();
            $childrenCount = $category->children()->count();

            $payload[] = [
                'id'              => $category->getKey(),
                'type'            => 'category',
                'title'           => $category->name,
                'subtitle'        => __('frontend.search.category_with_products', ['count' => $productsCount]),
                'description'     => $category->description ?: ($category->short_description ?? null),
                'image'           => null,
                'url'             => route('categories.show', $category->slug),
                'products_count'  => $productsCount,
                'children_count'  => $childrenCount,
                'relevance_score' => 1.0,
            ];
        }

        return $payload;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function searchBrands(SearchQueryData $queryData, int $limit): array
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, Brand> $results */
        $results = $this->builderFor(Brand::class, $queryData->query(), $limit)->get();
        $payload = [];

        foreach ($results as $brandModel) {
            if (! $brandModel instanceof Brand) {
                continue;
            }

            $brand = $brandModel;
            $productsCount = $brand->products()
                ->where('is_visible', true)
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->count();

            $payload[] = [
                'id'              => $brand->getKey(),
                'type'            => 'brand',
                'title'           => $brand->name,
                'subtitle'        => __('frontend.search.brand_with_products', ['count' => $productsCount]),
                'description'     => $brand->description,
                'image'           => $brand->logo,
                'url'             => route('brands.show', $brand->slug),
                'products_count'  => $productsCount,
                'relevance_score' => 1.0,
            ];
        }

        return $payload;
    }

    /**
     * @param  class-string                                      $modelClass
     * @return ScoutBuilder<\Illuminate\Database\Eloquent\Model>
     */
    private function builderFor(string $modelClass, string $query, int $limit): ScoutBuilder
    {
        $configuredMax = config('search.scout.max_results');
        $maxResults = is_int($configuredMax) ? $configuredMax : 200;
        $limit = max(1, min($limit, $maxResults));

        /** @var ScoutBuilder<\Illuminate\Database\Eloquent\Model> $builder */
        $builder = $modelClass::search($query);

        return $builder->take($limit);
    }
}
