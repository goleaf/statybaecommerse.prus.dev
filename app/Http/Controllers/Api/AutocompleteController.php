<?php

declare (strict_types=1);
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AutocompleteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
/**
 * AutocompleteController
 * 
 * HTTP controller handling AutocompleteController related web requests, responses, and business logic with proper validation and error handling.
 * 
 */
final class AutocompleteController extends Controller
{
    /**
     * Initialize the class instance with required dependencies.
     * @param AutocompleteService $autocompleteService
     */
    public function __construct(private readonly AutocompleteService $autocompleteService)
    {
    }
    /**
     * Handle search functionality with proper error handling.
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate(['q' => 'required|string|min:2|max:255', 'limit' => 'integer|min:1|max:50', 'types' => 'array', 'types.*' => 'string|in:products,categories,brands,collections,attributes']);
            $query = $validated['q'];
            $limit = (int) ($validated['limit'] ?? 10);
            $types = $validated['types'] ?? [];
            // Add to recent searches
            $this->autocompleteService->addToRecentSearches($query);
            $results = $this->autocompleteService->search($query, $limit, $types);
            return response()->json(['success' => true, 'data' => $results, 'meta' => ['query' => $query, 'total' => count($results), 'limit' => $limit, 'types' => $types]]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Search failed', 'error' => $e->getMessage()], 500);
        }
    }
    /**
     * Handle products functionality with proper error handling.
     * @param Request $request
     * @return JsonResponse
     */
    public function products(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate(['q' => 'required|string|min:2|max:255', 'limit' => 'integer|min:1|max:50']);
            $query = $validated['q'];
            $limit = $validated['limit'] ?? 10;
            $results = $this->autocompleteService->searchProducts($query, $limit);
            return response()->json(['success' => true, 'data' => $results, 'meta' => ['query' => $query, 'total' => count($results), 'limit' => $limit, 'type' => 'products']]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Product search failed', 'error' => $e->getMessage()], 500);
        }
    }
    /**
     * Handle categories functionality with proper error handling.
     * @param Request $request
     * @return JsonResponse
     */
    public function categories(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate(['q' => 'required|string|min:2|max:255', 'limit' => 'integer|min:1|max:50']);
            $query = $validated['q'];
            $limit = $validated['limit'] ?? 10;
            $results = $this->autocompleteService->searchCategories($query, $limit);
            return response()->json(['success' => true, 'data' => $results, 'meta' => ['query' => $query, 'total' => count($results), 'limit' => $limit, 'type' => 'categories']]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Category search failed', 'error' => $e->getMessage()], 500);
        }
    }
    /**
     * Handle brands functionality with proper error handling.
     * @param Request $request
     * @return JsonResponse
     */
    public function brands(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate(['q' => 'required|string|min:2|max:255', 'limit' => 'integer|min:1|max:50']);
            $query = $validated['q'];
            $limit = $validated['limit'] ?? 10;
            $results = $this->autocompleteService->searchBrands($query, $limit);
            return response()->json(['success' => true, 'data' => $results, 'meta' => ['query' => $query, 'total' => count($results), 'limit' => $limit, 'type' => 'brands']]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Brand search failed', 'error' => $e->getMessage()], 500);
        }
    }
    /**
     * Handle collections functionality with proper error handling.
     * @param Request $request
     * @return JsonResponse
     */
    public function collections(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate(['q' => 'required|string|min:2|max:255', 'limit' => 'integer|min:1|max:50']);
            $query = $validated['q'];
            $limit = $validated['limit'] ?? 10;
            $results = $this->autocompleteService->searchCollections($query, $limit);
            return response()->json(['success' => true, 'data' => $results, 'meta' => ['query' => $query, 'total' => count($results), 'limit' => $limit, 'type' => 'collections']]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Collection search failed', 'error' => $e->getMessage()], 500);
        }
    }
    /**
     * Handle attributes functionality with proper error handling.
     * @param Request $request
     * @return JsonResponse
     */
    public function attributes(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate(['q' => 'required|string|min:2|max:255', 'limit' => 'integer|min:1|max:50']);
            $query = $validated['q'];
            $limit = $validated['limit'] ?? 10;
            $results = $this->autocompleteService->searchAttributes($query, $limit);
            return response()->json(['success' => true, 'data' => $results, 'meta' => ['query' => $query, 'total' => count($results), 'limit' => $limit, 'type' => 'attributes']]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Attribute search failed', 'error' => $e->getMessage()], 500);
        }
    }
    /**
     * Handle popular functionality with proper error handling.
     * @param Request $request
     * @return JsonResponse
     */
    public function popular(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate(['limit' => 'integer|min:1|max:20']);
            $limit = $validated['limit'] ?? 10;
            $results = $this->autocompleteService->getPopularSuggestions($limit);
            return response()->json(['success' => true, 'data' => $results, 'meta' => ['total' => count($results), 'limit' => $limit, 'type' => 'popular']]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to get popular suggestions', 'error' => $e->getMessage()], 500);
        }
    }
    /**
     * Handle recent functionality with proper error handling.
     * @param Request $request
     * @return JsonResponse
     */
    public function recent(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate(['limit' => 'integer|min:1|max:10']);
            $limit = $validated['limit'] ?? 5;
            $results = $this->autocompleteService->getRecentSuggestions($limit);
            return response()->json(['success' => true, 'data' => $results, 'meta' => ['total' => count($results), 'limit' => $limit, 'type' => 'recent']]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to get recent suggestions', 'error' => $e->getMessage()], 500);
        }
    }
    /**
     * Handle clearRecent functionality with proper error handling.
     * @param Request $request
     * @return JsonResponse
     */
    public function clearRecent(Request $request): JsonResponse
    {
        try {
            $this->autocompleteService->clearRecentSearches();
            return response()->json(['success' => true, 'message' => 'Recent searches cleared successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to clear recent searches', 'error' => $e->getMessage()], 500);
        }
    }
    /**
     * Handle suggestions functionality with proper error handling.
     * @param Request $request
     * @return JsonResponse
     */
    public function suggestions(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate(['limit' => 'integer|min:1|max:20']);
            $limit = $validated['limit'] ?? 10;
            $popular = $this->autocompleteService->getPopularSuggestions((int) ceil($limit * 0.6));
            $recent = $this->autocompleteService->getRecentSuggestions((int) ceil($limit * 0.4));
            $results = array_merge($recent, $popular);
            $results = array_slice($results, 0, $limit);
            return response()->json(['success' => true, 'data' => $results, 'meta' => ['total' => count($results), 'limit' => $limit, 'type' => 'suggestions', 'popular_count' => count($popular), 'recent_count' => count($recent)]]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to get suggestions', 'error' => $e->getMessage()], 500);
        }
    }
    /**
     * Handle fuzzySearch functionality with proper error handling.
     * @param Request $request
     * @return JsonResponse
     */
    public function fuzzySearch(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate(['q' => 'required|string|min:2|max:255', 'limit' => 'integer|min:1|max:50', 'types' => 'array', 'types.*' => 'string|in:products,categories,brands,collections,attributes']);
            $query = $validated['q'];
            $limit = $validated['limit'] ?? 10;
            $types = $validated['types'] ?? [];
            // Add to recent searches
            $this->autocompleteService->addToRecentSearches($query);
            $results = $this->autocompleteService->searchWithFuzzy($query, $limit, $types);
            return response()->json(['success' => true, 'data' => $results, 'meta' => ['query' => $query, 'total' => count($results), 'limit' => $limit, 'types' => $types, 'fuzzy' => true]]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Fuzzy search failed', 'error' => $e->getMessage()], 500);
        }
    }
    /**
     * Handle personalized functionality with proper error handling.
     * @param Request $request
     * @return JsonResponse
     */
    public function personalized(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate(['limit' => 'integer|min:1|max:20']);
            $limit = $validated['limit'] ?? 5;
            $userId = auth()->id();
            if (!$userId) {
                return response()->json(['success' => false, 'message' => 'Authentication required'], 401);
            }
            $suggestions = $this->autocompleteService->getPersonalizedSuggestions($userId, $limit);
            return response()->json(['success' => true, 'data' => $suggestions, 'meta' => ['total' => count($suggestions), 'limit' => $limit, 'user_id' => $userId]]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to get personalized suggestions', 'error' => $e->getMessage()], 500);
        }
    }
    /**
     * Handle customers functionality with proper error handling.
     * @param Request $request
     * @return JsonResponse
     */
    public function customers(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate(['q' => 'required|string|min:2|max:255', 'limit' => 'integer|min:1|max:50']);
            $query = $validated['q'];
            $limit = $validated['limit'] ?? 10;
            $results = $this->autocompleteService->searchCustomers($query, $limit);
            return response()->json(['success' => true, 'data' => $results, 'meta' => ['query' => $query, 'total' => count($results), 'limit' => $limit, 'type' => 'customers']]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Customer search failed', 'error' => $e->getMessage()], 500);
        }
    }
    /**
     * Handle addresses functionality with proper error handling.
     * @param Request $request
     * @return JsonResponse
     */
    public function addresses(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate(['q' => 'required|string|min:2|max:255', 'limit' => 'integer|min:1|max:50']);
            $query = $validated['q'];
            $limit = $validated['limit'] ?? 10;
            $results = $this->autocompleteService->searchAddresses($query, $limit);
            return response()->json(['success' => true, 'data' => $results, 'meta' => ['query' => $query, 'total' => count($results), 'limit' => $limit, 'type' => 'addresses']]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Address search failed', 'error' => $e->getMessage()], 500);
        }
    }
    /**
     * Handle locations functionality with proper error handling.
     * @param Request $request
     * @return JsonResponse
     */
    public function locations(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate(['q' => 'required|string|min:2|max:255', 'limit' => 'integer|min:1|max:50']);
            $query = $validated['q'];
            $limit = $validated['limit'] ?? 10;
            $results = $this->autocompleteService->searchLocations($query, $limit);
            return response()->json(['success' => true, 'data' => $results, 'meta' => ['query' => $query, 'total' => count($results), 'limit' => $limit, 'type' => 'locations']]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Location search failed', 'error' => $e->getMessage()], 500);
        }
    }
    /**
     * Handle countries functionality with proper error handling.
     * @param Request $request
     * @return JsonResponse
     */
    public function countries(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate(['q' => 'required|string|min:2|max:255', 'limit' => 'integer|min:1|max:50']);
            $query = $validated['q'];
            $limit = $validated['limit'] ?? 10;
            $results = $this->autocompleteService->searchCountries($query, $limit);
            return response()->json(['success' => true, 'data' => $results, 'meta' => ['query' => $query, 'total' => count($results), 'limit' => $limit, 'type' => 'countries']]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Country search failed', 'error' => $e->getMessage()], 500);
        }
    }
    /**
     * Handle cities functionality with proper error handling.
     * @param Request $request
     * @return JsonResponse
     */
    public function cities(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate(['q' => 'required|string|min:2|max:255', 'limit' => 'integer|min:1|max:50']);
            $query = $validated['q'];
            $limit = $validated['limit'] ?? 10;
            $results = $this->autocompleteService->searchCities($query, $limit);
            return response()->json(['success' => true, 'data' => $results, 'meta' => ['query' => $query, 'total' => count($results), 'limit' => $limit, 'type' => 'cities']]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'City search failed', 'error' => $e->getMessage()], 500);
        }
    }
    /**
     * Handle orders functionality with proper error handling.
     * @param Request $request
     * @return JsonResponse
     */
    public function orders(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate(['q' => 'required|string|min:2|max:255', 'limit' => 'integer|min:1|max:50']);
            $query = $validated['q'];
            $limit = $validated['limit'] ?? 10;
            $results = $this->autocompleteService->searchOrders($query, $limit);
            return response()->json(['success' => true, 'data' => $results, 'meta' => ['query' => $query, 'total' => count($results), 'limit' => $limit, 'type' => 'orders']]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Order search failed', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle paginatedSearch functionality with proper error handling.
     * @param Request $request
     * @return JsonResponse
     */
    public function paginatedSearch(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'q' => 'required|string|min:2|max:255',
                'page' => 'integer|min:1',
                'per_page' => 'integer|min:1|max:100',
                'filters' => 'array',
                'types' => 'array',
                'types.*' => 'string|in:products,categories,brands,collections,attributes,locations,countries,cities,orders,customers,addresses',
            ]);

            $query = $validated['q'];
            $page = $validated['page'] ?? 1;
            $perPage = $validated['per_page'] ?? 20;
            $filters = $validated['filters'] ?? [];
            $types = $validated['types'] ?? [];

            $paginationService = app(\App\Services\SearchPaginationService::class);
            $results = $paginationService->getInfiniteScrollData($query, $page, $perPage, $filters, $types);

            return response()->json([
                'success' => true,
                'data' => $results['data'],
                'pagination' => $results['pagination'],
                'infinite_scroll' => $results['infinite_scroll'],
                'filters' => $results['filters'],
                'query' => $results['query'],
            ]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Paginated search failed', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle exportSearch functionality with proper error handling.
     * @param Request $request
     * @return JsonResponse
     */
    public function exportSearch(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'q' => 'required|string|min:2|max:255',
                'format' => 'string|in:json,csv,xml,xlsx',
                'types' => 'array',
                'types.*' => 'string|in:products,categories,brands,collections,attributes,locations,countries,cities,orders,customers,addresses',
                'options' => 'array',
            ]);

            $query = $validated['q'];
            $format = $validated['format'] ?? 'json';
            $types = $validated['types'] ?? [];
            $options = $validated['options'] ?? [];

            // Get search results
            $results = $this->autocompleteService->search($query, 1000, $types);

            // Export results
            $exportService = app(\App\Services\SearchExportService::class);
            $exportResult = $exportService->exportSearchResults($results, $query, $format, $options);

            return response()->json($exportResult);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Export failed', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle downloadExport functionality with proper error handling.
     * @param string $exportId
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function downloadExport(string $exportId)
    {
        try {
            $exportService = app(\App\Services\SearchExportService::class);
            $exportData = $exportService->getExportData($exportId);

            if (!$exportData) {
                return response()->json(['success' => false, 'message' => 'Export not found or expired'], 404);
            }

            $filename = "search_results_{$exportData['query']}_{$exportData['format']}_" . now()->format('Y-m-d_H-i-s');
            $mimeType = $this->getMimeType($exportData['format']);

            return response()->streamDownload(function () use ($exportData) {
                echo $exportData['data'];
            }, $filename, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'attachment',
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Download failed', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle shareSearch functionality with proper error handling.
     * @param Request $request
     * @return JsonResponse
     */
    public function shareSearch(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'q' => 'required|string|min:2|max:255',
                'types' => 'array',
                'types.*' => 'string|in:products,categories,brands,collections,attributes,locations,countries,cities,orders,customers,addresses',
                'options' => 'array',
            ]);

            $query = $validated['q'];
            $types = $validated['types'] ?? [];
            $options = $validated['options'] ?? [];

            // Get search results
            $results = $this->autocompleteService->search($query, 100, $types);

            // Generate shareable link
            $exportService = app(\App\Services\SearchExportService::class);
            $shareResult = $exportService->generateShareableLink($results, $query, $options);

            return response()->json($shareResult);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Share failed', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle viewSharedSearch functionality with proper error handling.
     * @param string $shareId
     * @return JsonResponse
     */
    public function viewSharedSearch(string $shareId): JsonResponse
    {
        try {
            $exportService = app(\App\Services\SearchExportService::class);
            $shareData = $exportService->getSharedSearch($shareId);

            if (!$shareData) {
                return response()->json(['success' => false, 'message' => 'Shared search not found or expired'], 404);
            }

            return response()->json([
                'success' => true,
                'share_data' => $shareData,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'View shared search failed', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle getAvailableFilters functionality with proper error handling.
     * @param Request $request
     * @return JsonResponse
     */
    public function getAvailableFilters(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'q' => 'required|string|min:2|max:255',
                'types' => 'array',
                'types.*' => 'string|in:products,categories,brands,collections,attributes,locations,countries,cities,orders,customers,addresses',
            ]);

            $query = $validated['q'];
            $types = $validated['types'] ?? [];

            // Get search results
            $results = $this->autocompleteService->search($query, 1000, $types);

            // Get available filters
            $paginationService = app(\App\Services\SearchPaginationService::class);
            $filters = $paginationService->getAvailableFilters($results);

            return response()->json([
                'success' => true,
                'filters' => $filters,
                'query' => $query,
            ]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Get filters failed', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle getMimeType functionality with proper error handling.
     * @param string $format
     * @return string
     */
    private function getMimeType(string $format): string
    {
        return match ($format) {
            'json' => 'application/json',
            'csv' => 'text/csv',
            'xml' => 'application/xml',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            default => 'application/octet-stream',
        };
    }
}