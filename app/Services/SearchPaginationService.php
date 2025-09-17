<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;

/**
 * SearchPaginationService
 * 
 * Service class containing SearchPaginationService business logic, external integrations, and complex operations with proper error handling and logging.
 * 
 */
final class SearchPaginationService
{
    private const CACHE_PREFIX = 'search_pagination:';
    private const DEFAULT_PAGE_SIZE = 20;
    private const MAX_PAGE_SIZE = 100;
    private const CACHE_TTL = 1800; // 30 minutes

    /**
     * Handle paginateSearchResults functionality with proper error handling.
     * @param array $results
     * @param string $query
     * @param int $page
     * @param int $pageSize
     * @param array $filters
     * @return array
     */
    public function paginateSearchResults(array $results, string $query, int $page = 1, int $pageSize = self::DEFAULT_PAGE_SIZE, array $filters = []): array
    {
        try {
            $pageSize = min($pageSize, self::MAX_PAGE_SIZE);
            $page = max($page, 1);
            
            // Apply filters if provided
            if (!empty($filters)) {
                $results = $this->applyFilters($results, $filters);
            }
            
            $totalResults = count($results);
            $totalPages = ceil($totalResults / $pageSize);
            $offset = ($page - 1) * $pageSize;
            
            $paginatedResults = array_slice($results, $offset, $pageSize);
            
            return [
                'data' => $paginatedResults,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $pageSize,
                    'total' => $totalResults,
                    'total_pages' => $totalPages,
                    'has_next_page' => $page < $totalPages,
                    'has_prev_page' => $page > 1,
                    'next_page' => $page < $totalPages ? $page + 1 : null,
                    'prev_page' => $page > 1 ? $page - 1 : null,
                ],
                'filters' => $filters,
                'query' => $query,
            ];
        } catch (\Exception $e) {
            \Log::warning('Search pagination failed: ' . $e->getMessage());
            return [
                'data' => [],
                'pagination' => [
                    'current_page' => 1,
                    'per_page' => $pageSize,
                    'total' => 0,
                    'total_pages' => 0,
                    'has_next_page' => false,
                    'has_prev_page' => false,
                    'next_page' => null,
                    'prev_page' => null,
                ],
                'filters' => $filters,
                'query' => $query,
            ];
        }
    }

    /**
     * Handle getInfiniteScrollData functionality with proper error handling.
     * @param string $query
     * @param int $page
     * @param int $pageSize
     * @param array $filters
     * @param array $types
     * @return array
     */
    public function getInfiniteScrollData(string $query, int $page = 1, int $pageSize = self::DEFAULT_PAGE_SIZE, array $filters = [], array $types = []): array
    {
        try {
            $cacheKey = $this->generateCacheKey($query, $page, $pageSize, $filters, $types);
            
            return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($query, $page, $pageSize, $filters, $types) {
                $autocompleteService = app(AutocompleteService::class);
                
                // Get all results for the query
                $allResults = $autocompleteService->search($query, 1000, $types); // Large limit for pagination
                
                // Paginate the results
                $paginatedData = $this->paginateSearchResults($allResults, $query, $page, $pageSize, $filters);
                
                // Add infinite scroll specific data
                $paginatedData['infinite_scroll'] = [
                    'has_more' => $paginatedData['pagination']['has_next_page'],
                    'next_page_url' => $this->generateNextPageUrl($query, $page + 1, $pageSize, $filters, $types),
                    'load_more_text' => $this->getLoadMoreText($paginatedData['pagination']),
                ];
                
                return $paginatedData;
            });
        } catch (\Exception $e) {
            \Log::warning('Infinite scroll data failed: ' . $e->getMessage());
            return [
                'data' => [],
                'pagination' => [
                    'current_page' => 1,
                    'per_page' => $pageSize,
                    'total' => 0,
                    'total_pages' => 0,
                    'has_next_page' => false,
                    'has_prev_page' => false,
                    'next_page' => null,
                    'prev_page' => null,
                ],
                'infinite_scroll' => [
                    'has_more' => false,
                    'next_page_url' => null,
                    'load_more_text' => 'No more results',
                ],
                'filters' => $filters,
                'query' => $query,
            ];
        }
    }

    /**
     * Handle applyFilters functionality with proper error handling.
     * @param array $results
     * @param array $filters
     * @return array
     */
    private function applyFilters(array $results, array $filters): array
    {
        try {
            foreach ($filters as $filterType => $filterValue) {
                if (empty($filterValue)) {
                    continue;
                }
                
                $results = match ($filterType) {
                    'type' => $this->filterByType($results, $filterValue),
                    'price_min' => $this->filterByPriceMin($results, (float) $filterValue),
                    'price_max' => $this->filterByPriceMax($results, (float) $filterValue),
                    'in_stock' => $this->filterByInStock($results, (bool) $filterValue),
                    'featured' => $this->filterByFeatured($results, (bool) $filterValue),
                    'category' => $this->filterByCategory($results, $filterValue),
                    'brand' => $this->filterByBrand($results, $filterValue),
                    'rating_min' => $this->filterByRatingMin($results, (float) $filterValue),
                    'date_from' => $this->filterByDateFrom($results, $filterValue),
                    'date_to' => $this->filterByDateTo($results, $filterValue),
                    default => $results,
                };
            }
            
            return $results;
        } catch (\Exception $e) {
            \Log::warning('Filter application failed: ' . $e->getMessage());
            return $results;
        }
    }

    /**
     * Handle filterByType functionality with proper error handling.
     * @param array $results
     * @param string|array $type
     * @return array
     */
    private function filterByType(array $results, $type): array
    {
        if (is_array($type)) {
            return array_filter($results, fn($result) => in_array($result['type'] ?? '', $type));
        }
        
        return array_filter($results, fn($result) => ($result['type'] ?? '') === $type);
    }

    /**
     * Handle filterByPriceMin functionality with proper error handling.
     * @param array $results
     * @param float $minPrice
     * @return array
     */
    private function filterByPriceMin(array $results, float $minPrice): array
    {
        return array_filter($results, function ($result) use ($minPrice) {
            $price = $result['price'] ?? $result['formatted_price'] ?? 0;
            if (is_string($price)) {
                $price = (float) preg_replace('/[^\d.,]/', '', $price);
            }
            return $price >= $minPrice;
        });
    }

    /**
     * Handle filterByPriceMax functionality with proper error handling.
     * @param array $results
     * @param float $maxPrice
     * @return array
     */
    private function filterByPriceMax(array $results, float $maxPrice): array
    {
        return array_filter($results, function ($result) use ($maxPrice) {
            $price = $result['price'] ?? $result['formatted_price'] ?? 0;
            if (is_string($price)) {
                $price = (float) preg_replace('/[^\d.,]/', '', $price);
            }
            return $price <= $maxPrice;
        });
    }

    /**
     * Handle filterByInStock functionality with proper error handling.
     * @param array $results
     * @param bool $inStock
     * @return array
     */
    private function filterByInStock(array $results, bool $inStock): array
    {
        return array_filter($results, fn($result) => ($result['in_stock'] ?? false) === $inStock);
    }

    /**
     * Handle filterByFeatured functionality with proper error handling.
     * @param array $results
     * @param bool $featured
     * @return array
     */
    private function filterByFeatured(array $results, bool $featured): array
    {
        return array_filter($results, fn($result) => ($result['is_featured'] ?? false) === $featured);
    }

    /**
     * Handle filterByCategory functionality with proper error handling.
     * @param array $results
     * @param string|array $category
     * @return array
     */
    private function filterByCategory(array $results, $category): array
    {
        if (is_array($category)) {
            return array_filter($results, function ($result) use ($category) {
                $resultCategory = $result['category'] ?? $result['category_name'] ?? '';
                return in_array($resultCategory, $category);
            });
        }
        
        return array_filter($results, function ($result) use ($category) {
            $resultCategory = $result['category'] ?? $result['category_name'] ?? '';
            return $resultCategory === $category;
        });
    }

    /**
     * Handle filterByBrand functionality with proper error handling.
     * @param array $results
     * @param string|array $brand
     * @return array
     */
    private function filterByBrand(array $results, $brand): array
    {
        if (is_array($brand)) {
            return array_filter($results, function ($result) use ($brand) {
                $resultBrand = $result['brand'] ?? $result['brand_name'] ?? '';
                return in_array($resultBrand, $brand);
            });
        }
        
        return array_filter($results, function ($result) use ($brand) {
            $resultBrand = $result['brand'] ?? $result['brand_name'] ?? '';
            return $resultBrand === $brand;
        });
    }

    /**
     * Handle filterByRatingMin functionality with proper error handling.
     * @param array $results
     * @param float $minRating
     * @return array
     */
    private function filterByRatingMin(array $results, float $minRating): array
    {
        return array_filter($results, function ($result) use ($minRating) {
            $rating = $result['average_rating'] ?? $result['rating'] ?? 0;
            return $rating >= $minRating;
        });
    }

    /**
     * Handle filterByDateFrom functionality with proper error handling.
     * @param array $results
     * @param string $dateFrom
     * @return array
     */
    private function filterByDateFrom(array $results, string $dateFrom): array
    {
        $dateFrom = \Carbon\Carbon::parse($dateFrom);
        
        return array_filter($results, function ($result) use ($dateFrom) {
            $date = $result['created_at'] ?? $result['date'] ?? null;
            if (!$date) {
                return false;
            }
            
            $resultDate = \Carbon\Carbon::parse($date);
            return $resultDate->gte($dateFrom);
        });
    }

    /**
     * Handle filterByDateTo functionality with proper error handling.
     * @param array $results
     * @param string $dateTo
     * @return array
     */
    private function filterByDateTo(array $results, string $dateTo): array
    {
        $dateTo = \Carbon\Carbon::parse($dateTo);
        
        return array_filter($results, function ($result) use ($dateTo) {
            $date = $result['created_at'] ?? $result['date'] ?? null;
            if (!$date) {
                return false;
            }
            
            $resultDate = \Carbon\Carbon::parse($date);
            return $resultDate->lte($dateTo);
        });
    }

    /**
     * Handle generateCacheKey functionality with proper error handling.
     * @param string $query
     * @param int $page
     * @param int $pageSize
     * @param array $filters
     * @param array $types
     * @return string
     */
    private function generateCacheKey(string $query, int $page, int $pageSize, array $filters, array $types): string
    {
        $keyData = [
            'query' => $query,
            'page' => $page,
            'page_size' => $pageSize,
            'filters' => $filters,
            'types' => $types,
        ];
        
        return self::CACHE_PREFIX . md5(serialize($keyData));
    }

    /**
     * Handle generateNextPageUrl functionality with proper error handling.
     * @param string $query
     * @param int $nextPage
     * @param int $pageSize
     * @param array $filters
     * @param array $types
     * @return string
     */
    private function generateNextPageUrl(string $query, int $nextPage, int $pageSize, array $filters, array $types): string
    {
        $params = [
            'q' => $query,
            'page' => $nextPage,
            'per_page' => $pageSize,
        ];
        
        if (!empty($filters)) {
            $params['filters'] = $filters;
        }
        
        if (!empty($types)) {
            $params['types'] = $types;
        }
        
        return route('api.autocomplete.search') . '?' . http_build_query($params);
    }

    /**
     * Handle getLoadMoreText functionality with proper error handling.
     * @param array $pagination
     * @return string
     */
    private function getLoadMoreText(array $pagination): string
    {
        if (!$pagination['has_next_page']) {
            return __('frontend.no_more_results');
        }
        
        $remaining = $pagination['total'] - ($pagination['current_page'] * $pagination['per_page']);
        
        if ($remaining <= 0) {
            return __('frontend.no_more_results');
        }
        
        return __('frontend.load_more_results', ['count' => min($remaining, $pagination['per_page'])]);
    }

    /**
     * Handle getAvailableFilters functionality with proper error handling.
     * @param array $results
     * @return array
     */
    public function getAvailableFilters(array $results): array
    {
        try {
            $filters = [
                'types' => [],
                'price_ranges' => [],
                'categories' => [],
                'brands' => [],
                'ratings' => [],
            ];
            
            foreach ($results as $result) {
                // Collect types
                if (isset($result['type'])) {
                    $filters['types'][$result['type']] = ($filters['types'][$result['type']] ?? 0) + 1;
                }
                
                // Collect price ranges
                if (isset($result['price']) && is_numeric($result['price'])) {
                    $price = (float) $result['price'];
                    $range = $this->getPriceRange($price);
                    $filters['price_ranges'][$range] = ($filters['price_ranges'][$range] ?? 0) + 1;
                }
                
                // Collect categories
                if (isset($result['category']) || isset($result['category_name'])) {
                    $category = $result['category'] ?? $result['category_name'];
                    $filters['categories'][$category] = ($filters['categories'][$category] ?? 0) + 1;
                }
                
                // Collect brands
                if (isset($result['brand']) || isset($result['brand_name'])) {
                    $brand = $result['brand'] ?? $result['brand_name'];
                    $filters['brands'][$brand] = ($filters['brands'][$brand] ?? 0) + 1;
                }
                
                // Collect ratings
                if (isset($result['average_rating']) && is_numeric($result['average_rating'])) {
                    $rating = (float) $result['average_rating'];
                    $ratingRange = $this->getRatingRange($rating);
                    $filters['ratings'][$ratingRange] = ($filters['ratings'][$ratingRange] ?? 0) + 1;
                }
            }
            
            return $filters;
        } catch (\Exception $e) {
            \Log::warning('Available filters generation failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Handle getPriceRange functionality with proper error handling.
     * @param float $price
     * @return string
     */
    private function getPriceRange(float $price): string
    {
        if ($price < 10) {
            return '0-10';
        } elseif ($price < 50) {
            return '10-50';
        } elseif ($price < 100) {
            return '50-100';
        } elseif ($price < 500) {
            return '100-500';
        } else {
            return '500+';
        }
    }

    /**
     * Handle getRatingRange functionality with proper error handling.
     * @param float $rating
     * @return string
     */
    private function getRatingRange(float $rating): string
    {
        if ($rating >= 4.5) {
            return '4.5+';
        } elseif ($rating >= 4.0) {
            return '4.0-4.5';
        } elseif ($rating >= 3.5) {
            return '3.5-4.0';
        } elseif ($rating >= 3.0) {
            return '3.0-3.5';
        } else {
            return 'Below 3.0';
        }
    }
}
