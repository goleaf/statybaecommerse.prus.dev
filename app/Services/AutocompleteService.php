<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

final class AutocompleteService
{
    private const CACHE_TTL = 300; // 5 minutes
    private const DEFAULT_LIMIT = 10;
    private const MAX_LIMIT = 50;

    /**
     * Perform comprehensive autocomplete search across all searchable entities
     */
    public function search(string $query, int $limit = self::DEFAULT_LIMIT, array $types = []): array
    {
        if (strlen($query) < 2) {
            return [];
        }

        $cacheKey = $this->generateCacheKey($query, $limit, $types);
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($query, $limit, $types) {
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
    }

    /**
     * Search products with advanced matching
     */
    public function searchProducts(string $query, int $limit = self::DEFAULT_LIMIT): array
    {
        $cacheKey = "autocomplete_products_{$query}_{$limit}_" . app()->getLocale();
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($query, $limit) {
            $locale = app()->getLocale();
            $searchTerm = $this->prepareSearchTerm($query);
            
            $products = Product::query()
                ->with(['media', 'brand', 'categories', 'variants'])
                ->where('is_visible', true)
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->where(function ($q) use ($searchTerm, $locale) {
                    // Exact match (highest priority)
                    $q->where('name', 'like', $searchTerm)
                        // SKU exact match
                        ->orWhere('sku', 'like', $searchTerm)
                        // Description match
                        ->orWhere('description', 'like', $searchTerm)
                        // Brand name match
                        ->orWhereHas('brand', function ($brandQuery) use ($searchTerm) {
                            $brandQuery->where('name', 'like', $searchTerm);
                        })
                        // Category name match
                        ->orWhereHas('categories', function ($catQuery) use ($searchTerm) {
                            $catQuery->where('name', 'like', $searchTerm);
                        })
                        // Translation matches
                        ->orWhereExists(function ($sq) use ($searchTerm, $locale) {
                            $sq->selectRaw('1')
                                ->from('product_translations as t')
                                ->whereColumn('t.product_id', 'products.id')
                                ->where('t.locale', $locale)
                                ->where(function ($tw) use ($searchTerm) {
                                    $tw->where('t.name', 'like', $searchTerm)
                                        ->orWhere('t.description', 'like', $searchTerm);
                                });
                        });
                })
                ->orderByRaw("
                    CASE 
                        WHEN name LIKE ? THEN 1
                        WHEN sku LIKE ? THEN 2
                        WHEN description LIKE ? THEN 3
                        ELSE 4
                    END
                ", ["%{$query}%", "%{$query}%", "%{$query}%"])
                ->limit($limit)
                ->get();
            
            return $products
                ->skipWhile(function (Product $product) {
                    // Skip products that are not properly configured or have missing essential data
                    return empty($product->name) || 
                           !$product->is_visible ||
                           $product->price <= 0 ||
                           empty($product->slug);
                })
                ->map(function (Product $product) use ($query, $locale) {
                    return [
                        'id' => $product->id,
                        'type' => 'product',
                        'title' => $product->getTranslatedName($locale),
                        'subtitle' => $product->brand?->name,
                        'description' => Str::limit($product->getTranslatedDescription($locale), 100),
                        'url' => route('localized.products.show', ['locale' => $locale, 'product' => $product->slug]),
                        'image' => $product->getFirstMediaUrl('images', 'thumb'),
                        'price' => $product->getPrice(),
                        'formatted_price' => $product->getFormattedPrice(),
                        'sku' => $product->sku,
                        'in_stock' => $product->isInStock(),
                        'relevance_score' => $this->calculateRelevanceScore($product->getTranslatedName($locale), $query),
                    ];
                })->toArray();
        });
    }

    /**
     * Search categories with hierarchical information
     */
    public function searchCategories(string $query, int $limit = self::DEFAULT_LIMIT): array
    {
        $cacheKey = "autocomplete_categories_{$query}_{$limit}_" . app()->getLocale();
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($query, $limit) {
            $locale = app()->getLocale();
            $searchTerm = $this->prepareSearchTerm($query);
            
            $categories = Category::query()
                ->with(['parent', 'children', 'media'])
                ->where('is_visible', true)
                ->where(function ($q) use ($searchTerm, $locale) {
                    $q->where('name', 'like', $searchTerm)
                        ->orWhere('description', 'like', $searchTerm)
                        ->orWhereExists(function ($sq) use ($searchTerm, $locale) {
                            $sq->selectRaw('1')
                                ->from('category_translations as t')
                                ->whereColumn('t.category_id', 'categories.id')
                                ->where('t.locale', $locale)
                                ->where(function ($tw) use ($searchTerm) {
                                    $tw->where('t.name', 'like', $searchTerm)
                                        ->orWhere('t.description', 'like', $searchTerm);
                                });
                        });
                })
                ->orderByRaw("
                    CASE 
                        WHEN name LIKE ? THEN 1
                        WHEN description LIKE ? THEN 2
                        ELSE 3
                    END
                ", ["%{$query}%", "%{$query}%"])
                ->limit($limit)
                ->get();
            
            return $categories
                ->skipWhile(function (Category $category) {
                    // Skip categories that are not properly configured or have missing essential data
                    return empty($category->name) || 
                           !$category->is_visible ||
                           empty($category->slug);
                })
                ->map(function (Category $category) use ($query, $locale) {
                    return [
                        'id' => $category->id,
                        'type' => 'category',
                        'title' => $category->getTranslatedName($locale),
                        'subtitle' => $category->parent ? $category->parent->getTranslatedName($locale) : null,
                        'description' => Str::limit($category->getTranslatedDescription($locale), 100),
                        'url' => route('localized.category.show', ['locale' => $locale, 'category' => $category->slug]),
                        'image' => $category->getFirstMediaUrl('images', 'thumb'),
                        'products_count' => $category->products()->count(),
                        'children_count' => $category->children()->count(),
                        'relevance_score' => $this->calculateRelevanceScore($category->getTranslatedName($locale), $query),
                    ];
                })->toArray();
        });
    }

    /**
     * Search brands with product count information
     */
    public function searchBrands(string $query, int $limit = self::DEFAULT_LIMIT): array
    {
        $cacheKey = "autocomplete_brands_{$query}_{$limit}_" . app()->getLocale();
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($query, $limit) {
            $locale = app()->getLocale();
            $searchTerm = $this->prepareSearchTerm($query);
            
            $brands = Brand::query()
                ->with(['media'])
                ->where('is_visible', true)
                ->where(function ($q) use ($searchTerm, $locale) {
                    $q->where('name', 'like', $searchTerm)
                        ->orWhere('description', 'like', $searchTerm)
                        ->orWhereExists(function ($sq) use ($searchTerm, $locale) {
                            $sq->selectRaw('1')
                                ->from('brand_translations as t')
                                ->whereColumn('t.brand_id', 'brands.id')
                                ->where('t.locale', $locale)
                                ->where(function ($tw) use ($searchTerm) {
                                    $tw->where('t.name', 'like', $searchTerm)
                                        ->orWhere('t.description', 'like', $searchTerm);
                                });
                        });
                })
                ->orderByRaw("
                    CASE 
                        WHEN name LIKE ? THEN 1
                        WHEN description LIKE ? THEN 2
                        ELSE 3
                    END
                ", ["%{$query}%", "%{$query}%"])
                ->limit($limit)
                ->get();
            
            return $brands
                ->skipWhile(function (Brand $brand) {
                    // Skip brands that are not properly configured or have missing essential data
                    return empty($brand->name) || 
                           !$brand->is_visible ||
                           empty($brand->slug);
                })
                ->map(function (Brand $brand) use ($query, $locale) {
                    return [
                        'id' => $brand->id,
                        'type' => 'brand',
                        'title' => $brand->getTranslatedName($locale),
                        'subtitle' => null,
                        'description' => Str::limit($brand->getTranslatedDescription($locale), 100),
                        'url' => route('localized.brand.show', ['locale' => $locale, 'brand' => $brand->slug]),
                        'image' => $brand->getFirstMediaUrl('images', 'thumb'),
                        'products_count' => $brand->products()->count(),
                        'relevance_score' => $this->calculateRelevanceScore($brand->getTranslatedName($locale), $query),
                    ];
                })->toArray();
        });
    }

    /**
     * Search collections with product count
     */
    public function searchCollections(string $query, int $limit = self::DEFAULT_LIMIT): array
    {
        $cacheKey = "autocomplete_collections_{$query}_{$limit}_" . app()->getLocale();
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($query, $limit) {
            $locale = app()->getLocale();
            $searchTerm = $this->prepareSearchTerm($query);
            
            $collections = Collection::query()
                ->with(['media'])
                ->where('is_visible', true)
                ->where(function ($q) use ($searchTerm, $locale) {
                    $q->where('name', 'like', $searchTerm)
                        ->orWhere('description', 'like', $searchTerm)
                        ->orWhereExists(function ($sq) use ($searchTerm, $locale) {
                            $sq->selectRaw('1')
                                ->from('collection_translations as t')
                                ->whereColumn('t.collection_id', 'collections.id')
                                ->where('t.locale', $locale)
                                ->where(function ($tw) use ($searchTerm) {
                                    $tw->where('t.name', 'like', $searchTerm)
                                        ->orWhere('t.description', 'like', $searchTerm);
                                });
                        });
                })
                ->orderByRaw("
                    CASE 
                        WHEN name LIKE ? THEN 1
                        WHEN description LIKE ? THEN 2
                        ELSE 3
                    END
                ", ["%{$query}%", "%{$query}%"])
                ->limit($limit)
                ->get();
            
            return $collections
                ->skipWhile(function (Collection $collection) {
                    // Skip collections that are not properly configured or have missing essential data
                    return empty($collection->name) || 
                           !$collection->is_visible ||
                           empty($collection->slug);
                })
                ->map(function (Collection $collection) use ($query, $locale) {
                    return [
                        'id' => $collection->id,
                        'type' => 'collection',
                        'title' => $collection->getTranslatedName($locale),
                        'subtitle' => $collection->is_automatic ? __('frontend.collection.automatic') : __('frontend.collection.manual'),
                        'description' => Str::limit($collection->getTranslatedDescription($locale), 100),
                        'url' => route('localized.collection.show', ['locale' => $locale, 'collection' => $collection->slug]),
                        'image' => $collection->getFirstMediaUrl('images', 'thumb'),
                        'products_count' => $collection->products()->count(),
                        'relevance_score' => $this->calculateRelevanceScore($collection->getTranslatedName($locale), $query),
                    ];
                })->toArray();
        });
    }

    /**
     * Search attributes and attribute values
     */
    public function searchAttributes(string $query, int $limit = self::DEFAULT_LIMIT): array
    {
        $cacheKey = "autocomplete_attributes_{$query}_{$limit}_" . app()->getLocale();
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($query, $limit) {
            $locale = app()->getLocale();
            $searchTerm = $this->prepareSearchTerm($query);
            
            // Search attributes
            $attributes = Attribute::query()
                ->with(['values'])
                ->where('is_visible', true)
                ->where(function ($q) use ($searchTerm, $locale) {
                    $q->where('name', 'like', $searchTerm)
                        ->orWhere('description', 'like', $searchTerm)
                        ->orWhereExists(function ($sq) use ($searchTerm, $locale) {
                            $sq->selectRaw('1')
                                ->from('attribute_translations as t')
                                ->whereColumn('t.attribute_id', 'attributes.id')
                                ->where('t.locale', $locale)
                                ->where(function ($tw) use ($searchTerm) {
                                    $tw->where('t.name', 'like', $searchTerm)
                                        ->orWhere('t.description', 'like', $searchTerm);
                                });
                        });
                })
                ->limit($limit)
                ->get();
            
            // Search attribute values
            $attributeValues = AttributeValue::query()
                ->with(['attribute'])
                ->where('is_visible', true)
                ->where('value', 'like', $searchTerm)
                ->limit($limit)
                ->get();
            
            $results = [];
            
            // Add attributes
            foreach ($attributes as $attribute) {
                $results[] = [
                    'id' => $attribute->id,
                    'type' => 'attribute',
                    'title' => $attribute->getTranslatedName($locale),
                    'subtitle' => $attribute->group_name,
                    'description' => Str::limit($attribute->getTranslatedDescription($locale), 100),
                    'url' => route('localized.attribute.show', ['locale' => $locale, 'attribute' => $attribute->slug]),
                    'image' => null,
                    'values_count' => $attribute->values()->count(),
                    'relevance_score' => $this->calculateRelevanceScore($attribute->getTranslatedName($locale), $query),
                ];
            }
            
            // Add attribute values
            foreach ($attributeValues as $value) {
                $results[] = [
                    'id' => $value->id,
                    'type' => 'attribute_value',
                    'title' => $value->getDisplayValue(),
                    'subtitle' => $value->attribute->getTranslatedName($locale),
                    'description' => Str::limit($value->getDisplayDescription(), 100),
                    'url' => route('localized.attribute.value.show', ['locale' => $locale, 'attribute' => $value->attribute->slug, 'value' => $value->id]),
                    'image' => null,
                    'color_code' => $value->color_code,
                    'relevance_score' => $this->calculateRelevanceScore($value->getDisplayValue(), $query),
                ];
            }
            
            return $results;
        });
    }

    /**
     * Get popular search suggestions
     */
    public function getPopularSuggestions(int $limit = 10): array
    {
        $cacheKey = "autocomplete_popular_{$limit}_" . app()->getLocale();
        
        return Cache::remember($cacheKey, 3600, function () use ($limit) { // Cache for 1 hour
            $locale = app()->getLocale();
            
            // Get popular products
            $popularProducts = Product::query()
                ->with(['media', 'brand'])
                ->where('is_visible', true)
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->orderBy('views_count', 'desc')
                ->limit($limit)
                ->get();
            
            return $popularProducts
                ->skipWhile(function (Product $product) {
                    // Skip popular products that are not properly configured or have missing essential data
                    return empty($product->name) || 
                           !$product->is_visible ||
                           empty($product->slug) ||
                           $product->price <= 0;
                })
                ->map(function (Product $product) use ($locale) {
                    return [
                        'id' => $product->id,
                        'type' => 'product',
                        'title' => $product->getTranslatedName($locale),
                        'subtitle' => $product->brand?->name,
                        'url' => route('localized.products.show', ['locale' => $locale, 'product' => $product->slug]),
                        'image' => $product->getFirstMediaUrl('images', 'thumb'),
                        'is_popular' => true,
                    ];
                })->toArray();
        });
    }

    /**
     * Get recent search suggestions (from session)
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
            if (!empty($quickResults)) {
                $results[] = array_merge($quickResults[0], [
                    'is_recent' => true,
                    'search_term' => $searchTerm,
                ]);
            }
        }
        
        return $results;
    }

    /**
     * Add search term to recent searches
     */
    public function addToRecentSearches(string $query): void
    {
        if (strlen($query) < 2) {
            return;
        }
        
        $recentSearches = session('recent_searches', []);
        
        // Remove if already exists
        $recentSearches = array_filter($recentSearches, fn($term) => $term !== $query);
        
        // Add to beginning
        array_unshift($recentSearches, $query);
        
        // Keep only last 10 searches
        $recentSearches = array_slice($recentSearches, 0, 10);
        
        session(['recent_searches' => $recentSearches]);
    }

    /**
     * Clear recent searches
     */
    public function clearRecentSearches(): void
    {
        session()->forget('recent_searches');
    }

    /**
     * Search by specific type
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
     * Calculate type limits based on total limit
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
     * Sort results by relevance
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
     * Calculate relevance score for a text match
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
        if (preg_match('/\b' . preg_quote($query, '/') . '\b/', $text)) {
            return 60;
        }
        
        // Fuzzy match gets lowest score
        $similarity = similar_text($text, $query, $percent);
        return (int) $percent;
    }

    /**
     * Prepare search term for database queries
     */
    private function prepareSearchTerm(string $query): string
    {
        return '%' . str_replace(['%', '_'], ['\%', '\_'], $query) . '%';
    }

    /**
     * Generate cache key
     */
    private function generateCacheKey(string $query, int $limit, array $types): string
    {
        $typesKey = empty($types) ? 'all' : implode('_', $types);
        return "autocomplete_{$typesKey}_{$query}_{$limit}_" . app()->getLocale();
    }
}
