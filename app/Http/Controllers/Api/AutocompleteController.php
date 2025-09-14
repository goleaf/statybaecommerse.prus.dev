<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AutocompleteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

final class AutocompleteController extends Controller
{
    public function __construct(
        private readonly AutocompleteService $autocompleteService
    ) {}

    /**
     * General autocomplete search across all entities
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'q' => 'required|string|min:2|max:255',
                'limit' => 'integer|min:1|max:50',
                'types' => 'array',
                'types.*' => 'string|in:products,categories,brands,collections,attributes',
            ]);

            $query = $validated['q'];
            $limit = $validated['limit'] ?? 10;
            $types = $validated['types'] ?? [];

            // Add to recent searches
            $this->autocompleteService->addToRecentSearches($query);

            $results = $this->autocompleteService->search($query, $limit, $types);

            return response()->json([
                'success' => true,
                'data' => $results,
                'meta' => [
                    'query' => $query,
                    'total' => count($results),
                    'limit' => $limit,
                    'types' => $types,
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Search products only
     */
    public function products(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'q' => 'required|string|min:2|max:255',
                'limit' => 'integer|min:1|max:50',
            ]);

            $query = $validated['q'];
            $limit = $validated['limit'] ?? 10;

            $results = $this->autocompleteService->searchProducts($query, $limit);

            return response()->json([
                'success' => true,
                'data' => $results,
                'meta' => [
                    'query' => $query,
                    'total' => count($results),
                    'limit' => $limit,
                    'type' => 'products',
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product search failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Search categories only
     */
    public function categories(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'q' => 'required|string|min:2|max:255',
                'limit' => 'integer|min:1|max:50',
            ]);

            $query = $validated['q'];
            $limit = $validated['limit'] ?? 10;

            $results = $this->autocompleteService->searchCategories($query, $limit);

            return response()->json([
                'success' => true,
                'data' => $results,
                'meta' => [
                    'query' => $query,
                    'total' => count($results),
                    'limit' => $limit,
                    'type' => 'categories',
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Category search failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Search brands only
     */
    public function brands(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'q' => 'required|string|min:2|max:255',
                'limit' => 'integer|min:1|max:50',
            ]);

            $query = $validated['q'];
            $limit = $validated['limit'] ?? 10;

            $results = $this->autocompleteService->searchBrands($query, $limit);

            return response()->json([
                'success' => true,
                'data' => $results,
                'meta' => [
                    'query' => $query,
                    'total' => count($results),
                    'limit' => $limit,
                    'type' => 'brands',
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Brand search failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Search collections only
     */
    public function collections(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'q' => 'required|string|min:2|max:255',
                'limit' => 'integer|min:1|max:50',
            ]);

            $query = $validated['q'];
            $limit = $validated['limit'] ?? 10;

            $results = $this->autocompleteService->searchCollections($query, $limit);

            return response()->json([
                'success' => true,
                'data' => $results,
                'meta' => [
                    'query' => $query,
                    'total' => count($results),
                    'limit' => $limit,
                    'type' => 'collections',
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Collection search failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Search attributes and attribute values
     */
    public function attributes(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'q' => 'required|string|min:2|max:255',
                'limit' => 'integer|min:1|max:50',
            ]);

            $query = $validated['q'];
            $limit = $validated['limit'] ?? 10;

            $results = $this->autocompleteService->searchAttributes($query, $limit);

            return response()->json([
                'success' => true,
                'data' => $results,
                'meta' => [
                    'query' => $query,
                    'total' => count($results),
                    'limit' => $limit,
                    'type' => 'attributes',
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Attribute search failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get popular search suggestions
     */
    public function popular(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'limit' => 'integer|min:1|max:20',
            ]);

            $limit = $validated['limit'] ?? 10;

            $results = $this->autocompleteService->getPopularSuggestions($limit);

            return response()->json([
                'success' => true,
                'data' => $results,
                'meta' => [
                    'total' => count($results),
                    'limit' => $limit,
                    'type' => 'popular',
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get popular suggestions',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get recent search suggestions
     */
    public function recent(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'limit' => 'integer|min:1|max:10',
            ]);

            $limit = $validated['limit'] ?? 5;

            $results = $this->autocompleteService->getRecentSuggestions($limit);

            return response()->json([
                'success' => true,
                'data' => $results,
                'meta' => [
                    'total' => count($results),
                    'limit' => $limit,
                    'type' => 'recent',
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get recent suggestions',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clear recent searches
     */
    public function clearRecent(Request $request): JsonResponse
    {
        try {
            $this->autocompleteService->clearRecentSearches();

            return response()->json([
                'success' => true,
                'message' => 'Recent searches cleared successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear recent searches',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get search suggestions for empty query (popular + recent)
     */
    public function suggestions(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'limit' => 'integer|min:1|max:20',
            ]);

            $limit = $validated['limit'] ?? 10;

            $popular = $this->autocompleteService->getPopularSuggestions((int) ceil($limit * 0.6));
            $recent = $this->autocompleteService->getRecentSuggestions((int) ceil($limit * 0.4));

            $results = array_merge($recent, $popular);
            $results = array_slice($results, 0, $limit);

            return response()->json([
                'success' => true,
                'data' => $results,
                'meta' => [
                    'total' => count($results),
                    'limit' => $limit,
                    'type' => 'suggestions',
                    'popular_count' => count($popular),
                    'recent_count' => count($recent),
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get suggestions',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
