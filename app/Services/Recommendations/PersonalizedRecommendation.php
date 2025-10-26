<?php

declare(strict_types=1);

namespace App\Services\Recommendations;

use App\Models\Product;
use App\Models\User;
use App\Models\UserPreference;
use App\Models\UserProductInteraction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;

/**
 * PersonalizedRecommendation
 *
 * Service class containing PersonalizedRecommendation business logic, external integrations,
 * and complex operations with proper error handling and logging.
 */
final class PersonalizedRecommendation extends BaseRecommendation
{
    /**
     * Handle getDefaultConfig functionality with proper error handling.
     */
    protected function getDefaultConfig(): array
    {
        return [
            // Prioritise a modest list of products to keep the widget focused.
            'max_results'        => 10,
            'min_score'          => 0.15,
            // Weighting that favours strong user preferences while still factoring in recency.
            'preference_weights' => [
                'category' => 0.45,
                'brand'    => 0.25,
                'price'    => 0.1,
                'behavior' => 0.2,
            ],
            // Respect fresh interactions more strongly while still retaining historical context.
            'recent_days'        => 30,
            'cache_ttl'          => 1800,
            'filters'            => [
                ['type' => 'where', 'field' => 'is_visible', 'value' => true],
            ],
        ];
    }

    /**
     * Handle getRecommendations functionality with proper error handling.
     */
    public function getRecommendations(?User $user = null, ?Product $product = null, array $context = []): Collection
    {
        if (! $user) {
            // Without a user there is no behaviour history to personalise against.
            return collect();
        }

        $cacheKey = $this->generateCacheKey('personalized', $user, $product, $context);
        if ($cached = $this->getCachedResult($cacheKey)) {
            return $cached;
        }

        $startTime = microtime(true);
        $recommendations = $this->generatePersonalizedRecommendations($user, $product);
        $this->logPerformance('personalized', microtime(true) - $startTime, $recommendations->count());
        $this->trackRecommendation('personalized', $user, $product, $recommendations->toArray());

        return $this->cacheResult($cacheKey, $recommendations, $this->config['cache_ttl']);
    }

    /**
     * Handle generatePersonalizedRecommendations functionality with proper error handling.
     */
    private function generatePersonalizedRecommendations(User $user, ?Product $product): Collection
    {
        $preferenceVectors = $this->buildPreferenceVectors($user);
        $excludedProductIds = $this->getExcludedProductIds($user, $product);

        $query = Product::query()
            ->with(['media', 'brand', 'categories'])
            ->where('is_visible', true)
            ->whereNotIn('id', $excludedProductIds);

        $query = $this->applyFilters($query);

        $products = $query->get()->map(function (Product $candidate) use ($preferenceVectors) {
            $score = $this->scoreCandidate($candidate, $preferenceVectors);
            $candidate->setAttribute('personalization_score', $score);

            return $candidate;
        })->filter(fn (Product $candidate) => $candidate->getAttribute('personalization_score') >= $this->minScore);

        return $products
            ->sortByDesc('personalization_score')
            ->values()
            ->take($this->maxResults);
    }

    /**
     * Handle buildPreferenceVectors functionality with proper error handling.
     *
     * @return array<string, array<string, float>>
     */
    private function buildPreferenceVectors(User $user): array
    {
        $weights = $this->config['preference_weights'];
        $preferences = UserPreference::query()
            ->where('user_id', $user->id)
            ->whereNotNull('preference_score')
            ->get();

        $vectors = [
            'category' => [],
            'brand'    => [],
            'price'    => [],
        ];

        foreach ($preferences as $preference) {
            $type = $preference->getAttribute('preference_type');
            $key = (string) $preference->getAttribute('preference_key');
            $score = (float) $preference->getAttribute('preference_score');

            if (! isset($vectors[$type])) {
                continue;
            }

            $vectors[$type][$key] = $score * ($weights[$type] ?? 1.0);
        }

        return $vectors;
    }

    /**
     * Handle getExcludedProductIds functionality with proper error handling.
     *
     * @return list<int>
     */
    private function getExcludedProductIds(User $user, ?Product $product): array
    {
        $interactionIds = UserProductInteraction::query()
            ->where('user_id', $user->id)
            ->pluck('product_id')
            ->map(static fn ($id) => (int) $id)
            ->all();

        if ($product) {
            $interactionIds[] = $product->id;
        }

        return array_values(array_unique($interactionIds));
    }

    /**
     * Handle scoreCandidate functionality with proper error handling.
     */
    private function scoreCandidate(Product $product, array $vectors): float
    {
        $categoryIds = $product->categories->pluck('id')->map(static fn ($id) => (string) $id)->all();
        $brandId = $product->brand_id ? (string) $product->brand_id : null;
        $priceBucket = $this->getPriceRange($product->price);

        $categoryScore = $this->aggregateVectorScore($categoryIds, Arr::get($vectors, 'category', []));
        $brandScore = $brandId ? Arr::get($vectors, "brand.{$brandId}", 0.0) : 0.0;
        $priceScore = Arr::get($vectors, "price.{$priceBucket}", 0.0);

        $behaviorWeight = $this->config['preference_weights']['behavior'] ?? 0.0;
        $behaviorBoost = $behaviorWeight > 0 ? $this->deriveBehaviorBoost($product) * $behaviorWeight : 0.0;

        return $categoryScore + $brandScore + $priceScore + $behaviorBoost;
    }

    /**
     * Handle aggregateVectorScore functionality with proper error handling.
     *
     * @param list<string>              $keys
     * @param array<string, float|int> $vector
     */
    private function aggregateVectorScore(array $keys, array $vector): float
    {
        $score = 0.0;
        foreach ($keys as $key) {
            $score += (float) ($vector[$key] ?? 0.0);
        }

        return $score;
    }

    /**
     * Handle deriveBehaviorBoost functionality with proper error handling.
     */
    private function deriveBehaviorBoost(Product $product): float
    {
        $behaviorMeta = $product->getAttribute('behavior_metrics');
        if (! is_array($behaviorMeta)) {
            return 0.0;
        }

        $recentViews = (float) ($behaviorMeta['recent_views'] ?? 0.0);
        $recentPurchases = (float) ($behaviorMeta['recent_purchases'] ?? 0.0);

        return ($recentViews * 0.05) + ($recentPurchases * 0.1);
    }
}
