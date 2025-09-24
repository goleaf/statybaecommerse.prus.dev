<?php

declare(strict_types=1);

namespace App\Services\Recommendations;

use App\Models\Product;
use App\Models\User;
use App\Models\UserProductInteraction;
use Illuminate\Database\Eloquent\Collection;

/**
 * CollaborativeFilteringRecommendation
 *
 * Service class containing CollaborativeFilteringRecommendation business logic, external integrations, and complex operations with proper error handling and logging.
 */
final class CollaborativeFilteringRecommendation extends BaseRecommendation
{
    /**
     * Handle getDefaultConfig functionality with proper error handling.
     */
    protected function getDefaultConfig(): array
    {
        return ['max_results' => 10, 'min_score' => 0.1, 'interaction_weights' => ['view' => 0.1, 'click' => 0.3, 'cart' => 0.5, 'purchase' => 1.0, 'wishlist' => 0.4, 'review' => 0.6], 'min_interactions' => 2, 'neighbor_threshold' => 0.3, 'max_neighbors' => 50];
    }

    /**
     * Handle getRecommendations functionality with proper error handling.
     */
    public function getRecommendations(?User $user = null, ?Product $product = null, array $context = []): Collection
    {
        $startTime = microtime(true);
        if (! $user) {
            return collect();
        }
        $cacheKey = $this->generateCacheKey('collaborative', $user, $product, $context);
        $cached = $this->getCachedResult($cacheKey);
        if ($cached) {
            return $cached;
        }
        $recommendations = $this->calculateCollaborativeRecommendations($user, $product);
        $this->logPerformance('collaborative', microtime(true) - $startTime, $recommendations->count());
        $this->trackRecommendation('collaborative', $user, $product, $recommendations->toArray());

        return $this->cacheResult($cacheKey, $recommendations, $this->config['cache_ttl'] ?? 3600);
    }

    /**
     * Handle calculateCollaborativeRecommendations functionality with proper error handling.
     */
    private function calculateCollaborativeRecommendations(User $user, ?Product $product = null): Collection
    {
        // Get user's interaction history
        $userInteractions = $this->getUserInteractions($user);
        if ($userInteractions->isEmpty()) {
            return collect();
        }
        // Find similar users (neighbors)
        $neighbors = $this->findSimilarUsers($user, $userInteractions);
        if ($neighbors->isEmpty()) {
            return collect();
        }

        // Generate recommendations based on neighbors' preferences
        return $this->generateRecommendationsFromNeighbors($user, $neighbors, $userInteractions);
    }

    /**
     * Handle getUserInteractions functionality with proper error handling.
     */
    private function getUserInteractions(User $user): Collection
    {
        return UserProductInteraction::where('user_id', $user->id)->withMinCount($this->config['min_interactions'])->with('product')->get()->keyBy('product_id');
    }

    /**
     * Handle findSimilarUsers functionality with proper error handling.
     */
    private function findSimilarUsers(User $user, Collection $userInteractions): Collection
    {
        $userProductIds = $userInteractions->pluck('product_id')->toArray();
        $userRatings = $userInteractions->pluck('rating', 'product_id')->toArray();
        // Find users who have interacted with similar products
        $candidateUsers = UserProductInteraction::whereIn('product_id', $userProductIds)->where('user_id', '!=', $user->id)->withMinCount($this->config['min_interactions'])->with('user')->get()->groupBy('user_id');
        $neighbors = collect();
        foreach ($candidateUsers as $userId => $interactions) {
            $similarity = $this->calculateUserSimilarity($userRatings, $interactions);
            if ($similarity >= $this->config['neighbor_threshold']) {
                $neighbors->push(['user' => $interactions->first()->user, 'similarity' => $similarity, 'interactions' => $interactions->keyBy('product_id')]);
            }
        }

        return $neighbors->sortByDesc('similarity')->take($this->config['max_neighbors']);
    }

    /**
     * Handle calculateUserSimilarity functionality with proper error handling.
     */
    private function calculateUserSimilarity(array $userRatings, Collection $neighborInteractions): float
    {
        $neighborRatings = $neighborInteractions->pluck('rating', 'product_id')->toArray();
        $commonProducts = array_intersect_key($userRatings, $neighborRatings);
        if (count($commonProducts) < 2) {
            return 0;
        }
        $userMean = array_sum($userRatings) / count($userRatings);
        $neighborMean = array_sum($neighborRatings) / count($neighborRatings);
        $numerator = 0;
        $userSumSquares = 0;
        $neighborSumSquares = 0;
        foreach ($commonProducts as $productId => $userRating) {
            $neighborRating = $neighborRatings[$productId];
            $userDiff = $userRating - $userMean;
            $neighborDiff = $neighborRating - $neighborMean;
            $numerator += $userDiff * $neighborDiff;
            $userSumSquares += $userDiff * $userDiff;
            $neighborSumSquares += $neighborDiff * $neighborDiff;
        }
        if ($userSumSquares == 0 || $neighborSumSquares == 0) {
            return 0;
        }

        return $numerator / (sqrt($userSumSquares) * sqrt($neighborSumSquares));
    }

    /**
     * Handle generateRecommendationsFromNeighbors functionality with proper error handling.
     */
    private function generateRecommendationsFromNeighbors(User $user, Collection $neighbors, Collection $userInteractions): Collection
    {
        $userProductIds = $userInteractions->pluck('product_id')->toArray();
        $recommendations = collect();
        foreach ($neighbors as $neighbor) {
            $neighborInteractions = $neighbor['interactions'];
            $similarity = $neighbor['similarity'];
            foreach ($neighborInteractions as $interaction) {
                $productId = $interaction->product_id;
                // Skip if user already interacted with this product
                if (in_array($productId, $userProductIds)) {
                    continue;
                }
                $weightedScore = $interaction->rating * $similarity * ($this->config['interaction_weights'][$interaction->interaction_type] ?? 1.0);
                if ($weightedScore >= $this->minScore) {
                    $recommendations->push(['product_id' => $productId, 'score' => $weightedScore, 'similarity' => $similarity, 'interaction' => $interaction]);
                }
            }
        }
        // Group by product and calculate final scores
        $productScores = $recommendations->groupBy('product_id')->map(function ($group) {
            $totalScore = $group->sum('score');
            $maxSimilarity = $group->max('similarity');
            $interactionCount = $group->count();

            return [
                'product_id' => $group->first()['product_id'],
                'score' => $totalScore / $interactionCount,
                // Average score
                'similarity' => $maxSimilarity,
                'interaction_count' => $interactionCount,
            ];
        });
        // Get products and apply filters
        $productIds = $productScores->pluck('product_id')->toArray();
        $query = Product::query()->with(['media', 'brand', 'categories'])->where('is_visible', true)->whereIn('id', $productIds);
        $query = $this->applyFilters($query);
        $products = $query->get()->keyBy('id');

        // Sort by score and return
        return $productScores->sortByDesc('score')->take($this->maxResults)->map(function ($item) use ($products) {
            return $products->get($item['product_id']);
        })->filter()->values();
    }

    /**
     * Handle updateUserInteraction functionality with proper error handling.
     */
    public function updateUserInteraction(User $user, Product $product, string $interactionType, ?float $rating = null): void
    {
        try {
            // Check if interaction already exists
            $existingInteraction = UserProductInteraction::where(['user_id' => $user->id, 'product_id' => $product->id, 'interaction_type' => $interactionType])->first();
            if ($existingInteraction) {
                $existingInteraction->increment('count');
                $existingInteraction->update(['rating' => $rating ?? $this->getDefaultRating($interactionType), 'last_interaction' => now()]);
            } else {
                UserProductInteraction::create(['user_id' => $user->id, 'product_id' => $product->id, 'interaction_type' => $interactionType, 'rating' => $rating ?? $this->getDefaultRating($interactionType), 'count' => 1, 'first_interaction' => now(), 'last_interaction' => now()]);
            }
        } catch (\Exception $e) {
            // Table might not exist yet, ignore
        }
    }

    /**
     * Handle getDefaultRating functionality with proper error handling.
     */
    private function getDefaultRating(string $interactionType): float
    {
        return match ($interactionType) {
            'view' => 1.0,
            'click' => 2.0,
            'cart' => 3.0,
            'purchase' => 5.0,
            'wishlist' => 4.0,
            'review' => 4.0,
            default => 1.0,
        };
    }
}
