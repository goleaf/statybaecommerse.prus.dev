<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Address;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\City;
use App\Models\Collection;
use App\Models\Country;
use App\Models\Location;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

/**
 * AutocompleteService
 *
 * Service class containing AutocompleteService business logic, external integrations, and complex operations with proper error handling and logging.
 */
final class AutocompleteService
{
    private const CACHE_TTL = 300;

    // 5 minutes
    private const DEFAULT_LIMIT = 10;

    private const MAX_LIMIT = 50;

    /**
     * Handle search functionality with proper error handling.
     */
    public function search(string $query, int $limit = self::DEFAULT_LIMIT, array $types = []): array
    {
        // Respect minimum length at the caller level (LiveSearch component). Allow 1-char searches here.
        if (strlen($query) < 1) {
            return [];
        }

        $startTime = microtime(true);
        $cacheKey = $this->generateCacheKey($query, $limit, $types);

        // Try to get from intelligent cache first
        $cacheService = app(\App\Services\SearchCacheService::class);
        $results = $cacheService->getCachedResults($cacheKey);

        if ($results === null) {
            $results = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($query, $limit, $types) {
                $results = [];
                $locale = app()->getLocale();
                // If no specific types requested, search all
                if (empty($types)) {
                    $types = ['products', 'categories', 'brands', 'collections', 'attributes'];
                }
                // Calculate limits for each type based on total limit
                $typeLimits = $this->calculateTypeLimits($types, $limit);
                // Search each type
                foreach ($types as $type) {
                    $typeLimit = $typeLimits[$type] ?? 0;
                    if ($typeLimit > 0) {
                        $typeResults = $this->searchByType($type, $query, $typeLimit, $locale);
                        $results = array_merge($results, $typeResults);
                    }
                }

                // Sort by relevance and limit final results
                return $this->sortByRelevance($results, $query, $limit);
            });

            // Store in intelligent cache
            $context = [
                'user_id' => auth()->id(),
                'types' => $types,
                'limit' => $limit,
            ];
            $cacheService->cacheSearchResults($cacheKey, $results, $query, $context);
        }

        $executionTime = microtime(true) - $startTime;

        // Track search analytics
        $this->trackSearchAnalytics($query, count($results));

        // Track performance metrics
        $this->trackSearchPerformance($query, $executionTime, count($results), implode(',', $types));

        // Apply search highlighting
        $results = $this->applySearchHighlighting($results, $query);

        return $results;
    }

    /**
     * Handle searchProducts functionality with proper error handling.
     */
    public function searchProducts(string $query, int $limit = self::DEFAULT_LIMIT): array
    {
        $cacheKey = "autocomplete_products_{$query}_{$limit}_".app()->getLocale();

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($query, $limit) {
            $locale = app()->getLocale();
            $searchTerm = $this->prepareSearchTerm($query);
            $products = Product::query()->with(['media', 'brand', 'categories', 'variants'])->where('is_visible', true)->whereNotNull('published_at')->where('published_at', '<=', now())->where(function ($q) use ($searchTerm, $locale) {
                // Exact match (highest priority)
                $q->where('name', 'like', $searchTerm)->orWhere('sku', 'like', $searchTerm)->orWhere('description', 'like', $searchTerm)->orWhereHas('brand', function ($brandQuery) use ($searchTerm) {
                    $brandQuery->where('name', 'like', $searchTerm);
                })->orWhereHas('categories', function ($catQuery) use ($searchTerm) {
                    $catQuery->where('name', 'like', $searchTerm);
                })->orWhereExists(function ($sq) use ($searchTerm, $locale) {
                    $sq->selectRaw('1')->from('product_translations as t')->whereColumn('t.product_id', 'products.id')->where('t.locale', $locale)->where(function ($tw) use ($searchTerm) {
                        $tw->where('t.name', 'like', $searchTerm)->orWhere('t.description', 'like', $searchTerm);
                    });
                });
            })->orderByRaw("\n                    CASE \n                        WHEN name LIKE ? THEN 1\n                        WHEN sku LIKE ? THEN 2\n                        WHEN description LIKE ? THEN 3\n                        ELSE 4\n                    END\n                ", ["%{$query}%", "%{$query}%", "%{$query}%"])->limit($limit)->cursor()->takeUntilTimeout(now()->addSeconds(5))->collect();

            return Arr::from($products->skipWhile(function (Product $product) {
                // Skip products that are not properly configured or have missing essential data
                return empty($product->name) || ! $product->is_visible || $product->price <= 0 || empty($product->slug);
            })->map(function (Product $product) use ($query, $locale) {
                return ['id' => $product->id, 'type' => 'product', 'title' => $product->getTranslatedName($locale), 'subtitle' => $product->brand?->name, 'description' => Str::limit($product->getTranslatedDescription($locale), 100), 'url' => route('localized.products.show', ['locale' => $locale, 'product' => $product->slug]), 'image' => $product->getFirstMediaUrl('images', 'thumb'), 'price' => $product->getPrice(), 'formatted_price' => $product->getFormattedPrice(), 'sku' => $product->sku, 'in_stock' => $product->isInStock(), 'relevance_score' => $this->calculateRelevanceScore($product->getTranslatedName($locale), $query)];
            }));
        });
    }

    /**
     * Handle searchCategories functionality with proper error handling.
     */
    public function searchCategories(string $query, int $limit = self::DEFAULT_LIMIT): array
    {
        $cacheKey = "autocomplete_categories_{$query}_{$limit}_".app()->getLocale();

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($query, $limit) {
            $locale = app()->getLocale();
            $searchTerm = $this->prepareSearchTerm($query);
            $categories = Category::query()->with(['parent', 'children', 'media'])->where('is_visible', true)->where(function ($q) use ($searchTerm, $locale) {
                $q->where('name', 'like', $searchTerm)->orWhere('description', 'like', $searchTerm)->orWhereExists(function ($sq) use ($searchTerm, $locale) {
                    $sq->selectRaw('1')->from('category_translations as t')->whereColumn('t.category_id', 'categories.id')->where('t.locale', $locale)->where(function ($tw) use ($searchTerm) {
                        $tw->where('t.name', 'like', $searchTerm)->orWhere('t.description', 'like', $searchTerm);
                    });
                });
            })->orderByRaw("\n                    CASE \n                        WHEN name LIKE ? THEN 1\n                        WHEN description LIKE ? THEN 2\n                        ELSE 3\n                    END\n                ", ["%{$query}%", "%{$query}%"])->limit($limit)->cursor()->takeUntilTimeout(now()->addSeconds(5))->collect();

            return Arr::from($categories->skipWhile(function (Category $category) {
                // Skip categories that are not properly configured or have missing essential data
                return empty($category->name) || ! $category->is_visible || empty($category->slug);
            })->map(function (Category $category) use ($query, $locale) {
                $title = method_exists($category, 'getTranslatedName') ? $category->getTranslatedName($locale) : ($category->name ?? '');
                $subtitle = $category->parent ? (method_exists($category->parent, 'getTranslatedName') ? $category->parent->getTranslatedName($locale) : ($category->parent->name ?? null)) : null;
                $description = method_exists($category, 'getTranslatedDescription') ? $category->getTranslatedDescription($locale) : ($category->description ?? '');
                $url = Route::has('localized.category.show') ? route('localized.category.show', ['locale' => $locale, 'category' => $category->slug]) : url('/category/'.$category->slug);

                return [
                    'id' => $category->id,
                    'type' => 'category',
                    'title' => $title,
                    'subtitle' => $subtitle,
                    'description' => Str::limit($description, 100),
                    'url' => $url,
                    'image' => $category->getFirstMediaUrl('images', 'thumb'),
                    'products_count' => $category->products()->count(),
                    'children_count' => $category->children()->count(),
                    'relevance_score' => $this->calculateRelevanceScore($title, $query),
                ];
            }));
        });
    }

    /**
     * Handle searchBrands functionality with proper error handling.
     */
    public function searchBrands(string $query, int $limit = self::DEFAULT_LIMIT): array
    {
        $cacheKey = "autocomplete_brands_{$query}_{$limit}_".app()->getLocale();

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($query, $limit) {
            $locale = app()->getLocale();
            $searchTerm = $this->prepareSearchTerm($query);
            $brands = Brand::query()->with(['media'])->where('is_visible', true)->where(function ($q) use ($searchTerm, $locale) {
                $q->where('name', 'like', $searchTerm)->orWhere('description', 'like', $searchTerm)->orWhereExists(function ($sq) use ($searchTerm, $locale) {
                    $sq->selectRaw('1')->from('brand_translations as t')->whereColumn('t.brand_id', 'brands.id')->where('t.locale', $locale)->where(function ($tw) use ($searchTerm) {
                        $tw->where('t.name', 'like', $searchTerm)->orWhere('t.description', 'like', $searchTerm);
                    });
                });
            })->orderByRaw("\n                    CASE \n                        WHEN name LIKE ? THEN 1\n                        WHEN description LIKE ? THEN 2\n                        ELSE 3\n                    END\n                ", ["%{$query}%", "%{$query}%"])->limit($limit)->cursor()->takeUntilTimeout(now()->addSeconds(5))->collect();

            return Arr::from($brands->skipWhile(function (Brand $brand) {
                // Skip brands that are not properly configured or have missing essential data
                return empty($brand->name) || ! $brand->is_visible || empty($brand->slug);
            })->map(function (Brand $brand) use ($query, $locale) {
                $title = method_exists($brand, 'getTranslatedName') ? $brand->getTranslatedName($locale) : ($brand->name ?? '');
                $description = method_exists($brand, 'getTranslatedDescription') ? $brand->getTranslatedDescription($locale) : ($brand->description ?? '');
                $url = Route::has('localized.brand.show') ? route('localized.brand.show', ['locale' => $locale, 'brand' => $brand->slug]) : url('/brand/'.$brand->slug);

                return [
                    'id' => $brand->id,
                    'type' => 'brand',
                    'title' => $title,
                    'subtitle' => null,
                    'description' => Str::limit($description, 100),
                    'url' => $url,
                    'image' => $brand->getFirstMediaUrl('images', 'thumb'),
                    'products_count' => $brand->products()->count(),
                    'relevance_score' => $this->calculateRelevanceScore($title, $query),
                ];
            }));
        });
    }

    /**
     * Handle searchCollections functionality with proper error handling.
     */
    public function searchCollections(string $query, int $limit = self::DEFAULT_LIMIT): array
    {
        $cacheKey = "autocomplete_collections_{$query}_{$limit}_".app()->getLocale();

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($query, $limit) {
            $locale = app()->getLocale();
            $searchTerm = $this->prepareSearchTerm($query);
            $collections = Collection::query()->with(['media'])->where('is_visible', true)->where(function ($q) use ($searchTerm, $locale) {
                $q->where('name', 'like', $searchTerm)->orWhere('description', 'like', $searchTerm)->orWhereExists(function ($sq) use ($searchTerm, $locale) {
                    $sq->selectRaw('1')->from('collection_translations as t')->whereColumn('t.collection_id', 'collections.id')->where('t.locale', $locale)->where(function ($tw) use ($searchTerm) {
                        $tw->where('t.name', 'like', $searchTerm)->orWhere('t.description', 'like', $searchTerm);
                    });
                });
            })->orderByRaw("\n                    CASE \n                        WHEN name LIKE ? THEN 1\n                        WHEN description LIKE ? THEN 2\n                        ELSE 3\n                    END\n                ", ["%{$query}%", "%{$query}%"])->limit($limit)->cursor()->takeUntilTimeout(now()->addSeconds(5))->collect();

            return Arr::from($collections->skipWhile(function (Collection $collection) {
                // Skip collections that are not properly configured or have missing essential data
                return empty($collection->name) || ! $collection->is_visible || empty($collection->slug);
            })->map(function (Collection $collection) use ($query, $locale) {
                return ['id' => $collection->id, 'type' => 'collection', 'title' => $collection->getTranslatedName($locale), 'subtitle' => $collection->is_automatic ? __('frontend.collection.automatic') : __('frontend.collection.manual'), 'description' => Str::limit($collection->getTranslatedDescription($locale), 100), 'url' => route('localized.collection.show', ['locale' => $locale, 'collection' => $collection->slug]), 'image' => $collection->getFirstMediaUrl('images', 'thumb'), 'products_count' => $collection->products()->count(), 'relevance_score' => $this->calculateRelevanceScore($collection->getTranslatedName($locale), $query)];
            }));
        });
    }

    /**
     * Handle searchAttributes functionality with proper error handling.
     */
    public function searchAttributes(string $query, int $limit = self::DEFAULT_LIMIT): array
    {
        $cacheKey = "autocomplete_attributes_{$query}_{$limit}_".app()->getLocale();

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($query, $limit) {
            $locale = app()->getLocale();
            $searchTerm = $this->prepareSearchTerm($query);
            // Search attributes
            $attributes = Attribute::query()->with(['values'])->where('is_visible', true)->where(function ($q) use ($searchTerm, $locale) {
                $q->where('name', 'like', $searchTerm)->orWhere('description', 'like', $searchTerm)->orWhereExists(function ($sq) use ($searchTerm, $locale) {
                    $sq->selectRaw('1')->from('attribute_translations as t')->whereColumn('t.attribute_id', 'attributes.id')->where('t.locale', $locale)->where(function ($tw) use ($searchTerm) {
                        $tw->where('t.name', 'like', $searchTerm);
                    });
                });
            })->limit($limit)->get();
            // Search attribute values
            $attributeValues = AttributeValue::query()->with(['attribute'])->where('is_visible', true)->where('value', 'like', $searchTerm)->limit($limit)->get();
            $results = [];
            // Add attributes
            foreach ($attributes as $attribute) {
                $results[] = ['id' => $attribute->id, 'type' => 'attribute', 'title' => $attribute->getTranslatedName($locale), 'subtitle' => $attribute->group_name, 'description' => Str::limit($attribute->getTranslatedDescription($locale), 100), 'url' => route('localized.attribute.show', ['locale' => $locale, 'attribute' => $attribute->slug]), 'image' => null, 'values_count' => $attribute->values()->count(), 'relevance_score' => $this->calculateRelevanceScore($attribute->getTranslatedName($locale), $query)];
            }
            // Add attribute values
            foreach ($attributeValues as $value) {
                $results[] = ['id' => $value->id, 'type' => 'attribute_value', 'title' => $value->getDisplayValue(), 'subtitle' => $value->attribute->getTranslatedName($locale), 'description' => Str::limit($value->getDisplayDescription(), 100), 'url' => route('localized.attribute.value.show', ['locale' => $locale, 'attribute' => $value->attribute->slug, 'value' => $value->id]), 'image' => null, 'color_code' => $value->color_code, 'relevance_score' => $this->calculateRelevanceScore($value->getDisplayValue(), $query)];
            }

            return $results;
        });
    }

    /**
     * Handle getPopularSuggestions functionality with proper error handling.
     */
    public function getPopularSuggestions(int $limit = 10): array
    {
        $cacheKey = "autocomplete_popular_{$limit}_".app()->getLocale();

        return Cache::remember($cacheKey, 3600, function () use ($limit) {
            // Cache for 1 hour
            $locale = app()->getLocale();
            // Get popular products
            $popularProducts = Product::query()->with(['media', 'brand'])->where('is_visible', true)->whereNotNull('published_at')->where('published_at', '<=', now())->orderBy('published_at', 'desc')->limit($limit)->get();

            return Arr::from($popularProducts->skipWhile(function (Product $product) {
                // Skip popular products that are not properly configured or have missing essential data
                return empty($product->name) || ! $product->is_visible || empty($product->slug) || $product->price <= 0;
            })->map(function (Product $product) use ($locale) {
                return ['id' => $product->id, 'type' => 'product', 'title' => $product->getTranslatedName($locale), 'subtitle' => $product->brand?->name, 'url' => route('localized.products.show', ['locale' => $locale, 'product' => $product->slug]), 'image' => $product->getFirstMediaUrl('images', 'thumb'), 'is_popular' => true];
            }));
        });
    }

    /**
     * Handle getRecentSuggestions functionality with proper error handling.
     */
    public function getRecentSuggestions(int $limit = 5): array
    {
        $recentSearches = session('recent_searches', []);
        if (empty($recentSearches)) {
            return [];
        }
        // Get recent searches and perform quick lookup
        $results = [];
        $locale = app()->getLocale();
        foreach (array_slice($recentSearches, 0, $limit) as $searchTerm) {
            $quickResults = $this->search($searchTerm, 1);
            if (! empty($quickResults)) {
                $results[] = array_merge($quickResults[0], ['is_recent' => true, 'search_term' => $searchTerm]);
            }
        }

        return $results;
    }

    /**
     * Handle addToRecentSearches functionality with proper error handling.
     */
    public function addToRecentSearches(string $query): void
    {
        if (strlen($query) < 2) {
            return;
        }
        $recentSearches = session('recent_searches', []);
        // Remove if already exists
        $recentSearches = array_filter($recentSearches, fn ($term) => $term !== $query);
        // Add to beginning
        array_unshift($recentSearches, $query);
        // Keep only last 10 searches
        $recentSearches = array_slice($recentSearches, 0, 10);
        session(['recent_searches' => $recentSearches]);
    }

    /**
     * Handle clearRecentSearches functionality with proper error handling.
     */
    public function clearRecentSearches(): void
    {
        session()->forget('recent_searches');
    }

    /**
     * Handle searchByType functionality with proper error handling.
     */
    private function searchByType(string $type, string $query, int $limit, string $locale): array
    {
        return match ($type) {
            'products' => $this->searchProducts($query, $limit),
            'categories' => $this->searchCategories($query, $limit),
            'brands' => $this->searchBrands($query, $limit),
            'collections' => $this->searchCollections($query, $limit),
            'attributes' => $this->searchAttributes($query, $limit),
            default => [],
        };
    }

    /**
     * Handle calculateTypeLimits functionality with proper error handling.
     */
    private function calculateTypeLimits(array $types, int $totalLimit): array
    {
        $typeCount = count($types);
        $baseLimit = (int) floor($totalLimit / $typeCount);
        $remainder = $totalLimit % $typeCount;
        $limits = [];
        foreach ($types as $index => $type) {
            $limits[$type] = $baseLimit + ($index < $remainder ? 1 : 0);
        }

        return $limits;
    }

    /**
     * Handle sortByRelevance functionality with proper error handling.
     */
    private function sortByRelevance(array $results, string $query, int $limit): array
    {
        // Sort by relevance score (if available) or by type priority
        usort($results, function ($a, $b) use ($query) {
            $scoreA = $a['relevance_score'] ?? $this->calculateRelevanceScore($a['title'], $query);
            $scoreB = $b['relevance_score'] ?? $this->calculateRelevanceScore($b['title'], $query);
            if ($scoreA === $scoreB) {
                // Type priority: products > categories > brands > collections > attributes
                $typePriority = ['product' => 1, 'category' => 2, 'brand' => 3, 'collection' => 4, 'attribute' => 5, 'attribute_value' => 6];

                return ($typePriority[$a['type']] ?? 7) <=> ($typePriority[$b['type']] ?? 7);
            }

            return $scoreB <=> $scoreA;
        });

        return array_slice($results, 0, $limit);
    }

    /**
     * Handle calculateRelevanceScore functionality with proper error handling.
     */
    private function calculateRelevanceScore(string $text, string $query): int
    {
        $text = strtolower($text);
        $query = strtolower($query);
        // Exact match gets highest score
        if ($text === $query) {
            return 100;
        }
        // Starts with query gets high score
        if (str_starts_with($text, $query)) {
            return 90;
        }
        // Contains query gets medium score
        if (str_contains($text, $query)) {
            return 70;
        }
        // Word boundary match gets lower score
        if (preg_match('/\b'.preg_quote($query, '/').'\b/', $text)) {
            return 60;
        }
        // Fuzzy match gets lowest score
        $similarity = similar_text($text, $query, $percent);

        return (int) $percent;
    }

    /**
     * Handle prepareSearchTerm functionality with proper error handling.
     */
    private function prepareSearchTerm(string $query): string
    {
        return '%'.str_replace(['%', '_'], ['\%', '\_'], $query).'%';
    }

    /**
     * Handle generateCacheKey functionality with proper error handling.
     */
    private function generateCacheKey(string $query, int $limit, array $types): string
    {
        $typesKey = empty($types) ? 'all' : implode('_', $types);

        return "autocomplete_{$typesKey}_{$query}_{$limit}_".app()->getLocale();
    }

    /**
     * Track search analytics
     */
    private function trackSearchAnalytics(string $query, int $resultCount): void
    {
        try {
            $analyticsService = app(\App\Services\SearchAnalyticsService::class);
            $analyticsService->trackSearch($query, $resultCount, auth()->id());
        } catch (\Exception $e) {
            // Silently fail analytics tracking to not break search functionality
            \Log::warning('Search analytics tracking failed: '.$e->getMessage());
        }
    }

    /**
     * Track search performance metrics
     */
    private function trackSearchPerformance(string $query, float $executionTime, int $resultCount, string $searchTypes): void
    {
        try {
            $performanceService = app(\App\Services\SearchPerformanceService::class);
            $performanceService->trackSearchPerformance($query, $executionTime, $resultCount, $searchTypes);
        } catch (\Exception $e) {
            // Silently fail performance tracking to not break search functionality
            \Log::warning('Search performance tracking failed: '.$e->getMessage());
        }
    }

    /**
     * Apply search highlighting to results
     */
    private function applySearchHighlighting(array $results, string $query): array
    {
        try {
            $highlightingService = app(\App\Services\SearchHighlightingService::class);

            return $highlightingService->highlightResults($results, $query, ['title', 'subtitle', 'description']);
        } catch (\Exception $e) {
            // Silently fail highlighting to not break search functionality
            \Log::warning('Search highlighting failed: '.$e->getMessage());

            return $results;
        }
    }

    /**
     * Apply search ranking to results
     */
    private function applySearchRanking(array $results, string $query): array
    {
        try {
            $rankingService = app(\App\Services\SearchRankingService::class);
            $context = [
                'user_id' => auth()->id(),
                'search_history' => $this->getUserSearchHistory(),
                'location' => $this->getUserLocation(),
            ];

            $rankedResults = $rankingService->rankResults($results, $query, $context);

            return $rankingService->applyBusinessRules($rankedResults);
        } catch (\Exception $e) {
            // Silently fail ranking to not break search functionality
            \Log::warning('Search ranking failed: '.$e->getMessage());

            return $results;
        }
    }

    /**
     * Get user search history for context
     */
    private function getUserSearchHistory(): array
    {
        try {
            $userId = auth()->id();
            if (! $userId) {
                return [];
            }

            $cacheKey = "user_search_history_{$userId}";

            return Cache::get($cacheKey, []);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get user location for context
     */
    private function getUserLocation(): array
    {
        try {
            // This would typically get user's location from their profile or IP
            return [
                'country' => 'LT',
                'city' => 'Vilnius',
            ];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Generate typo variations for fuzzy search
     */
    private function generateTypoVariations(string $query): array
    {
        $variations = [];
        $words = explode(' ', $query);

        foreach ($words as $word) {
            if (strlen($word) > 3) {
                // Common typos: double letters, missing letters, swapped letters
                $variations[] = $this->addDoubleLetter($word);
                $variations[] = $this->removeDoubleLetter($word);
                $variations[] = $this->swapAdjacentLetters($word);
            }
        }

        return array_filter(array_unique($variations));
    }

    /**
     * Add double letter (e.g., "laptop" -> "lapptop")
     */
    private function addDoubleLetter(string $word): string
    {
        if (strlen($word) < 4) {
            return $word;
        }

        $pos = rand(1, strlen($word) - 2);

        return substr($word, 0, $pos).$word[$pos].substr($word, $pos);
    }

    /**
     * Remove double letter (e.g., "lapptop" -> "laptop")
     */
    private function removeDoubleLetter(string $word): string
    {
        for ($i = 0; $i < strlen($word) - 1; $i++) {
            if ($word[$i] === $word[$i + 1]) {
                return substr($word, 0, $i).substr($word, $i + 1);
            }
        }

        return $word;
    }

    /**
     * Swap adjacent letters (e.g., "laptop" -> "laptpo")
     */
    private function swapAdjacentLetters(string $word): string
    {
        if (strlen($word) < 3) {
            return $word;
        }

        $pos = rand(0, strlen($word) - 2);
        $chars = str_split($word);
        [$chars[$pos], $chars[$pos + 1]] = [$chars[$pos + 1], $chars[$pos]];

        return implode('', $chars);
    }

    /**
     * Enhanced search with fuzzy matching and typo tolerance
     */
    public function searchWithFuzzy(string $query, int $limit = self::DEFAULT_LIMIT, array $types = []): array
    {
        if (strlen($query) < 2) {
            return [];
        }

        // First try exact search
        $results = $this->search($query, $limit, $types);

        // If we don't have enough results, try fuzzy search
        if (count($results) < $limit * 0.5) {
            $typoVariations = $this->generateTypoVariations($query);

            foreach ($typoVariations as $variation) {
                $fuzzyResults = $this->search($variation, $limit, $types);
                $results = array_merge($results, $fuzzyResults);

                if (count($results) >= $limit) {
                    break;
                }
            }
        }

        // Remove duplicates and sort by relevance
        $results = $this->removeDuplicateResults($results);
        $results = $this->sortByRelevance($results, $query, $limit);

        return $results;
    }

    /**
     * Remove duplicate results based on ID and type
     */
    private function removeDuplicateResults(array $results): array
    {
        $seen = [];
        $unique = [];

        foreach ($results as $result) {
            $key = $result['id'].'_'.$result['type'];
            if (! isset($seen[$key])) {
                $seen[$key] = true;
                $unique[] = $result;
            }
        }

        return $unique;
    }

    /**
     * Get personalized search suggestions based on user behavior
     */
    public function getPersonalizedSuggestions(int $userId, int $limit = 5): array
    {
        $cacheKey = "personalized_suggestions_{$userId}_{$limit}";

        return Cache::remember($cacheKey, 1800, function () use ($userId, $limit) {
            // Get user's recent searches
            $recentSearches = $this->getRecentSearches($userId, 10);

            // Get popular searches in user's categories
            $userCategories = $this->getUserPreferredCategories($userId);
            $popularInCategories = $this->getPopularSearchesInCategories($userCategories, 5);

            // Combine and rank suggestions
            $suggestions = array_merge($recentSearches, $popularInCategories);

            return array_slice($suggestions, 0, $limit);
        });
    }

    /**
     * Get user's recent searches
     */
    private function getRecentSearches(int $userId, int $limit): array
    {
        // This would typically come from a search_history table
        // For now, return cached recent searches
        return Cache::get("recent_searches_{$userId}", []);
    }

    /**
     * Get user's preferred categories based on search history
     */
    private function getUserPreferredCategories(int $userId): array
    {
        // This would analyze user's search patterns
        // For now, return default categories
        return ['products', 'categories', 'brands'];
    }

    /**
     * Get popular searches in specific categories
     */
    private function getPopularSearchesInCategories(array $categories, int $limit): array
    {
        $suggestions = [];

        foreach ($categories as $category) {
            $popular = $this->getPopularSuggestions(3);
            $suggestions = array_merge($suggestions, $popular);
        }

        return array_slice($suggestions, 0, $limit);
    }

    /**
     * Search customers for admin autocomplete
     */
    public function searchCustomers(string $query, int $limit = self::DEFAULT_LIMIT): array
    {
        $cacheKey = "autocomplete_customers_{$query}_{$limit}_".app()->getLocale();

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($query, $limit) {
            $locale = app()->getLocale();
            $searchTerm = $this->prepareSearchTerm($query);

            $customers = User::query()
                ->where('is_active', true)
                ->where(function ($q) use ($searchTerm) {
                    $q
                        ->where('name', 'like', $searchTerm)
                        ->orWhere('email', 'like', $searchTerm)
                        ->orWhere('phone', 'like', $searchTerm);
                })
                ->orderByRaw('
                    CASE 
                        WHEN name LIKE ? THEN 1
                        WHEN email LIKE ? THEN 2
                        WHEN phone LIKE ? THEN 3
                        ELSE 4
                    END
                ', ["%{$query}%", "%{$query}%", "%{$query}%"])
                ->limit($limit)
                ->cursor()
                ->takeUntilTimeout(now()->addSeconds(5))
                ->collect();

            return Arr::from($customers->map(function (User $customer) use ($query) {
                return [
                    'id' => $customer->id,
                    'type' => 'customer',
                    'title' => $customer->name,
                    'subtitle' => $customer->email,
                    'description' => $customer->phone ? "Phone: {$customer->phone}" : null,
                    'url' => route('filament.admin.resources.customers.edit', $customer),
                    'image' => $customer->getFirstMediaUrl('avatar'),
                    'orders_count' => $customer->orders()->count(),
                    'total_spent' => $customer->orders()->sum('total'),
                    'last_order_at' => $customer->orders()->latest()->first()?->created_at,
                    'relevance_score' => $this->calculateRelevanceScore($customer->name, $query),
                ];
            }));
        });
    }

    /**
     * Search addresses for admin autocomplete
     */
    public function searchAddresses(string $query, int $limit = self::DEFAULT_LIMIT): array
    {
        $cacheKey = "autocomplete_addresses_{$query}_{$limit}_".app()->getLocale();

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($query, $limit) {
            $locale = app()->getLocale();
            $searchTerm = $this->prepareSearchTerm($query);

            $addresses = Address::query()
                ->with(['user', 'city', 'country'])
                ->where(function ($q) use ($searchTerm) {
                    $q
                        ->where('street', 'like', $searchTerm)
                        ->orWhere('city_name', 'like', $searchTerm)
                        ->orWhere('postal_code', 'like', $searchTerm)
                        ->orWhereHas('user', function ($userQuery) use ($searchTerm) {
                            $userQuery
                                ->where('name', 'like', $searchTerm)
                                ->orWhere('email', 'like', $searchTerm);
                        });
                })
                ->orderByRaw('
                    CASE 
                        WHEN street LIKE ? THEN 1
                        WHEN city_name LIKE ? THEN 2
                        WHEN postal_code LIKE ? THEN 3
                        ELSE 4
                    END
                ', ["%{$query}%", "%{$query}%", "%{$query}%"])
                ->limit($limit)
                ->cursor()
                ->takeUntilTimeout(now()->addSeconds(5))
                ->collect();

            return Arr::from($addresses->map(function (Address $address) use ($query) {
                return [
                    'id' => $address->id,
                    'type' => 'address',
                    'title' => $address->full_address,
                    'subtitle' => $address->user?->name,
                    'description' => 'Type: '.ucfirst($address->type),
                    'url' => route('filament.admin.resources.addresses.edit', $address),
                    'image' => null,
                    'user_id' => $address->user_id,
                    'address_type' => $address->type,
                    'is_default' => $address->is_default,
                    'relevance_score' => $this->calculateRelevanceScore($address->full_address, $query),
                ];
            }));
        });
    }

    /**
     * Search locations for admin autocomplete
     */
    public function searchLocations(string $query, int $limit = self::DEFAULT_LIMIT): array
    {
        $cacheKey = "autocomplete_locations_{$query}_{$limit}_".app()->getLocale();

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($query, $limit) {
            $locale = app()->getLocale();
            $searchTerm = $this->prepareSearchTerm($query);

            $locations = Location::query()
                ->where('is_enabled', true)
                ->where(function ($q) use ($searchTerm) {
                    $q
                        ->where('name', 'like', $searchTerm)
                        ->orWhere('code', 'like', $searchTerm)
                        ->orWhere('city', 'like', $searchTerm)
                        ->orWhere('address_line_1', 'like', $searchTerm);
                })
                ->orderByRaw('
                    CASE 
                        WHEN name LIKE ? THEN 1
                        WHEN code LIKE ? THEN 2
                        WHEN city LIKE ? THEN 3
                        ELSE 4
                    END
                ', ["%{$query}%", "%{$query}%", "%{$query}%"])
                ->limit($limit)
                ->cursor()
                ->takeUntilTimeout(now()->addSeconds(5))
                ->collect();

            return Arr::from($locations->map(function (Location $location) use ($query, $locale) {
                return [
                    'id' => $location->id,
                    'type' => 'location',
                    'title' => $location->getTranslatedName($locale),
                    'subtitle' => $location->city.', '.$location->country_code,
                    'description' => $location->address_line_1,
                    'url' => route('filament.admin.resources.locations.edit', $location),
                    'image' => null,
                    'code' => $location->code,
                    'type_label' => ucfirst($location->type),
                    'is_default' => $location->is_default,
                    'relevance_score' => $this->calculateRelevanceScore($location->getTranslatedName($locale), $query),
                ];
            }));
        });
    }

    /**
     * Search countries for admin autocomplete
     */
    public function searchCountries(string $query, int $limit = self::DEFAULT_LIMIT): array
    {
        $cacheKey = "autocomplete_countries_{$query}_{$limit}_".app()->getLocale();

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($query, $limit) {
            $locale = app()->getLocale();
            $searchTerm = $this->prepareSearchTerm($query);

            $countries = Country::query()
                ->where(function ($q) use ($searchTerm) {
                    $q
                        ->where('name', 'like', $searchTerm)
                        ->orWhere('cca2', 'like', $searchTerm)
                        ->orWhere('cca3', 'like', $searchTerm)
                        ->orWhere('name_common', 'like', $searchTerm);
                })
                ->orderByRaw('
                    CASE 
                        WHEN name LIKE ? THEN 1
                        WHEN name_common LIKE ? THEN 2
                        WHEN cca2 LIKE ? THEN 3
                        ELSE 4
                    END
                ', ["%{$query}%", "%{$query}%", "%{$query}%"])
                ->limit($limit)
                ->cursor()
                ->takeUntilTimeout(now()->addSeconds(5))
                ->collect();

            return Arr::from($countries->map(function (Country $country) use ($query) {
                return [
                    'id' => $country->id,
                    'type' => 'country',
                    'title' => $country->name,
                    'subtitle' => $country->cca2.' - '.$country->cca3,
                    'description' => $country->region.', '.$country->subregion,
                    'url' => route('filament.admin.resources.countries.edit', $country),
                    'image' => null,
                    'cca2' => $country->cca2,
                    'cca3' => $country->cca3,
                    'region' => $country->region,
                    'relevance_score' => $this->calculateRelevanceScore($country->name, $query),
                ];
            }));
        });
    }

    /**
     * Search cities for admin autocomplete
     */
    public function searchCities(string $query, int $limit = self::DEFAULT_LIMIT): array
    {
        $cacheKey = "autocomplete_cities_{$query}_{$limit}_".app()->getLocale();

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($query, $limit) {
            $locale = app()->getLocale();
            $searchTerm = $this->prepareSearchTerm($query);

            $cities = City::query()
                ->with(['country', 'region'])
                ->where(function ($q) use ($searchTerm) {
                    $q
                        ->where('name', 'like', $searchTerm)
                        ->orWhere('code', 'like', $searchTerm)
                        ->orWhereHas('country', function ($countryQuery) use ($searchTerm) {
                            $countryQuery->where('name', 'like', $searchTerm);
                        });
                })
                ->orderByRaw('
                    CASE 
                        WHEN name LIKE ? THEN 1
                        WHEN code LIKE ? THEN 2
                        ELSE 3
                    END
                ', ["%{$query}%", "%{$query}%"])
                ->limit($limit)
                ->cursor()
                ->takeUntilTimeout(now()->addSeconds(5))
                ->collect();

            return Arr::from($cities->map(function (City $city) use ($query, $locale) {
                return [
                    'id' => $city->id,
                    'type' => 'city',
                    'title' => $city->getTranslatedName($locale),
                    'subtitle' => $city->country?->name,
                    'description' => $city->getTranslatedDescription($locale),
                    'url' => route('filament.admin.resources.cities.edit', $city),
                    'image' => null,
                    'code' => $city->code,
                    'country_id' => $city->country_id,
                    'relevance_score' => $this->calculateRelevanceScore($city->getTranslatedName($locale), $query),
                ];
            }));
        });
    }

    /**
     * Search orders for admin autocomplete
     */
    public function searchOrders(string $query, int $limit = self::DEFAULT_LIMIT): array
    {
        $cacheKey = "autocomplete_orders_{$query}_{$limit}_".app()->getLocale();

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($query, $limit) {
            $locale = app()->getLocale();
            $searchTerm = $this->prepareSearchTerm($query);

            $orders = Order::query()
                ->with(['user', 'items'])
                ->where(function ($q) use ($searchTerm) {
                    $q
                        ->where('order_number', 'like', $searchTerm)
                        ->orWhere('reference', 'like', $searchTerm)
                        ->orWhereHas('user', function ($userQuery) use ($searchTerm) {
                            $userQuery
                                ->where('name', 'like', $searchTerm)
                                ->orWhere('email', 'like', $searchTerm);
                        });
                })
                ->orderByRaw('
                    CASE 
                        WHEN order_number LIKE ? THEN 1
                        WHEN reference LIKE ? THEN 2
                        ELSE 3
                    END
                ', ["%{$query}%", "%{$query}%"])
                ->limit($limit)
                ->cursor()
                ->takeUntilTimeout(now()->addSeconds(5))
                ->collect();

            return Arr::from($orders->map(function (Order $order) use ($query) {
                return [
                    'id' => $order->id,
                    'type' => 'order',
                    'title' => $order->order_number,
                    'subtitle' => $order->user?->name.' - '.$order->formatted_total,
                    'description' => $order->status.' - '.$order->created_at->format('d/m/Y'),
                    'url' => route('filament.admin.resources.orders.edit', $order),
                    'image' => null,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'total' => $order->total,
                    'formatted_total' => $order->formatted_total,
                    'user_id' => $order->user_id,
                    'items_count' => $order->items()->count(),
                    'relevance_score' => $this->calculateRelevanceScore($order->order_number, $query),
                ];
            }));
        });
    }

    /**
     * Search by ID for specific result
     */
    public function searchById(int $id, string $type): ?array
    {
        $cacheKey = "autocomplete_by_id_{$type}_{$id}_".app()->getLocale();

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($id, $type) {
            $locale = app()->getLocale();

            return match ($type) {
                'products' => $this->getProductById($id, $locale),
                'categories' => $this->getCategoryById($id, $locale),
                'brands' => $this->getBrandById($id, $locale),
                'collections' => $this->getCollectionById($id, $locale),
                'attributes' => $this->getAttributeById($id, $locale),
                'customers' => $this->getCustomerById($id, $locale),
                'addresses' => $this->getAddressById($id, $locale),
                'locations' => $this->getLocationById($id, $locale),
                'countries' => $this->getCountryById($id, $locale),
                'cities' => $this->getCityById($id, $locale),
                'orders' => $this->getOrderById($id, $locale),
                default => null,
            };
        });
    }

    /**
     * Get product by ID
     */
    private function getProductById(int $id, string $locale): ?array
    {
        $product = Product::with(['media', 'brand', 'categories'])
            ->where('id', $id)
            ->where('is_visible', true)
            ->first();

        if (! $product) {
            return null;
        }

        return [
            'id' => $product->id,
            'type' => 'product',
            'title' => $product->getTranslatedName($locale),
            'subtitle' => $product->brand?->getTranslatedName($locale),
            'description' => Str::limit($product->getTranslatedDescription($locale), 100),
            'url' => route('localized.product.show', ['locale' => $locale, 'product' => $product->slug]),
            'image' => $product->getFirstMediaUrl('default'),
            'formatted_price' => $product->formatted_price,
            'in_stock' => $product->in_stock,
        ];
    }

    /**
     * Get category by ID
     */
    private function getCategoryById(int $id, string $locale): ?array
    {
        $category = Category::with(['parent', 'children'])
            ->where('id', $id)
            ->where('is_visible', true)
            ->first();

        if (! $category) {
            return null;
        }

        return [
            'id' => $category->id,
            'type' => 'category',
            'title' => $category->getTranslatedName($locale),
            'subtitle' => $category->parent?->getTranslatedName($locale),
            'description' => Str::limit($category->getTranslatedDescription($locale), 100),
            'url' => route('localized.category.show', ['locale' => $locale, 'category' => $category->slug]),
            'image' => $category->getFirstMediaUrl('default'),
            'products_count' => $category->products()->count(),
        ];
    }

    /**
     * Get brand by ID
     */
    private function getBrandById(int $id, string $locale): ?array
    {
        $brand = Brand::where('id', $id)
            ->where('is_visible', true)
            ->first();

        if (! $brand) {
            return null;
        }

        return [
            'id' => $brand->id,
            'type' => 'brand',
            'title' => $brand->getTranslatedName($locale),
            'subtitle' => $brand->country,
            'description' => Str::limit($brand->getTranslatedDescription($locale), 100),
            'url' => route('localized.brand.show', ['locale' => $locale, 'brand' => $brand->slug]),
            'image' => $brand->getFirstMediaUrl('default'),
            'products_count' => $brand->products()->count(),
        ];
    }

    /**
     * Get collection by ID
     */
    private function getCollectionById(int $id, string $locale): ?array
    {
        $collection = Collection::where('id', $id)
            ->where('is_visible', true)
            ->first();

        if (! $collection) {
            return null;
        }

        return [
            'id' => $collection->id,
            'type' => 'collection',
            'title' => $collection->getTranslatedName($locale),
            'subtitle' => $collection->type,
            'description' => Str::limit($collection->getTranslatedDescription($locale), 100),
            'url' => route('localized.collection.show', ['locale' => $locale, 'collection' => $collection->slug]),
            'image' => $collection->getFirstMediaUrl('default'),
            'products_count' => $collection->products()->count(),
        ];
    }

    /**
     * Get attribute by ID
     */
    private function getAttributeById(int $id, string $locale): ?array
    {
        $attribute = Attribute::with(['values'])
            ->where('id', $id)
            ->where('is_visible', true)
            ->first();

        if (! $attribute) {
            return null;
        }

        return [
            'id' => $attribute->id,
            'type' => 'attribute',
            'title' => $attribute->getTranslatedName($locale),
            'subtitle' => $attribute->group_name,
            'description' => Str::limit($attribute->getTranslatedDescription($locale), 100),
            'url' => route('localized.attribute.show', ['locale' => $locale, 'attribute' => $attribute->slug]),
            'image' => null,
            'values_count' => $attribute->values()->count(),
        ];
    }

    /**
     * Get customer by ID
     */
    private function getCustomerById(int $id, string $locale): ?array
    {
        $customer = User::where('id', $id)
            ->where('is_active', true)
            ->first();

        if (! $customer) {
            return null;
        }

        return [
            'id' => $customer->id,
            'type' => 'customer',
            'title' => $customer->name,
            'subtitle' => $customer->email,
            'description' => $customer->phone ? "Phone: {$customer->phone}" : null,
            'url' => route('filament.admin.resources.customers.edit', $customer),
            'image' => $customer->getFirstMediaUrl('avatar'),
            'orders_count' => $customer->orders()->count(),
            'total_spent' => $customer->orders()->sum('total'),
            'last_order_at' => $customer->orders()->latest()->first()?->created_at,
        ];
    }

    /**
     * Get address by ID
     */
    private function getAddressById(int $id, string $locale): ?array
    {
        $address = Address::with(['user', 'city', 'country'])
            ->where('id', $id)
            ->first();

        if (! $address) {
            return null;
        }

        return [
            'id' => $address->id,
            'type' => 'address',
            'title' => $address->full_address,
            'subtitle' => $address->user?->name,
            'description' => 'Type: '.ucfirst($address->type),
            'url' => route('filament.admin.resources.addresses.edit', $address),
            'image' => null,
            'user_id' => $address->user_id,
            'address_type' => $address->type,
            'is_default' => $address->is_default,
        ];
    }

    /**
     * Get location by ID
     */
    private function getLocationById(int $id, string $locale): ?array
    {
        $location = Location::where('id', $id)
            ->where('is_enabled', true)
            ->first();

        if (! $location) {
            return null;
        }

        return [
            'id' => $location->id,
            'type' => 'location',
            'title' => $location->getTranslatedName($locale),
            'subtitle' => $location->city.', '.$location->country_code,
            'description' => $location->address_line_1,
            'url' => route('filament.admin.resources.locations.edit', $location),
            'image' => null,
            'code' => $location->code,
            'type_label' => ucfirst($location->type),
            'is_default' => $location->is_default,
        ];
    }

    /**
     * Get country by ID
     */
    private function getCountryById(int $id, string $locale): ?array
    {
        $country = Country::where('id', $id)->first();

        if (! $country) {
            return null;
        }

        return [
            'id' => $country->id,
            'type' => 'country',
            'title' => $country->name,
            'subtitle' => $country->cca2.' - '.$country->cca3,
            'description' => $country->region.', '.$country->subregion,
            'url' => route('filament.admin.resources.countries.edit', $country),
            'image' => null,
            'cca2' => $country->cca2,
            'cca3' => $country->cca3,
            'region' => $country->region,
        ];
    }

    /**
     * Get city by ID
     */
    private function getCityById(int $id, string $locale): ?array
    {
        $city = City::with(['country', 'region'])
            ->where('id', $id)
            ->first();

        if (! $city) {
            return null;
        }

        return [
            'id' => $city->id,
            'type' => 'city',
            'title' => $city->getTranslatedName($locale),
            'subtitle' => $city->country?->name,
            'description' => $city->getTranslatedDescription($locale),
            'url' => route('filament.admin.resources.cities.edit', $city),
            'image' => null,
            'code' => $city->code,
            'country_id' => $city->country_id,
        ];
    }

    /**
     * Get order by ID
     */
    private function getOrderById(int $id, string $locale): ?array
    {
        $order = Order::with(['user', 'items'])
            ->where('id', $id)
            ->first();

        if (! $order) {
            return null;
        }

        return [
            'id' => $order->id,
            'type' => 'order',
            'title' => $order->order_number,
            'subtitle' => $order->user?->name.' - '.$order->formatted_total,
            'description' => $order->status.' - '.$order->created_at->format('d/m/Y'),
            'url' => route('filament.admin.resources.orders.edit', $order),
            'image' => null,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'total' => $order->total,
            'formatted_total' => $order->formatted_total,
            'user_id' => $order->user_id,
            'items_count' => $order->items()->count(),
        ];
    }
}
