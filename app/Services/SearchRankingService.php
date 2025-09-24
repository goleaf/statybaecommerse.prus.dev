<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;

final class SearchRankingService
{
    private const CACHE_PREFIX = 'search_ranking_';

    private const CACHE_TTL = 3600; // 1 hour

    /**
     * Rank search results based on multiple factors
     */
    public function rankResults(array $results, string $query, array $context = []): array
    {
        if (empty($results)) {
            return $results;
        }

        $cacheKey = self::CACHE_PREFIX.'ranked_'.md5($query.serialize($context));

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($results, $query, $context) {
            $rankedResults = [];

            foreach ($results as $result) {
                $score = $this->calculateRankingScore($result, $query, $context);
                $result['ranking_score'] = $score;
                $rankedResults[] = $result;
            }

            // Sort by ranking score (highest first)
            usort($rankedResults, fn ($a, $b) => $b['ranking_score'] <=> $a['ranking_score']);

            return $rankedResults;
        });
    }

    /**
     * Calculate ranking score for a single result
     */
    private function calculateRankingScore(array $result, string $query, array $context): float
    {
        $score = 0.0;

        // Base relevance score (if available)
        if (isset($result['relevance_score'])) {
            $score += $result['relevance_score'] * 0.3;
        }

        // Text matching score
        $score += $this->calculateTextMatchingScore($result, $query) * 0.4;

        // Popularity score
        $score += $this->calculatePopularityScore($result) * 0.2;

        // Context score
        $score += $this->calculateContextScore($result, $context) * 0.1;

        return round($score, 2);
    }

    /**
     * Calculate text matching score
     */
    private function calculateTextMatchingScore(array $result, string $query): float
    {
        $score = 0.0;
        $queryLower = strtolower($query);
        $queryWords = explode(' ', $queryLower);

        // Title matching (highest weight)
        if (isset($result['title'])) {
            $titleLower = strtolower($result['title']);
            $score += $this->calculateWordMatchingScore($titleLower, $queryWords) * 0.5;
        }

        // Subtitle matching
        if (isset($result['subtitle'])) {
            $subtitleLower = strtolower($result['subtitle']);
            $score += $this->calculateWordMatchingScore($subtitleLower, $queryWords) * 0.3;
        }

        // Description matching
        if (isset($result['description'])) {
            $descriptionLower = strtolower($result['description']);
            $score += $this->calculateWordMatchingScore($descriptionLower, $queryWords) * 0.2;
        }

        return $score;
    }

    /**
     * Calculate word matching score
     */
    private function calculateWordMatchingScore(string $text, array $queryWords): float
    {
        $score = 0.0;
        $textWords = explode(' ', $text);

        foreach ($queryWords as $queryWord) {
            foreach ($textWords as $textWord) {
                if ($textWord === $queryWord) {
                    $score += 1.0; // Exact match
                } elseif (strpos($textWord, $queryWord) === 0) {
                    $score += 0.8; // Starts with
                } elseif (strpos($textWord, $queryWord) !== false) {
                    $score += 0.6; // Contains
                }
            }
        }

        return $score;
    }

    /**
     * Calculate popularity score
     */
    private function calculatePopularityScore(array $result): float
    {
        $score = 0.0;

        // Product-specific popularity factors
        if ($result['type'] === 'product') {
            // Sales count (if available)
            if (isset($result['sales_count'])) {
                $score += min($result['sales_count'] / 100, 1.0) * 0.4;
            }

            // Reviews count
            if (isset($result['reviews_count'])) {
                $score += min($result['reviews_count'] / 50, 1.0) * 0.3;
            }

            // Average rating
            if (isset($result['average_rating'])) {
                $score += ($result['average_rating'] / 5.0) * 0.3;
            }

            // Featured products boost
            if (isset($result['is_featured']) && $result['is_featured']) {
                $score += 0.2;
            }
        }

        // Category-specific popularity factors
        if ($result['type'] === 'category') {
            // Products count
            if (isset($result['products_count'])) {
                $score += min($result['products_count'] / 1000, 1.0) * 0.6;
            }

            // Children count
            if (isset($result['children_count'])) {
                $score += min($result['children_count'] / 100, 1.0) * 0.4;
            }
        }

        // Brand-specific popularity factors
        if ($result['type'] === 'brand') {
            // Products count
            if (isset($result['products_count'])) {
                $score += min($result['products_count'] / 500, 1.0) * 1.0;
            }
        }

        // Customer-specific popularity factors
        if ($result['type'] === 'customer') {
            // Orders count
            if (isset($result['orders_count'])) {
                $score += min($result['orders_count'] / 50, 1.0) * 0.6;
            }

            // Total spent
            if (isset($result['total_spent'])) {
                $score += min($result['total_spent'] / 10000, 1.0) * 0.4;
            }
        }

        // Order-specific popularity factors
        if ($result['type'] === 'order') {
            // Order total
            if (isset($result['total'])) {
                $score += min($result['total'] / 1000, 1.0) * 0.5;
            }

            // Items count
            if (isset($result['items_count'])) {
                $score += min($result['items_count'] / 20, 1.0) * 0.5;
            }
        }

        return $score;
    }

    /**
     * Calculate context score based on user context
     */
    private function calculateContextScore(array $result, array $context): float
    {
        $score = 0.0;

        // User preferences
        if (isset($context['user_id'])) {
            $score += $this->calculateUserPreferenceScore($result, $context['user_id']) * 0.5;
        }

        // Search history
        if (isset($context['search_history'])) {
            $score += $this->calculateSearchHistoryScore($result, $context['search_history']) * 0.3;
        }

        // Location context
        if (isset($context['location'])) {
            $score += $this->calculateLocationScore($result, $context['location']) * 0.2;
        }

        return $score;
    }

    /**
     * Calculate user preference score
     */
    private function calculateUserPreferenceScore(array $result, int $userId): float
    {
        $cacheKey = self::CACHE_PREFIX.'user_prefs_'.$userId;

        return Cache::remember($cacheKey, 1800, function () {
            // This would typically analyze user's past behavior
            // For now, return a base score
            return 0.5;
        });
    }

    /**
     * Calculate search history score
     */
    private function calculateSearchHistoryScore(array $result, array $searchHistory): float
    {
        $score = 0.0;

        foreach ($searchHistory as $search) {
            if (isset($search['query'])) {
                $similarity = $this->calculateQuerySimilarity($result, $search['query']);
                $score += $similarity * 0.1;
            }
        }

        return min($score, 1.0);
    }

    /**
     * Calculate location score
     */
    private function calculateLocationScore(array $result, array $location): float
    {
        // This would typically consider geographic relevance
        // For now, return a base score
        return 0.3;
    }

    /**
     * Calculate query similarity
     */
    private function calculateQuerySimilarity(array $result, string $query): float
    {
        $resultText = '';

        if (isset($result['title'])) {
            $resultText .= $result['title'].' ';
        }

        if (isset($result['description'])) {
            $resultText .= $result['description'].' ';
        }

        $resultWords = explode(' ', strtolower($resultText));
        $queryWords = explode(' ', strtolower($query));

        $commonWords = array_intersect($resultWords, $queryWords);

        return count($commonWords) / max(count($queryWords), 1);
    }

    /**
     * Boost results based on business rules
     */
    public function applyBusinessRules(array $results, array $rules = []): array
    {
        foreach ($results as &$result) {
            $boost = 0.0;

            // Featured products boost
            if ($result['type'] === 'product' && isset($result['is_featured']) && $result['is_featured']) {
                $boost += 0.2;
            }

            // In-stock products boost
            if ($result['type'] === 'product' && isset($result['in_stock']) && $result['in_stock']) {
                $boost += 0.1;
            }

            // Recent orders boost
            if ($result['type'] === 'order' && isset($result['created_at'])) {
                $daysSinceCreated = now()->diffInDays($result['created_at']);
                if ($daysSinceCreated < 7) {
                    $boost += 0.15;
                }
            }

            // Active customers boost
            if ($result['type'] === 'customer' && isset($result['is_active']) && $result['is_active']) {
                $boost += 0.1;
            }

            $result['ranking_score'] = ($result['ranking_score'] ?? 0) + $boost;
        }

        // Re-sort by updated ranking score
        usort($results, fn ($a, $b) => $b['ranking_score'] <=> $a['ranking_score']);

        return $results;
    }

    /**
     * Get ranking explanation for debugging
     */
    public function getRankingExplanation(array $result, string $query): array
    {
        return [
            'text_matching' => $this->calculateTextMatchingScore($result, $query),
            'popularity' => $this->calculatePopularityScore($result),
            'context' => $this->calculateContextScore($result, []),
            'total_score' => $result['ranking_score'] ?? 0,
        ];
    }
}
