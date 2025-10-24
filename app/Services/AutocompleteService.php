<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Attribute;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Lightweight autocomplete service that provides consistent structure for search results.
 * The previous implementation became corrupted; this version focuses on predictable, testable behaviour.
 */
final class AutocompleteService
{
    private const DEFAULT_LIMIT = 10;
    private const MAX_LIMIT = 50;
    private const RECENT_CACHE_LIMIT = 20;
    private const RECENT_TTL_SECONDS = 604800; // 7 days

    /**
     * Perform an autocomplete search across the selected resource types.
     *
     * @param  array<int, string>  $types
     * @return array<int, array<string, mixed>>
     */
    public function search(string $query, int $limit = self::DEFAULT_LIMIT, array $types = []): array
    {
        $normalizedQuery = trim($query);

        if ($normalizedQuery === '') {
            return [];
        }

        $limit = $this->normalizeLimit($limit);
        $types = $this->resolveTypes($types);

        $perTypeLimit = max(1, (int) ceil($limit / count($types)));
        $results = collect();

        foreach ($types as $type) {
            $results = $results->merge(match ($type) {
                'products' => $this->searchProducts($normalizedQuery, $perTypeLimit),
                'categories' => $this->searchCategories($normalizedQuery, $perTypeLimit),
                'brands' => $this->searchBrands($normalizedQuery, $perTypeLimit),
                'collections' => $this->searchCollections($normalizedQuery, $perTypeLimit),
                'attributes' => $this->searchAttributes($normalizedQuery, $perTypeLimit),
                default => [],
            });
        }

        $this->addToRecentSearches($normalizedQuery);

        return $results
            ->unique(fn (array $item) => sprintf('%s:%s', $item['type'] ?? 'unknown', $item['id'] ?? Str::uuid()))
            ->take($limit)
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function searchProducts(string $query, int $limit = self::DEFAULT_LIMIT): array
    {
        $normalizedQuery = trim($query);

        if ($normalizedQuery === '') {
            return [];
        }

        $limit = $this->normalizeLimit($limit);
        $searchTerm = $this->prepareSearchTerm($normalizedQuery);

        return Product::query()
            ->with(['brand'])
            ->where('is_visible', true)
            ->whereNotNull('slug')
            ->where(function (Builder $builder) use ($searchTerm): void {
                $builder
                    ->where('name', 'like', $searchTerm)
                    ->orWhere('sku', 'like', $searchTerm)
                    ->orWhere('description', 'like', $searchTerm);
            })
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get()
            ->map(fn (Product $product) => $this->mapProduct($product, $normalizedQuery))
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function searchCategories(string $query, int $limit = self::DEFAULT_LIMIT): array
    {
        $normalizedQuery = trim($query);

        if ($normalizedQuery === '') {
            return [];
        }

        $limit = $this->normalizeLimit($limit);
        $searchTerm = $this->prepareSearchTerm($normalizedQuery);

        return Category::query()
            ->where('is_visible', true)
            ->whereNotNull('slug')
            ->where(function (Builder $builder) use ($searchTerm): void {
                $builder
                    ->where('name', 'like', $searchTerm)
                    ->orWhere('description', 'like', $searchTerm);
            })
            ->orderBy('name')
            ->limit($limit)
            ->get()
            ->map(fn (Category $category) => $this->mapCategory($category, $normalizedQuery))
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function searchBrands(string $query, int $limit = self::DEFAULT_LIMIT): array
    {
        $normalizedQuery = trim($query);

        if ($normalizedQuery === '') {
            return [];
        }

        $limit = $this->normalizeLimit($limit);
        $searchTerm = $this->prepareSearchTerm($normalizedQuery);

        return Brand::query()
            ->where('is_enabled', true)
            ->where(function (Builder $builder) use ($searchTerm): void {
                $builder
                    ->where('name', 'like', $searchTerm)
                    ->orWhere('description', 'like', $searchTerm);
            })
            ->orderBy('name')
            ->limit($limit)
            ->get()
            ->map(fn (Brand $brand) => $this->mapBrand($brand, $normalizedQuery))
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function searchCollections(string $query, int $limit = self::DEFAULT_LIMIT): array
    {
        $normalizedQuery = trim($query);

        if ($normalizedQuery === '') {
            return [];
        }

        $limit = $this->normalizeLimit($limit);
        $searchTerm = $this->prepareSearchTerm($normalizedQuery);

        return Collection::query()
            ->where('is_visible', true)
            ->whereNotNull('slug')
            ->where(function (Builder $builder) use ($searchTerm): void {
                $builder
                    ->where('name', 'like', $searchTerm)
                    ->orWhere('description', 'like', $searchTerm);
            })
            ->orderBy('name')
            ->limit($limit)
            ->get()
            ->map(fn (Collection $collection) => $this->mapCollection($collection, $normalizedQuery))
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function searchAttributes(string $query, int $limit = self::DEFAULT_LIMIT): array
    {
        $normalizedQuery = trim($query);

        if ($normalizedQuery === '') {
            return [];
        }

        $limit = $this->normalizeLimit($limit);
        $searchTerm = $this->prepareSearchTerm($normalizedQuery);

        return Attribute::query()
            ->where(function (Builder $builder) use ($searchTerm): void {
                $builder
                    ->where('name', 'like', $searchTerm)
                    ->orWhere('code', 'like', $searchTerm);
            })
            ->orderBy('name')
            ->limit($limit)
            ->get()
            ->map(fn (Attribute $attribute) => $this->mapAttribute($attribute, $normalizedQuery))
            ->all();
    }

    /**
     * Popular suggestions leverage simple heuristics so the UI can display meaningful defaults.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getPopularSuggestions(int $limit = 10, array $filters = []): array
    {
        $limit = $this->normalizeLimit($limit);
        $context = $this->normaliseProductContext($filters);
        $cacheKey = $this->popularSuggestionsCacheKey($limit, $context);

        return Cache::remember(
            $cacheKey,
            900,
            function () use ($limit, $context): array {
                $query = Product::query()
                    ->where('is_visible', true)
                    ->orderByDesc('created_at');

                $this->applyProductContextFilters($query, $context);

                return $query
                    ->limit($limit)
                    ->get()
                    ->map(fn (Product $product) => [
                        'type' => 'product',
                        'id' => $product->id,
                        'title' => $product->name,
                        'url' => $this->productUrl($product),
                        'is_popular' => true,
                    ])
                    ->all();
            }
        );
    }

    /**
     * Recently searched terms are scoped per user (or guest).
     *
     * @return array<int, array<string, mixed>>
     */
    public function getRecentSuggestions(int $limit = 5): array
    {
        $limit = $this->normalizeLimit($limit);
        $recent = Cache::get($this->recentCacheKey(), []);

        return array_slice($recent, 0, $limit);
    }

    public function addToRecentSearches(string $query): void
    {
        $normalizedQuery = trim($query);

        if ($normalizedQuery === '') {
            return;
        }

        $key = $this->recentCacheKey();
        $recent = Cache::get($key, []);

        $recent = array_values(array_filter($recent, fn (array $entry) => ($entry['search_term'] ?? '') !== $normalizedQuery));
        array_unshift($recent, [
            'search_term' => $normalizedQuery,
            'searched_at' => Carbon::now()->toIso8601String(),
        ]);

        Cache::put($key, array_slice($recent, 0, self::RECENT_CACHE_LIMIT), self::RECENT_TTL_SECONDS);
    }

    public function clearRecentSearches(): void
    {
        Cache::forget($this->recentCacheKey());
    }

    /**
     * Normalise the requested limit to a sensible range.
     */
    private function normalizeLimit(int $limit): int
    {
        return min(self::MAX_LIMIT, max(1, $limit));
    }

    /**
     * @param  array<int, string>  $types
     * @return array<int, string>
     */
    private function resolveTypes(array $types): array
    {
        if ($types === []) {
            return ['products', 'categories', 'brands', 'collections', 'attributes'];
        }

        $allowed = ['products', 'categories', 'brands', 'collections', 'attributes'];

        return array_values(array_intersect($allowed, array_unique($types)));
    }

    private function prepareSearchTerm(string $query): string
    {
        return '%'.str_replace(['%', '_'], ['\\%', '\\_'], $query).'%';
    }

    private function calculateRelevanceScore(string $haystack, string $needle): int
    {
        if ($haystack === '') {
            return 0;
        }

        similar_text(Str::lower($haystack), Str::lower($needle), $percentage);

        return (int) round($percentage);
    }

    /**
     * @return array<string, mixed>
     */
    private function mapProduct(Product $product, string $query): array
    {
        return [
            'type' => 'product',
            'id' => $product->id,
            'title' => $product->name,
            'subtitle' => $product->brand?->name,
            'url' => $this->productUrl($product),
            'image' => $product->getFirstMediaUrl('images', 'thumb') ?: null,
            'price' => $product->price,
            'formatted_price' => number_format((float) $product->price, 2),
            'relevance_score' => $this->calculateRelevanceScore($product->name, $query),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapCategory(Category $category, string $query): array
    {
        return [
            'type' => 'category',
            'id' => $category->id,
            'title' => $category->name,
            'subtitle' => $category->parent?->name,
            'url' => url('/category/'.$category->slug),
            'relevance_score' => $this->calculateRelevanceScore($category->name ?? '', $query),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapBrand(Brand $brand, string $query): array
    {
        return [
            'type' => 'brand',
            'id' => $brand->id,
            'title' => $brand->name,
            'url' => url('/brands/'.$brand->slug),
            'relevance_score' => $this->calculateRelevanceScore($brand->name ?? '', $query),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapCollection(Collection $collection, string $query): array
    {
        return [
            'type' => 'collection',
            'id' => $collection->id,
            'title' => $collection->name,
            'url' => url('/collections/'.$collection->slug),
            'relevance_score' => $this->calculateRelevanceScore($collection->name ?? '', $query),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapAttribute(Attribute $attribute, string $query): array
    {
        return [
            'type' => 'attribute',
            'id' => $attribute->id,
            'title' => $attribute->name,
            'code' => $attribute->code,
            'relevance_score' => $this->calculateRelevanceScore($attribute->name ?? $attribute->code ?? '', $query),
        ];
    }

    private function productUrl(Product $product): string
    {
        return url('/products/'.$product->slug);
    }

    private function recentCacheKey(): string
    {
        $user = $this->currentUser();

        return 'autocomplete:recent:'.($user?->getAuthIdentifier() ?? 'guest');
    }

    private function currentUser(): ?Authenticatable
    {
        return Auth::user();
    }
}
