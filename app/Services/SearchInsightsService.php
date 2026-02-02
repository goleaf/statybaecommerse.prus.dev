<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
use Log;

/**
 * SearchInsightsService
 *
 * Service class containing SearchInsightsService business logic, external integrations, and complex operations with proper error handling and logging.
 */
final class SearchInsightsService
{
    private const CACHE_PREFIX = 'search_insights:';

    private const CACHE_TTL = 3600; // 1 hour

    private const INSIGHTS_CACHE_TTL = 1800; // 30 minutes

    /**
     * Handle getSearchInsights functionality with proper error handling.
     */
    public function getSearchInsights(string $query, array $context = []): array
    {
        try {
            $cacheKey = self::CACHE_PREFIX . 'insights_' . md5($query . serialize($context));

            return Cache::remember($cacheKey, self::INSIGHTS_CACHE_TTL, function () use ($query, $context) {
                return [
                    'query_analysis'     => $this->analyzeQuery($query),
                    'search_suggestions' => $this->getSearchSuggestions($query),
                ];
            });
        } catch (Exception $e) {
            Log::warning('Search insights generation failed: ' . $e->getMessage());

            return [
                'query_analysis'     => [],
                'search_suggestions' => [],
            ];
        }
    }

    /**
     * Handle analyzeQuery functionality with proper error handling.
     */
    private function analyzeQuery(string $query): array
    {
        try {
            $words = explode(' ', strtolower(trim($query)));
            $wordCount = count($words);
            $avgWordLength = $wordCount > 0 ? array_sum(array_map('strlen', $words)) / $wordCount : 0;

            return [
                'word_count'            => $wordCount,
                'character_count'       => strlen($query),
                'average_word_length'   => round($avgWordLength, 2),
                'complexity_score'      => $this->calculateComplexityScore($query),
                'language_detection'    => $this->detectLanguage($query),
                'intent_classification' => $this->classifyIntent($query),
                'entity_extraction'     => $this->extractEntities($query),
                'sentiment_analysis'    => $this->analyzeSentiment($query),
            ];
        } catch (Exception $e) {
            Log::warning('Query analysis failed: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Handle getSearchSuggestions functionality with proper error handling.
     */
    private function getSearchSuggestions(string $query): array
    {
        try {
            $cacheKey = self::CACHE_PREFIX . 'suggestions_' . md5($query);

            return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($query) {
                $autocompleteService = app(AutocompleteService::class);
                $results = $autocompleteService->search($query, 50);

                $suggestions = [];
                $queryWords = explode(' ', strtolower($query));

                foreach ($results as $result) {
                    if (isset($result['title'])) {
                        $titleWords = explode(' ', strtolower($result['title']));
                        $newWords = array_diff($titleWords, $queryWords);

                        foreach ($newWords as $word) {
                            if (strlen($word) >= 3) {
                                $suggestions[] = $word;
                            }
                        }
                    }
                }

                $wordCounts = array_count_values($suggestions);
                arsort($wordCounts);

                return array_slice(array_keys($wordCounts), 0, 10);
            });
        } catch (Exception $e) {
            Log::warning('Search suggestions generation failed: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Handle calculateComplexityScore functionality with proper error handling.
     */
    private function calculateComplexityScore(string $query): float
    {
        $words = explode(' ', strtolower(trim($query)));
        $wordCount = count($words);
        $avgWordLength = $wordCount > 0 ? array_sum(array_map('strlen', $words)) / $wordCount : 0;

        // Simple complexity scoring based on word count and average length
        $complexity = ($wordCount * 0.3) + ($avgWordLength * 0.1);

        return min(round($complexity, 2), 10.0);
    }

    /**
     * Handle detectLanguage functionality with proper error handling.
     */
    private function detectLanguage(string $query): string
    {
        // Simple language detection based on character patterns
        if (preg_match('/[ąčęėįšųūž]/i', $query)) {
            return 'lt';
        } elseif (preg_match('/[a-z]/i', $query)) {
            return 'en';
        }

        return 'unknown';
    }

    /**
     * Handle classifyIntent functionality with proper error handling.
     */
    private function classifyIntent(string $query): string
    {
        $query = strtolower($query);

        if (preg_match('/\b(buy|purchase|order|shop)\b/', $query)) {
            return 'purchase';
        } elseif (preg_match('/\b(compare|vs|versus)\b/', $query)) {
            return 'compare';
        } elseif (preg_match('/\b(how|what|where|when|why)\b/', $query)) {
            return 'informational';
        }

        return 'general';
    }

    /**
     * Handle extractEntities functionality with proper error handling.
     */
    private function extractEntities(string $query): array
    {
        $entities = [];

        // Extract potential product names (capitalized words)
        if (preg_match_all('/\b[A-Z][a-z]+\b/', $query, $matches)) {
            $entities['products'] = $matches[0];
        }

        // Extract potential numbers (prices, quantities)
        if (preg_match_all('/\b\d+(?:\.\d+)?\b/', $query, $matches)) {
            $entities['numbers'] = $matches[0];
        }

        // Extract potential brands (common brand patterns)
        $brands = ['nike', 'adidas', 'apple', 'samsung', 'sony', 'lg'];
        foreach ($brands as $brand) {
            if (stripos($query, $brand) !== false) {
                $entities['brands'][] = $brand;
            }
        }

        return $entities;
    }

    /**
     * Handle analyzeSentiment functionality with proper error handling.
     */
    private function analyzeSentiment(string $query): array
    {
        $positiveWords = ['good', 'great', 'excellent', 'amazing', 'best', 'love', 'perfect'];
        $negativeWords = ['bad', 'terrible', 'awful', 'worst', 'hate', 'broken', 'defective'];

        $query = strtolower($query);
        $positiveCount = 0;
        $negativeCount = 0;

        foreach ($positiveWords as $word) {
            if (strpos((string) $query, $word) !== false) {
                $positiveCount++;
            }
        }

        foreach ($negativeWords as $word) {
            if (strpos((string) $query, $word) !== false) {
                $negativeCount++;
            }
        }

        if ($positiveCount > $negativeCount) {
            return ['sentiment' => 'positive', 'score' => 0.7];
        } elseif ($negativeCount > $positiveCount) {
            return ['sentiment' => 'negative', 'score' => -0.7];
        }

        return ['sentiment' => 'neutral', 'score' => 0.0];
    }
}