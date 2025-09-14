<?php

declare(strict_types=1);

namespace App\Services\Recommendations;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

final class HybridRecommendation extends BaseRecommendation
{
    protected function getDefaultConfig(): array
    {
        return [
            'max_results' => 10,
            'min_score' => 0.1,
            'algorithm_weights' => [
                'content_based' => 0.4,
                'collaborative' => 0.4,
                'popularity' => 0.2,
            ],
            'fallback_algorithms' => ['popularity', 'trending'],
            'content_based_config' => [],
            'collaborative_config' => [],
            'popularity_config' => [],
        ];
    }

    public function getRecommendations(
        ?User $user = null,
        ?Product $product = null,
        array $context = []
    ): Collection {
        $startTime = microtime(true);

        $cacheKey = $this->generateCacheKey('hybrid', $user, $product, $context);
        $cached = $this->getCachedResult($cacheKey);
        
        if ($cached) {
            return $cached;
        }

        $recommendations = $this->calculateHybridRecommendations($user, $product, $context);

        $this->logPerformance('hybrid', microtime(true) - $startTime, $recommendations->count());
        $this->trackRecommendation('hybrid', $user, $product, $recommendations->toArray());

        return $this->cacheResult($cacheKey, $recommendations, $this->config['cache_ttl'] ?? 3600);
    }

    private function calculateHybridRecommendations(
        ?User $user = null,
        ?Product $product = null,
        array $context = []
    ): Collection {
        $algorithmResults = [];
        $weights = $this->config['algorithm_weights'];

        // Content-based recommendations
        if (isset($weights['content_based']) && $product) {
            $contentBased = new ContentBasedRecommendation($this->config['content_based_config']);
            $contentResults = $contentBased->getRecommendations($user, $product, $context);
            $algorithmResults['content_based'] = $this->normalizeScores($contentResults, $weights['content_based']);
        }

        // Collaborative filtering recommendations
        if (isset($weights['collaborative']) && $user) {
            $collaborative = new CollaborativeFilteringRecommendation($this->config['collaborative_config']);
            $collaborativeResults = $collaborative->getRecommendations($user, $product, $context);
            $algorithmResults['collaborative'] = $this->normalizeScores($collaborativeResults, $weights['collaborative']);
        }

        // Popularity-based recommendations
        if (isset($weights['popularity'])) {
            $popularity = new PopularityRecommendation($this->config['popularity_config']);
            $popularityResults = $popularity->getRecommendations($user, $product, $context);
            $algorithmResults['popularity'] = $this->normalizeScores($popularityResults, $weights['popularity']);
        }

        // If no algorithms produced results, use fallback
        if (empty($algorithmResults)) {
            return $this->getFallbackRecommendations($user, $product, $context);
        }

        // Combine results from all algorithms
        $combinedScores = $this->combineAlgorithmResults($algorithmResults);

        // Get products and apply filters
        $productIds = array_keys($combinedScores);
        
        $query = Product::query()
            ->with(['media', 'brand', 'categories'])
            ->where('is_visible', true)
            ->whereIn('id', $productIds);

        $query = $this->applyFilters($query);
        $products = $query->get()->keyBy('id');

        // Sort by combined score and return
        return collect($combinedScores)
            ->sortByDesc('score')
            ->take($this->maxResults)
            ->map(function ($item, $productId) use ($products) {
                return $products->get($productId);
            })
            ->filter()
            ->values();
    }

    private function normalizeScores(Collection $results, float $weight): array
    {
        $scores = [];
        
        foreach ($results as $index => $product) {
            // Normalize based on position and weight
            $normalizedScore = (1.0 - ($index * 0.1)) * $weight;
            $scores[$product->id] = [
                'product' => $product,
                'score' => max($normalizedScore, 0.1),
                'weight' => $weight,
            ];
        }

        return $scores;
    }

    private function combineAlgorithmResults(array $algorithmResults): array
    {
        $combinedScores = [];
        
        foreach ($algorithmResults as $algorithm => $results) {
            foreach ($results as $productId => $result) {
                if (!isset($combinedScores[$productId])) {
                    $combinedScores[$productId] = [
                        'score' => 0,
                        'algorithms' => [],
                        'product' => $result['product'],
                    ];
                }
                
                $combinedScores[$productId]['score'] += $result['score'];
                $combinedScores[$productId]['algorithms'][] = $algorithm;
            }
        }

        // Normalize combined scores
        $maxScore = max(array_column($combinedScores, 'score'));
        if ($maxScore > 0) {
            foreach ($combinedScores as &$score) {
                $score['score'] = $score['score'] / $maxScore;
            }
        }

        return $combinedScores;
    }

    private function getFallbackRecommendations(
        ?User $user = null,
        ?Product $product = null,
        array $context = []
    ): Collection {
        $fallbackAlgorithms = $this->config['fallback_algorithms'];
        
        foreach ($fallbackAlgorithms as $algorithm) {
            $recommendations = $this->getAlgorithmRecommendations($algorithm, $user, $product, $context);
            
            if ($recommendations->isNotEmpty()) {
                return $recommendations;
            }
        }

        return collect();
    }

    private function getAlgorithmRecommendations(
        string $algorithm,
        ?User $user = null,
        ?Product $product = null,
        array $context = []
    ): Collection {
        return match ($algorithm) {
            'popularity' => (new PopularityRecommendation())->getRecommendations($user, $product, $context),
            'trending' => (new TrendingRecommendation())->getRecommendations($user, $product, $context),
            'content_based' => (new ContentBasedRecommendation())->getRecommendations($user, $product, $context),
            'collaborative' => (new CollaborativeFilteringRecommendation())->getRecommendations($user, $product, $context),
            'cross_sell' => (new CrossSellRecommendation())->getRecommendations($user, $product, $context),
            'up_sell' => (new UpSellRecommendation())->getRecommendations($user, $product, $context),
            default => collect(),
        };
    }

    public function adjustWeights(array $performanceData): void
    {
        // Dynamically adjust algorithm weights based on performance
        $totalCtr = array_sum(array_column($performanceData, 'ctr'));
        
        if ($totalCtr > 0) {
            foreach ($performanceData as $algorithm => $data) {
                $this->config['algorithm_weights'][$algorithm] = $data['ctr'] / $totalCtr;
            }
        }
    }

    public function getAlgorithmPerformance(): array
    {
        $performance = [];
        
        foreach ($this->config['algorithm_weights'] as $algorithm => $weight) {
            $performance[$algorithm] = [
                'weight' => $weight,
                'enabled' => $weight > 0,
            ];
        }

        return $performance;
    }
}
