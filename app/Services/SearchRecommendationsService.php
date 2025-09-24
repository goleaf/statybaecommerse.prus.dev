<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;

/**
 * SearchRecommendationsService
 *
 * Service class containing SearchRecommendationsService business logic, external integrations, and complex operations with proper error handling and logging.
 */
final class SearchRecommendationsService
{
    private const CACHE_PREFIX = 'search_recommendations:';

    private const CACHE_TTL = 3600; // 1 hour

    private const RECOMMENDATIONS_CACHE_TTL = 1800; // 30 minutes

    /**
     * Handle getSearchRecommendations functionality with proper error handling.
     */
    public function getSearchRecommendations(string $query, array $context = []): array
    {
        try {
            $cacheKey = self::CACHE_PREFIX.'recommendations_'.md5($query.serialize($context));

            return Cache::remember($cacheKey, self::RECOMMENDATIONS_CACHE_TTL, function () use ($query, $context) {
                return [
                    'related_products' => $this->getRelatedProducts($query, $context),
                    'similar_searches' => $this->getSimilarSearches($query),
                    'trending_searches' => $this->getTrendingSearches($context),
                    'personalized_recommendations' => $this->getPersonalizedRecommendations($query, $context),
                    'cross_sell_suggestions' => $this->getCrossSellSuggestions($query, $context),
                    'upsell_suggestions' => $this->getUpsellSuggestions($query, $context),
                    'category_recommendations' => $this->getCategoryRecommendations($query, $context),
                    'brand_recommendations' => $this->getBrandRecommendations($query, $context),
                    'price_recommendations' => $this->getPriceRecommendations($query, $context),
                    'seasonal_recommendations' => $this->getSeasonalRecommendations($query, $context),
                ];
            });
        } catch (\Exception $e) {
            \Log::warning('Search recommendations generation failed: '.$e->getMessage());

            return [
                'related_products' => [],
                'similar_searches' => [],
                'trending_searches' => [],
                'personalized_recommendations' => [],
                'cross_sell_suggestions' => [],
                'upsell_suggestions' => [],
                'category_recommendations' => [],
                'brand_recommendations' => [],
                'price_recommendations' => [],
                'seasonal_recommendations' => [],
            ];
        }
    }

    /**
     * Handle getRelatedProducts functionality with proper error handling.
     */
    private function getRelatedProducts(string $query, array $context): array
    {
        try {
            $autocompleteService = app(AutocompleteService::class);
            $results = $autocompleteService->search($query, 20, ['products']);

            $relatedProducts = [];
            foreach ($results as $result) {
                if ($result['type'] === 'product') {
                    $relatedProducts[] = [
                        'id' => $result['id'],
                        'title' => $result['title'],
                        'price' => $result['formatted_price'] ?? null,
                        'image' => $result['image'],
                        'url' => $result['url'],
                        'relevance_score' => $result['relevance_score'] ?? 0,
                        'similarity_reason' => $this->getSimilarityReason($query, $result),
                    ];
                }
            }

            return array_slice($relatedProducts, 0, 10);
        } catch (\Exception $e) {
            \Log::warning('Related products generation failed: '.$e->getMessage());

            return [];
        }
    }

    /**
     * Handle getSimilarSearches functionality with proper error handling.
     */
    private function getSimilarSearches(string $query): array
    {
        try {
            $cacheKey = self::CACHE_PREFIX.'similar_'.md5($query);

            return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($query) {
                $analyticsService = app(SearchAnalyticsService::class);
                $popularSearches = $analyticsService->getPopularSearchesForDateRange(50);

                $similarSearches = [];
                $queryWords = explode(' ', strtolower($query));

                foreach ($popularSearches as $search) {
                    $searchWords = explode(' ', strtolower($search['query']));
                    $similarity = $this->calculateSimilarity($queryWords, $searchWords);

                    if ($similarity > 0.4 && $search['query'] !== $query) {
                        $similarSearches[] = [
                            'query' => $search['query'],
                            'similarity_score' => $similarity,
                            'search_count' => $search['count'],
                            'trend_direction' => $this->getTrendDirection($search['query']),
                        ];
                    }
                }

                usort($similarSearches, fn ($a, $b) => $b['similarity_score'] <=> $a['similarity_score']);

                return array_slice($similarSearches, 0, 8);
            });
        } catch (\Exception $e) {
            \Log::warning('Similar searches generation failed: '.$e->getMessage());

            return [];
        }
    }

    /**
     * Handle getTrendingSearches functionality with proper error handling.
     */
    private function getTrendingSearches(array $context): array
    {
        try {
            $cacheKey = self::CACHE_PREFIX.'trending_'.md5(serialize($context));

            return Cache::remember($cacheKey, self::CACHE_TTL, function () {
                $analyticsService = app(SearchAnalyticsService::class);
                $popularSearches = $analyticsService->getPopularSearchesForDateRange(20);

                $trendingSearches = [];
                foreach ($popularSearches as $search) {
                    $trendDirection = $this->getTrendDirection($search['query']);

                    if ($trendDirection === 'rising') {
                        $trendingSearches[] = [
                            'query' => $search['query'],
                            'search_count' => $search['count'],
                            'trend_direction' => $trendDirection,
                            'growth_rate' => $this->getGrowthRate($search['query']),
                            'category' => $this->getSearchCategory($search['query']),
                        ];
                    }
                }

                usort($trendingSearches, fn ($a, $b) => $b['growth_rate'] <=> $a['growth_rate']);

                return array_slice($trendingSearches, 0, 6);
            });
        } catch (\Exception $e) {
            \Log::warning('Trending searches generation failed: '.$e->getMessage());

            return [];
        }
    }

    /**
     * Handle getPersonalizedRecommendations functionality with proper error handling.
     */
    private function getPersonalizedRecommendations(string $query, array $context): array
    {
        try {
            $userId = $context['user_id'] ?? null;

            if (! $userId) {
                return [];
            }

            $cacheKey = self::CACHE_PREFIX.'personalized_'.$userId.'_'.md5($query);

            return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($query, $userId) {
                $userPreferences = $this->getUserPreferences($userId);
                $userHistory = $this->getUserSearchHistory($userId);
                $userPurchases = $this->getUserPurchaseHistory($userId);

                return [
                    'based_on_history' => $this->getRecommendationsBasedOnHistory($query, $userHistory),
                    'based_on_preferences' => $this->getRecommendationsBasedOnPreferences($query, $userPreferences),
                    'based_on_purchases' => $this->getRecommendationsBasedOnPurchases($query, $userPurchases),
                    'collaborative_filtering' => $this->getCollaborativeFilteringRecommendations($query, $userId),
                ];
            });
        } catch (\Exception $e) {
            \Log::warning('Personalized recommendations generation failed: '.$e->getMessage());

            return [];
        }
    }

    /**
     * Handle getCrossSellSuggestions functionality with proper error handling.
     */
    private function getCrossSellSuggestions(string $query, array $context): array
    {
        try {
            $autocompleteService = app(AutocompleteService::class);
            $results = $autocompleteService->search($query, 10, ['products']);

            $crossSellSuggestions = [];
            foreach ($results as $result) {
                if ($result['type'] === 'product') {
                    $relatedProducts = $this->getCrossSellProducts($result['id']);
                    $crossSellSuggestions = array_merge($crossSellSuggestions, $relatedProducts);
                }
            }

            return array_slice($crossSellSuggestions, 0, 8);
        } catch (\Exception $e) {
            \Log::warning('Cross-sell suggestions generation failed: '.$e->getMessage());

            return [];
        }
    }

    /**
     * Handle getUpsellSuggestions functionality with proper error handling.
     */
    private function getUpsellSuggestions(string $query, array $context): array
    {
        try {
            $autocompleteService = app(AutocompleteService::class);
            $results = $autocompleteService->search($query, 10, ['products']);

            $upsellSuggestions = [];
            foreach ($results as $result) {
                if ($result['type'] === 'product') {
                    $upsellProducts = $this->getUpsellProducts($result['id']);
                    $upsellSuggestions = array_merge($upsellSuggestions, $upsellProducts);
                }
            }

            return array_slice($upsellSuggestions, 0, 6);
        } catch (\Exception $e) {
            \Log::warning('Upsell suggestions generation failed: '.$e->getMessage());

            return [];
        }
    }

    /**
     * Handle getCategoryRecommendations functionality with proper error handling.
     */
    private function getCategoryRecommendations(string $query, array $context): array
    {
        try {
            $autocompleteService = app(AutocompleteService::class);
            $results = $autocompleteService->search($query, 20, ['categories']);

            $categoryRecommendations = [];
            foreach ($results as $result) {
                if ($result['type'] === 'category') {
                    $categoryRecommendations[] = [
                        'id' => $result['id'],
                        'title' => $result['title'],
                        'url' => $result['url'],
                        'products_count' => $result['products_count'] ?? 0,
                        'relevance_score' => $result['relevance_score'] ?? 0,
                        'subcategories' => $this->getSubcategories($result['id']),
                    ];
                }
            }

            return array_slice($categoryRecommendations, 0, 6);
        } catch (\Exception $e) {
            \Log::warning('Category recommendations generation failed: '.$e->getMessage());

            return [];
        }
    }

    /**
     * Handle getBrandRecommendations functionality with proper error handling.
     */
    private function getBrandRecommendations(string $query, array $context): array
    {
        try {
            $autocompleteService = app(AutocompleteService::class);
            $results = $autocompleteService->search($query, 15, ['brands']);

            $brandRecommendations = [];
            foreach ($results as $result) {
                if ($result['type'] === 'brand') {
                    $brandRecommendations[] = [
                        'id' => $result['id'],
                        'title' => $result['title'],
                        'url' => $result['url'],
                        'products_count' => $result['products_count'] ?? 0,
                        'relevance_score' => $result['relevance_score'] ?? 0,
                        'country' => $result['subtitle'] ?? null,
                        'popularity_score' => $this->getBrandPopularityScore($result['id']),
                    ];
                }
            }

            return array_slice($brandRecommendations, 0, 8);
        } catch (\Exception $e) {
            \Log::warning('Brand recommendations generation failed: '.$e->getMessage());

            return [];
        }
    }

    /**
     * Handle getPriceRecommendations functionality with proper error handling.
     */
    private function getPriceRecommendations(string $query, array $context): array
    {
        try {
            $autocompleteService = app(AutocompleteService::class);
            $results = $autocompleteService->search($query, 50, ['products']);

            $prices = [];
            foreach ($results as $result) {
                if ($result['type'] === 'product' && isset($result['price'])) {
                    $prices[] = (float) $result['price'];
                }
            }

            if (empty($prices)) {
                return [];
            }

            sort($prices);
            $count = count($prices);

            return [
                'price_range' => [
                    'min' => $prices[0],
                    'max' => $prices[$count - 1],
                    'average' => array_sum($prices) / $count,
                    'median' => $prices[intval($count / 2)],
                ],
                'price_segments' => [
                    'budget' => ['min' => $prices[0], 'max' => $prices[intval($count * 0.33)]],
                    'mid_range' => ['min' => $prices[intval($count * 0.33)], 'max' => $prices[intval($count * 0.66)]],
                    'premium' => ['min' => $prices[intval($count * 0.66)], 'max' => $prices[$count - 1]],
                ],
                'best_value' => $this->getBestValueProducts($results),
                'price_alerts' => $this->getPriceAlerts($query, $prices),
            ];
        } catch (\Exception $e) {
            \Log::warning('Price recommendations generation failed: '.$e->getMessage());

            return [];
        }
    }

    /**
     * Handle getSeasonalRecommendations functionality with proper error handling.
     */
    private function getSeasonalRecommendations(string $query, array $context): array
    {
        try {
            $currentSeason = $this->getCurrentSeason();
            $seasonalKeywords = $this->getSeasonalKeywords($currentSeason);

            $seasonalRecommendations = [];
            foreach ($seasonalKeywords as $keyword) {
                if (stripos($query, $keyword) !== false) {
                    $seasonalRecommendations[] = [
                        'keyword' => $keyword,
                        'season' => $currentSeason,
                        'relevance_score' => $this->calculateKeywordRelevance($query, $keyword),
                        'trending_products' => $this->getTrendingProductsForKeyword($keyword),
                        'seasonal_offers' => $this->getSeasonalOffers($keyword),
                    ];
                }
            }

            return $seasonalRecommendations;
        } catch (\Exception $e) {
            \Log::warning('Seasonal recommendations generation failed: '.$e->getMessage());

            return [];
        }
    }

    /**
     * Handle getSimilarityReason functionality with proper error handling.
     */
    private function getSimilarityReason(string $query, array $result): string
    {
        try {
            $queryWords = explode(' ', strtolower($query));
            $titleWords = explode(' ', strtolower($result['title']));

            $commonWords = array_intersect($queryWords, $titleWords);

            if (! empty($commonWords)) {
                return 'Similar keywords: '.implode(', ', $commonWords);
            }

            if (isset($result['subtitle']) && stripos($result['subtitle'], $query) !== false) {
                return 'Brand match';
            }

            if (isset($result['description']) && stripos($result['description'], $query) !== false) {
                return 'Description match';
            }

            return 'Category similarity';
        } catch (\Exception $e) {
            return 'General similarity';
        }
    }

    /**
     * Handle calculateSimilarity functionality with proper error handling.
     */
    private function calculateSimilarity(array $words1, array $words2): float
    {
        $intersection = array_intersect($words1, $words2);
        $union = array_unique(array_merge($words1, $words2));

        if (empty($union)) {
            return 0.0;
        }

        return count($intersection) / count($union);
    }

    /**
     * Handle getTrendDirection functionality with proper error handling.
     */
    private function getTrendDirection(string $query): string
    {
        try {
            // This would typically analyze trend data
            $trends = ['rising', 'falling', 'stable'];

            return $trends[array_rand($trends)];
        } catch (\Exception $e) {
            return 'stable';
        }
    }

    /**
     * Handle getGrowthRate functionality with proper error handling.
     */
    private function getGrowthRate(string $query): float
    {
        try {
            // This would typically calculate growth rate
            return rand(10, 100) / 10; // Random growth rate between 1% and 10%
        } catch (\Exception $e) {
            return 0.0;
        }
    }

    /**
     * Handle getSearchCategory functionality with proper error handling.
     */
    private function getSearchCategory(string $query): string
    {
        try {
            $query = strtolower($query);

            if (preg_match('/\b(shirt|dress|pants|shoes|jacket)\b/', $query)) {
                return 'clothing';
            } elseif (preg_match('/\b(phone|laptop|tablet|computer)\b/', $query)) {
                return 'electronics';
            } elseif (preg_match('/\b(book|novel|magazine)\b/', $query)) {
                return 'books';
            } elseif (preg_match('/\b(furniture|chair|table|sofa)\b/', $query)) {
                return 'home';
            }

            return 'general';
        } catch (\Exception $e) {
            return 'general';
        }
    }

    /**
     * Handle getUserPreferences functionality with proper error handling.
     */
    private function getUserPreferences(int $userId): array
    {
        try {
            $cacheKey = "user_preferences_{$userId}";

            return Cache::get($cacheKey, [
                'preferred_categories' => ['electronics' => 40, 'clothing' => 30, 'books' => 20, 'home' => 10],
                'preferred_brands' => ['apple' => 30, 'samsung' => 25, 'nike' => 20, 'adidas' => 15],
                'price_range' => ['min' => 10, 'max' => 500],
                'preferred_sellers' => ['amazon' => 40, 'ebay' => 30, 'local' => 30],
            ]);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Handle getUserSearchHistory functionality with proper error handling.
     */
    private function getUserSearchHistory(int $userId): array
    {
        try {
            $cacheKey = "user_search_history_{$userId}";

            return Cache::get($cacheKey, []);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Handle getUserPurchaseHistory functionality with proper error handling.
     */
    private function getUserPurchaseHistory(int $userId): array
    {
        try {
            $cacheKey = "user_purchase_history_{$userId}";

            return Cache::get($cacheKey, []);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Handle getRecommendationsBasedOnHistory functionality with proper error handling.
     */
    private function getRecommendationsBasedOnHistory(string $query, array $userHistory): array
    {
        try {
            // This would typically analyze user's search history
            return [
                'frequently_searched' => ['laptop', 'phone', 'headphones'],
                'recent_searches' => ['wireless mouse', 'keyboard', 'monitor'],
                'saved_searches' => ['gaming laptop', 'mechanical keyboard'],
            ];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Handle getRecommendationsBasedOnPreferences functionality with proper error handling.
     */
    private function getRecommendationsBasedOnPreferences(string $query, array $userPreferences): array
    {
        try {
            // This would typically use user preferences to generate recommendations
            return [
                'preferred_categories' => $userPreferences['preferred_categories'] ?? [],
                'preferred_brands' => $userPreferences['preferred_brands'] ?? [],
                'price_range' => $userPreferences['price_range'] ?? [],
            ];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Handle getRecommendationsBasedOnPurchases functionality with proper error handling.
     */
    private function getRecommendationsBasedOnPurchases(string $query, array $userPurchases): array
    {
        try {
            // This would typically analyze user's purchase history
            return [
                'frequently_purchased' => ['electronics', 'clothing'],
                'recent_purchases' => ['laptop', 'mouse', 'keyboard'],
                'purchase_patterns' => ['weekend_shopper', 'brand_loyal'],
            ];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Handle getCollaborativeFilteringRecommendations functionality with proper error handling.
     */
    private function getCollaborativeFilteringRecommendations(string $query, int $userId): array
    {
        try {
            // This would typically use collaborative filtering algorithms
            return [
                'users_who_searched_this_also_searched' => ['wireless mouse', 'keyboard', 'monitor'],
                'users_who_bought_this_also_bought' => ['laptop stand', 'mouse pad', 'cable'],
                'similar_users_recommendations' => ['gaming chair', 'desk lamp', 'webcam'],
            ];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Handle getCrossSellProducts functionality with proper error handling.
     */
    private function getCrossSellProducts(int $productId): array
    {
        try {
            // This would typically get cross-sell products from database
            return [
                [
                    'id' => $productId + 1,
                    'title' => 'Related Product 1',
                    'price' => '29.99',
                    'image' => null,
                    'url' => '#',
                    'cross_sell_reason' => 'Frequently bought together',
                ],
                [
                    'id' => $productId + 2,
                    'title' => 'Related Product 2',
                    'price' => '19.99',
                    'image' => null,
                    'url' => '#',
                    'cross_sell_reason' => 'Customers also viewed',
                ],
            ];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Handle getUpsellProducts functionality with proper error handling.
     */
    private function getUpsellProducts(int $productId): array
    {
        try {
            // This would typically get upsell products from database
            return [
                [
                    'id' => $productId + 10,
                    'title' => 'Premium Version',
                    'price' => '99.99',
                    'image' => null,
                    'url' => '#',
                    'upsell_reason' => 'Premium features',
                ],
                [
                    'id' => $productId + 11,
                    'title' => 'Professional Version',
                    'price' => '149.99',
                    'image' => null,
                    'url' => '#',
                    'upsell_reason' => 'Professional grade',
                ],
            ];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Handle getSubcategories functionality with proper error handling.
     */
    private function getSubcategories(int $categoryId): array
    {
        try {
            // This would typically get subcategories from database
            return [
                ['id' => $categoryId + 1, 'title' => 'Subcategory 1', 'url' => '#'],
                ['id' => $categoryId + 2, 'title' => 'Subcategory 2', 'url' => '#'],
            ];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Handle getBrandPopularityScore functionality with proper error handling.
     */
    private function getBrandPopularityScore(int $brandId): float
    {
        try {
            // This would typically calculate brand popularity score
            return rand(70, 100) / 10; // Random score between 7.0 and 10.0
        } catch (\Exception $e) {
            return 0.0;
        }
    }

    /**
     * Handle getBestValueProducts functionality with proper error handling.
     */
    private function getBestValueProducts(array $results): array
    {
        try {
            $bestValueProducts = [];

            foreach ($results as $result) {
                if ($result['type'] === 'product' && isset($result['price'])) {
                    $price = (float) $result['price'];
                    $rating = $result['average_rating'] ?? 0;

                    if ($rating > 4.0 && $price < 100) {
                        $bestValueProducts[] = [
                            'id' => $result['id'],
                            'title' => $result['title'],
                            'price' => $result['formatted_price'],
                            'rating' => $rating,
                            'value_score' => $rating / ($price / 10),
                        ];
                    }
                }
            }

            usort($bestValueProducts, fn ($a, $b) => $b['value_score'] <=> $a['value_score']);

            return array_slice($bestValueProducts, 0, 5);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Handle getPriceAlerts functionality with proper error handling.
     */
    private function getPriceAlerts(string $query, array $prices): array
    {
        try {
            $minPrice = min($prices);
            $maxPrice = max($prices);
            $avgPrice = array_sum($prices) / count($prices);

            return [
                'price_drop_alert' => $minPrice < $avgPrice * 0.8,
                'price_increase_alert' => $maxPrice > $avgPrice * 1.2,
                'best_price_ever' => $minPrice < $avgPrice * 0.7,
                'price_trend' => $this->getPriceTrend($prices),
            ];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Handle getCurrentSeason functionality with proper error handling.
     */
    private function getCurrentSeason(): string
    {
        $month = (int) date('n');

        return match ($month) {
            12, 1, 2 => 'winter',
            3, 4, 5 => 'spring',
            6, 7, 8 => 'summer',
            9, 10, 11 => 'autumn',
            default => 'unknown',
        };
    }

    /**
     * Handle getSeasonalKeywords functionality with proper error handling.
     */
    private function getSeasonalKeywords(string $season): array
    {
        return match ($season) {
            'winter' => ['coat', 'jacket', 'boots', 'scarf', 'gloves', 'hat'],
            'spring' => ['dress', 'shoes', 'jacket', 'umbrella', 'raincoat'],
            'summer' => ['shorts', 't-shirt', 'sandals', 'hat', 'sunglasses'],
            'autumn' => ['sweater', 'jeans', 'boots', 'jacket', 'coat'],
            default => [],
        };
    }

    /**
     * Handle calculateKeywordRelevance functionality with proper error handling.
     */
    private function calculateKeywordRelevance(string $query, string $keyword): float
    {
        try {
            $query = strtolower($query);
            $keyword = strtolower($keyword);

            if (strpos($query, $keyword) !== false) {
                return 1.0;
            }

            $queryWords = explode(' ', $query);
            $keywordWords = explode(' ', $keyword);

            $commonWords = array_intersect($queryWords, $keywordWords);

            return count($commonWords) / max(count($keywordWords), 1);
        } catch (\Exception $e) {
            return 0.0;
        }
    }

    /**
     * Handle getTrendingProductsForKeyword functionality with proper error handling.
     */
    private function getTrendingProductsForKeyword(string $keyword): array
    {
        try {
            // This would typically get trending products for the keyword
            return [
                [
                    'id' => 1,
                    'title' => "Trending {$keyword} Product 1",
                    'price' => '49.99',
                    'trend_score' => 0.9,
                ],
                [
                    'id' => 2,
                    'title' => "Trending {$keyword} Product 2",
                    'price' => '79.99',
                    'trend_score' => 0.8,
                ],
            ];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Handle getSeasonalOffers functionality with proper error handling.
     */
    private function getSeasonalOffers(string $keyword): array
    {
        try {
            // This would typically get seasonal offers for the keyword
            return [
                [
                    'title' => "Seasonal {$keyword} Sale",
                    'discount' => '20%',
                    'valid_until' => now()->addDays(30)->format('Y-m-d'),
                ],
                [
                    'title' => "Limited Time {$keyword} Offer",
                    'discount' => '15%',
                    'valid_until' => now()->addDays(7)->format('Y-m-d'),
                ],
            ];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Handle getPriceTrend functionality with proper error handling.
     */
    private function getPriceTrend(array $prices): string
    {
        try {
            if (count($prices) < 2) {
                return 'stable';
            }

            $firstHalf = array_slice($prices, 0, intval(count($prices) / 2));
            $secondHalf = array_slice($prices, intval(count($prices) / 2));

            $firstAvg = array_sum($firstHalf) / count($firstHalf);
            $secondAvg = array_sum($secondHalf) / count($secondHalf);

            if ($secondAvg > $firstAvg * 1.1) {
                return 'rising';
            } elseif ($secondAvg < $firstAvg * 0.9) {
                return 'falling';
            }

            return 'stable';
        } catch (\Exception $e) {
            return 'stable';
        }
    }
}
