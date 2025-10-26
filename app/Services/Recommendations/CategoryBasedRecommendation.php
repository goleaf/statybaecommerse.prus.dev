<?php

declare(strict_types=1);

namespace App\Services\Recommendations;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

/**
 * CategoryBasedRecommendation
 *
 * Service class containing CategoryBasedRecommendation business logic, external integrations,
 * and complex operations with proper error handling and logging.
 */
final class CategoryBasedRecommendation extends BaseRecommendation
{
    /**
     * Handle getDefaultConfig functionality with proper error handling.
     */
    protected function getDefaultConfig(): array
    {
        return [
            // By default recommend up to eight items that share the active category context.
            'max_results' => 8,
            'min_score'   => 0.1,
            // Allow configuring how far up the category tree we should look when broadening matches.
            'include_parent' => true,
            // Let integrators override the ordering column for deterministic storefront output.
            'order_by'        => 'relevance_score',
            'order_direction' => 'desc',
            // Provide a TTL that mirrors the default block cache duration.
            'cache_ttl' => 1800,
            // Keep a sane default filter set so results remain purchasable.
            'filters' => [
                ['type' => 'where', 'field' => 'is_visible', 'value' => true],
            ],
        ];
    }

    /**
     * Handle getRecommendations functionality with proper error handling.
     */
    public function getRecommendations(?User $user = null, ?Product $product = null, array $context = []): Collection
    {
        // Deterministically derive the cache key so storefront widgets can reuse the computation.
        $cacheKey = $this->generateCacheKey('category_based', $user, $product, $context);
        if ($cached = $this->getCachedResult($cacheKey)) {
            return $cached;
        }

        $startTime = microtime(true);
        $categoryIds = $this->resolveCategoryIds($product, $context);
        if (empty($categoryIds)) {
            // No category means there is no meaningful within-category suggestion, so return an empty collection.
            return collect();
        }

        $recommendations = $this->buildCategoryRecommendations($categoryIds, $product);
        $this->logPerformance('category_based', microtime(true) - $startTime, $recommendations->count());
        $this->trackRecommendation('category_based', $user, $product, $recommendations->toArray());

        return $this->cacheResult($cacheKey, $recommendations, $this->config['cache_ttl']);
    }

    /**
     * Handle resolveCategoryIds functionality with proper error handling.
     *
     * @param  array<string, mixed> $context
     * @return list<int>
     */
    private function resolveCategoryIds(?Product $product, array $context): array
    {
        // Prefer explicit context provided by the caller (category page widgets, filters, etc.).
        if (isset($context['category_id'])) {
            return [(int) $context['category_id']];
        }

        if (! $product) {
            return [];
        }

        $categoryIds = $product->categories->pluck('id')->map(static fn ($id) => (int) $id)->all();
        if ($categoryIds === []) {
            return [];
        }

        if (! empty($categoryIds) || ! $this->config['include_parent']) {
            return $categoryIds;
        }

        // Optionally include the immediate parent category to expand coverage for sparse catalogues.
        $parentIds = Category::query()
            ->whereIn('id', $categoryIds)
            ->pluck('parent_id')
            ->filter()
            ->map(static fn ($id) => (int) $id)
            ->all();

        return array_values(array_unique(array_merge($categoryIds, $parentIds)));
    }

    /**
     * Handle buildCategoryRecommendations functionality with proper error handling.
     *
     * @param list<int> $categoryIds
     */
    private function buildCategoryRecommendations(array $categoryIds, ?Product $product): Collection
    {
        $query = Product::query()
            ->with(['media', 'brand', 'categories'])
            ->where('is_visible', true)
            ->whereHas('categories', function ($query) use ($categoryIds): void {
                $query->whereIn('categories.id', $categoryIds);
            });

        if ($product) {
            // Skip the source product so shoppers only see alternative options.
            $query->where('id', '!=', $product->id);
        }

        $query = $this->applyFilters($query);
        $query->orderBy($this->config['order_by'] ?? 'relevance_score', $this->config['order_direction'] ?? 'desc');

        $products = $query->limit($this->maxResults)->get();

        // Attach lightweight metadata so downstream analytics can diagnose category depth coverage.
        return $products->map(function (Product $candidate) use ($categoryIds) {
            $candidate->setAttribute('recommendation_context', [
                'matched_categories' => $categoryIds,
                'strategy'           => 'category_based',
            ]);

            return $candidate;
        });
    }
}
