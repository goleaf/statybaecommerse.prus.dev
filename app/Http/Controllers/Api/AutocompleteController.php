<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AutocompleteService;
use App\Services\SearchAnalyticsService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Log;
use OpenApi\Attributes as OA;

/**
 * AutocompleteController
 *
 * HTTP controller handling AutocompleteController related web requests, responses, and business logic with proper validation and error handling.
 */
#[OA\Tag(name: 'Autocomplete', description: 'Autocomplete search, insights, and export endpoints.')]
final class AutocompleteController extends Controller
{
    /**
     * Initialize the class instance with required dependencies.
     */
    public function __construct(private readonly AutocompleteService $autocompleteService) {}

    /**
     * Handle search functionality with proper error handling.
     */
    #[OA\Get(
        path: '/api/autocomplete/search',
        summary: 'Search across autocomplete resources.',
        tags: ['Autocomplete'],
        parameters: [
            new OA\QueryParameter(
                name: 'q',
                description: 'Search query string.',
                required: true,
                schema: new OA\Schema(type: 'string', minLength: 2, maxLength: 255),
            ),
            new OA\QueryParameter(
                name: 'limit',
                description: 'Maximum number of results to return.',
                required: false,
                schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 50, default: 10),
            ),
            new OA\QueryParameter(
                name: 'types',
                description: 'Filter the result set to specific resource types.',
                required: false,
                schema: new OA\Schema(
                    type: 'array',
                    items: new OA\Items(type: 'string', enum: ['products', 'categories', 'brands', 'collections', 'attributes']),
                ),
                style: 'form',
                explode: true,
            ),
        ],
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/AutocompleteResults'),
            new OA\Response(response: 422, ref: '#/components/responses/AutocompleteValidationError'),
            new OA\Response(response: 500, ref: '#/components/responses/AutocompleteFailure'),
        ]
    )]
    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'model_class' => ['required', 'string'],
            'search_field' => ['nullable', 'string'],
            'search_query' => ['required', 'string'],
            'value_field' => ['nullable', 'string'],
            'label_field' => ['nullable', 'string'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

            return response()->json(['success' => true, 'data' => $results, 'meta' => ['query' => $query, 'total' => count($results), 'limit' => $limit, 'types' => $types]]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Search failed', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle products functionality with proper error handling.
     */
    #[OA\Get(
        path: '/api/autocomplete/products',
        summary: 'Search for products via autocomplete.',
        tags: ['Autocomplete'],
        parameters: [
            new OA\QueryParameter(
                name: 'q',
                description: 'Product search query.',
                required: true,
                schema: new OA\Schema(type: 'string', minLength: 2, maxLength: 255),
            ),
            new OA\QueryParameter(
                name: 'limit',
                description: 'Maximum number of suggestions to return.',
                required: false,
                schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 50, default: 10),
            ),
        ],
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/AutocompleteResults'),
            new OA\Response(response: 422, ref: '#/components/responses/AutocompleteValidationError'),
            new OA\Response(response: 500, ref: '#/components/responses/AutocompleteFailure'),
        ]
    )]
    public function products(Request $request): JsonResponse
    {
        try {
            $modelClass = $validated['model_class'];
            $searchField = $validated['search_field'] ?? $validated['label_field'] ?? 'name';
            $searchQuery = $validated['search_query'];
            $valueField = $validated['value_field'] ?? 'id';
            $labelField = $validated['label_field'] ?? 'name';
            $limit = $validated['limit'] ?? 10;

            return response()->json(['success' => true, 'data' => $results, 'meta' => ['query' => $query, 'total' => count($results), 'limit' => $limit, 'type' => 'products']]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Product search failed', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle categories functionality with proper error handling.
     */
    #[OA\Get(
        path: '/api/autocomplete/categories',
        summary: 'Search for categories via autocomplete.',
        tags: ['Autocomplete'],
        parameters: [
            new OA\QueryParameter(
                name: 'q',
                description: 'Category search query.',
                required: true,
                schema: new OA\Schema(type: 'string', minLength: 2, maxLength: 255),
            ),
            new OA\QueryParameter(
                name: 'limit',
                description: 'Maximum number of suggestions to return.',
                required: false,
                schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 50, default: 10),
            ),
        ],
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/AutocompleteResults'),
            new OA\Response(response: 422, ref: '#/components/responses/AutocompleteValidationError'),
            new OA\Response(response: 500, ref: '#/components/responses/AutocompleteFailure'),
        ]
    )]
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
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Category search failed', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle brands functionality with proper error handling.
     */
    #[OA\Get(
        path: '/api/autocomplete/brands',
        summary: 'Search for brands via autocomplete.',
        tags: ['Autocomplete'],
        parameters: [
            new OA\QueryParameter(
                name: 'q',
                description: 'Brand search query.',
                required: true,
                schema: new OA\Schema(type: 'string', minLength: 2, maxLength: 255),
            ),
            new OA\QueryParameter(
                name: 'limit',
                description: 'Maximum number of suggestions to return.',
                required: false,
                schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 50, default: 10),
            ),
        ],
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/AutocompleteResults'),
            new OA\Response(response: 422, ref: '#/components/responses/AutocompleteValidationError'),
            new OA\Response(response: 500, ref: '#/components/responses/AutocompleteFailure'),
        ]
    )]
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
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Brand search failed', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle collections functionality with proper error handling.
     */
    #[OA\Get(
        path: '/api/autocomplete/collections',
        summary: 'Search for collections via autocomplete.',
        tags: ['Autocomplete'],
        parameters: [
            new OA\QueryParameter(
                name: 'q',
                description: 'Collection search query.',
                required: true,
                schema: new OA\Schema(type: 'string', minLength: 2, maxLength: 255),
            ),
            new OA\QueryParameter(
                name: 'limit',
                description: 'Maximum number of suggestions to return.',
                required: false,
                schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 50, default: 10),
            ),
        ],
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/AutocompleteResults'),
            new OA\Response(response: 422, ref: '#/components/responses/AutocompleteValidationError'),
            new OA\Response(response: 500, ref: '#/components/responses/AutocompleteFailure'),
        ]
    )]
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
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Collection search failed', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle attributes functionality with proper error handling.
     */
    #[OA\Get(
        path: '/api/autocomplete/attributes',
        summary: 'Search for product attributes via autocomplete.',
        tags: ['Autocomplete'],
        parameters: [
            new OA\QueryParameter(
                name: 'q',
                description: 'Attribute search query.',
                required: true,
                schema: new OA\Schema(type: 'string', minLength: 2, maxLength: 255),
            ),
            new OA\QueryParameter(
                name: 'limit',
                description: 'Maximum number of suggestions to return.',
                required: false,
                schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 50, default: 10),
            ),
        ],
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/AutocompleteResults'),
            new OA\Response(response: 422, ref: '#/components/responses/AutocompleteValidationError'),
            new OA\Response(response: 500, ref: '#/components/responses/AutocompleteFailure'),
        ]
    )]
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
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Attribute search failed', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle popular functionality with proper error handling.
     */
    #[OA\Get(
        path: '/api/autocomplete/popular',
        summary: 'Fetch popular autocomplete suggestions.',
        tags: ['Autocomplete'],
        parameters: [
            new OA\QueryParameter(
                name: 'limit',
                description: 'Maximum number of entries to return.',
                required: false,
                schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 20, default: 10),
            ),
        ],
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/AutocompleteSuggestions'),
            new OA\Response(response: 422, ref: '#/components/responses/AutocompleteValidationError'),
            new OA\Response(response: 500, ref: '#/components/responses/AutocompleteFailure'),
        ]
    )]
    public function popular(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate(['limit' => 'integer|min:1|max:20']);
            $limit = $validated['limit'] ?? 10;
            $results = $this->autocompleteService->getPopularSuggestions($limit);

            return response()->json(['success' => true, 'data' => $results, 'meta' => ['total' => count($results), 'limit' => $limit, 'type' => 'popular']]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to get popular suggestions', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle recent functionality with proper error handling.
     */
    #[OA\Get(
        path: '/api/autocomplete/recent',
        summary: 'Fetch recently used autocomplete suggestions.',
        tags: ['Autocomplete'],
        parameters: [
            new OA\QueryParameter(
                name: 'limit',
                description: 'Maximum number of entries to return.',
                required: false,
                schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 10, default: 5),
            ),
        ],
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/AutocompleteSuggestions'),
            new OA\Response(response: 422, ref: '#/components/responses/AutocompleteValidationError'),
            new OA\Response(response: 500, ref: '#/components/responses/AutocompleteFailure'),
        ]
    )]
    public function recent(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate(['limit' => 'integer|min:1|max:10']);
            $limit = $validated['limit'] ?? 5;
            $results = $this->autocompleteService->getRecentSuggestions($limit);

            return response()->json(['success' => true, 'data' => $results, 'meta' => ['total' => count($results), 'limit' => $limit, 'type' => 'recent']]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to get recent suggestions', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle clearRecent functionality with proper error handling.
     */
    #[OA\Post(
        path: '/api/autocomplete/recent/clear',
        summary: 'Clear stored recent autocomplete suggestions.',
        tags: ['Autocomplete'],
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/SuccessMessage'),
            new OA\Response(response: 500, ref: '#/components/responses/AutocompleteFailure'),
        ]
    )]
    public function clearRecent(Request $request): JsonResponse
    {
        try {
            $this->autocompleteService->clearRecentSearches();

            return response()->json(['success' => true, 'message' => 'Recent searches cleared successfully']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to clear recent searches', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle suggestions functionality with proper error handling.
     */
    #[OA\Get(
        path: '/api/autocomplete/suggestions',
        summary: 'Return blended popular and recent autocomplete suggestions.',
        tags: ['Autocomplete'],
        parameters: [
            new OA\QueryParameter(
                name: 'limit',
                description: 'Maximum number of entries to return.',
                required: false,
                schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 20, default: 10),
            ),
        ],
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/AutocompleteSuggestions'),
            new OA\Response(response: 422, ref: '#/components/responses/AutocompleteValidationError'),
            new OA\Response(response: 500, ref: '#/components/responses/AutocompleteFailure'),
        ]
    )]
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
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to get suggestions', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle fuzzySearch functionality with proper error handling.
     */
    #[OA\Get(
        path: '/api/autocomplete/fuzzy-search',
        summary: 'Perform a fuzzy autocomplete search across resources.',
        tags: ['Autocomplete'],
        parameters: [
            new OA\QueryParameter(
                name: 'q',
                description: 'Search query string.',
                required: true,
                schema: new OA\Schema(type: 'string', minLength: 2, maxLength: 255),
            ),
            new OA\QueryParameter(
                name: 'limit',
                description: 'Maximum number of results to return.',
                required: false,
                schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 50, default: 10),
            ),
            new OA\QueryParameter(
                name: 'types',
                description: 'Restrict search to specific entity types.',
                required: false,
                schema: new OA\Schema(
                    type: 'array',
                    items: new OA\Items(type: 'string', enum: ['products', 'categories', 'brands', 'collections', 'attributes']),
                ),
                style: 'form',
                explode: true,
            ),
        ],
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/AutocompleteResults'),
            new OA\Response(response: 422, ref: '#/components/responses/AutocompleteValidationError'),
            new OA\Response(response: 500, ref: '#/components/responses/AutocompleteFailure'),
        ]
    )]
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
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Fuzzy search failed', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle personalized functionality with proper error handling.
     */
    #[OA\Get(
        path: '/api/autocomplete/personalized',
        summary: 'Return personalized autocomplete suggestions for the authenticated user.',
        tags: ['Autocomplete'],
        security: [
            ['SanctumToken' => []],
        ],
        parameters: [
            new OA\QueryParameter(
                name: 'limit',
                description: 'Maximum number of entries to return.',
                required: false,
                schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 20, default: 5),
            ),
        ],
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/AutocompletePersonalized'),
            new OA\Response(response: 401, ref: '#/components/responses/AuthenticationError'),
            new OA\Response(response: 422, ref: '#/components/responses/AutocompleteValidationError'),
            new OA\Response(response: 500, ref: '#/components/responses/AutocompleteFailure'),
        ]
    )]
    public function personalized(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate(['limit' => 'integer|min:1|max:20']);
            $limit = $validated['limit'] ?? 5;
            $userId = auth()->id();
            if (! $userId) {
                return response()->json(['success' => false, 'message' => 'Authentication required'], 401);
            }
            $suggestions = $this->autocompleteService->getPersonalizedSuggestions($userId, $limit);

            return response()->json(['success' => true, 'data' => $suggestions, 'meta' => ['total' => count($suggestions), 'limit' => $limit, 'user_id' => $userId]]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to get personalized suggestions', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle customers functionality with proper error handling.
     */
    #[OA\Get(
        path: '/api/autocomplete/customers',
        summary: 'Search for customers via autocomplete.',
        tags: ['Autocomplete'],
        parameters: [
            new OA\QueryParameter(
                name: 'q',
                description: 'Customer search query.',
                required: true,
                schema: new OA\Schema(type: 'string', minLength: 2, maxLength: 255),
            ),
            new OA\QueryParameter(
                name: 'limit',
                description: 'Maximum number of suggestions to return.',
                required: false,
                schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 50, default: 10),
            ),
        ],
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/AutocompleteResults'),
            new OA\Response(response: 422, ref: '#/components/responses/AutocompleteValidationError'),
            new OA\Response(response: 500, ref: '#/components/responses/AutocompleteFailure'),
        ]
    )]
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
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Customer search failed', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle addresses functionality with proper error handling.
     */
    #[OA\Get(
        path: '/api/autocomplete/addresses',
        summary: 'Search for addresses via autocomplete.',
        tags: ['Autocomplete'],
        parameters: [
            new OA\QueryParameter(
                name: 'q',
                description: 'Address search query.',
                required: true,
                schema: new OA\Schema(type: 'string', minLength: 2, maxLength: 255),
            ),
            new OA\QueryParameter(
                name: 'limit',
                description: 'Maximum number of suggestions to return.',
                required: false,
                schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 50, default: 10),
            ),
        ],
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/AutocompleteResults'),
            new OA\Response(response: 422, ref: '#/components/responses/AutocompleteValidationError'),
            new OA\Response(response: 500, ref: '#/components/responses/AutocompleteFailure'),
        ]
    )]
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
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Address search failed', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle locations functionality with proper error handling.
     */
    #[OA\Get(
        path: '/api/autocomplete/locations',
        summary: 'Search for locations via autocomplete.',
        tags: ['Autocomplete'],
        parameters: [
            new OA\QueryParameter(
                name: 'q',
                description: 'Location search query.',
                required: true,
                schema: new OA\Schema(type: 'string', minLength: 2, maxLength: 255),
            ),
            new OA\QueryParameter(
                name: 'limit',
                description: 'Maximum number of suggestions to return.',
                required: false,
                schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 50, default: 10),
            ),
        ],
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/AutocompleteResults'),
            new OA\Response(response: 422, ref: '#/components/responses/AutocompleteValidationError'),
            new OA\Response(response: 500, ref: '#/components/responses/AutocompleteFailure'),
        ]
    )]
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
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Location search failed', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle countries functionality with proper error handling.
     */
    #[OA\Get(
        path: '/api/autocomplete/countries',
        summary: 'Search for countries via autocomplete.',
        tags: ['Autocomplete'],
        parameters: [
            new OA\QueryParameter(
                name: 'q',
                description: 'Country search query.',
                required: true,
                schema: new OA\Schema(type: 'string', minLength: 2, maxLength: 255),
            ),
            new OA\QueryParameter(
                name: 'limit',
                description: 'Maximum number of suggestions to return.',
                required: false,
                schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 50, default: 10),
            ),
        ],
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/AutocompleteResults'),
            new OA\Response(response: 422, ref: '#/components/responses/AutocompleteValidationError'),
            new OA\Response(response: 500, ref: '#/components/responses/AutocompleteFailure'),
        ]
    )]
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
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Country search failed', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle cities functionality with proper error handling.
     */
    #[OA\Get(
        path: '/api/autocomplete/cities',
        summary: 'Search for cities via autocomplete.',
        tags: ['Autocomplete'],
        parameters: [
            new OA\QueryParameter(
                name: 'q',
                description: 'City search query.',
                required: true,
                schema: new OA\Schema(type: 'string', minLength: 2, maxLength: 255),
            ),
            new OA\QueryParameter(
                name: 'limit',
                description: 'Maximum number of suggestions to return.',
                required: false,
                schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 50, default: 10),
            ),
        ],
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/AutocompleteResults'),
            new OA\Response(response: 422, ref: '#/components/responses/AutocompleteValidationError'),
            new OA\Response(response: 500, ref: '#/components/responses/AutocompleteFailure'),
        ]
    )]
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
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'City search failed', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle orders functionality with proper error handling.
     */
    #[OA\Get(
        path: '/api/autocomplete/orders',
        summary: 'Search for orders via autocomplete.',
        tags: ['Autocomplete'],
        parameters: [
            new OA\QueryParameter(
                name: 'q',
                description: 'Order search query.',
                required: true,
                schema: new OA\Schema(type: 'string', minLength: 2, maxLength: 255),
            ),
            new OA\QueryParameter(
                name: 'limit',
                description: 'Maximum number of suggestions to return.',
                required: false,
                schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 50, default: 10),
            ),
        ],
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/AutocompleteResults'),
            new OA\Response(response: 422, ref: '#/components/responses/AutocompleteValidationError'),
            new OA\Response(response: 500, ref: '#/components/responses/AutocompleteFailure'),
        ]
    )]
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
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Order search failed', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle paginatedSearch functionality with proper error handling.
     */
    #[OA\Get(
        path: '/api/autocomplete/paginated-search',
        summary: 'Search across resources with paginated results.',
        tags: ['Autocomplete'],
        parameters: [
            new OA\QueryParameter(
                name: 'q',
                description: 'Search query string.',
                required: true,
                schema: new OA\Schema(type: 'string', minLength: 2, maxLength: 255),
            ),
            new OA\QueryParameter(
                name: 'page',
                description: 'Page number to return.',
                required: false,
                schema: new OA\Schema(type: 'integer', minimum: 1, default: 1),
            ),
            new OA\QueryParameter(
                name: 'per_page',
                description: 'Number of results per page.',
                required: false,
                schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 100, default: 20),
            ),
            new OA\QueryParameter(
                name: 'filters',
                description: 'Optional deep object filters applied to the search.',
                required: false,
                style: 'deepObject',
                explode: true,
                schema: new OA\Schema(type: 'object'),
            ),
            new OA\QueryParameter(
                name: 'types',
                description: 'Restrict search to specific entity types.',
                required: false,
                schema: new OA\Schema(
                    type: 'array',
                    items: new OA\Items(type: 'string', enum: ['products', 'categories', 'brands', 'collections', 'attributes', 'locations', 'countries', 'cities', 'orders', 'customers', 'addresses']),
                ),
                style: 'form',
                explode: true,
            ),
        ],
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/AutocompletePaginatedResults'),
            new OA\Response(response: 422, ref: '#/components/responses/AutocompleteValidationError'),
            new OA\Response(response: 500, ref: '#/components/responses/AutocompleteFailure'),
        ]
    )]
    public function paginatedSearch(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'q'        => 'required|string|min:2|max:255',
                'page'     => 'integer|min:1',
                'per_page' => 'integer|min:1|max:100',
                'filters'  => 'array',
                'types'    => 'array',
                'types.*'  => 'string|in:products,categories,brands,collections,attributes,locations,countries,cities,orders,customers,addresses',
            ]);

            $query = $validated['q'];
            $page = $validated['page'] ?? 1;
            $perPage = $validated['per_page'] ?? 20;
            $filters = $validated['filters'] ?? [];
            $types = $validated['types'] ?? [];

            $paginationService = app(\App\Services\SearchPaginationService::class);
            $results = $paginationService->getInfiniteScrollData($query, $page, $perPage, $filters, $types);

            return response()->json([
                'success'         => true,
                'data'            => $results['data'],
                'pagination'      => $results['pagination'],
                'infinite_scroll' => $results['infinite_scroll'],
                'filters'         => $results['filters'],
                'query'           => $results['query'],
            ]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Paginated search failed', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle exportSearch functionality with proper error handling.
     */
    #[OA\Post(
        path: '/api/autocomplete/export',
        summary: 'Export autocomplete results to a downloadable artifact.',
        tags: ['Autocomplete'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                required: ['q'],
                properties: [
                    new OA\Property(property: 'q', type: 'string', minLength: 2, maxLength: 255),
                    new OA\Property(property: 'format', type: 'string', enum: ['json', 'csv', 'xml', 'xlsx'], default: 'json'),
                    new OA\Property(
                        property: 'types',
                        type: 'array',
                        items: new OA\Items(type: 'string'),
                    ),
                    new OA\Property(property: 'options', type: 'object', nullable: true),
                ],
            ),
        ),
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/AutocompleteExport'),
            new OA\Response(response: 422, ref: '#/components/responses/AutocompleteValidationError'),
            new OA\Response(response: 500, ref: '#/components/responses/AutocompleteFailure'),
        ]
    )]
    public function exportSearch(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'q'       => 'required|string|min:2|max:255',
                'format'  => 'string|in:json,csv,xml,xlsx',
                'types'   => 'array',
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
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Export failed', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle downloadExport functionality with proper error handling.
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    #[OA\Get(
        path: '/api/autocomplete/export/{exportId}',
        summary: 'Download a previously generated autocomplete export.',
        tags: ['Autocomplete'],
        parameters: [
            new OA\PathParameter(
                name: 'exportId',
                description: 'Identifier of the export artifact.',
                required: true,
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Export payload stream.',
                content: [
                    new OA\MediaType(mediaType: 'application/octet-stream'),
                ],
            ),
            new OA\Response(response: 404, ref: '#/components/responses/AutocompleteExportNotFound'),
            new OA\Response(response: 500, ref: '#/components/responses/AutocompleteFailure'),
        ]
    )]
    public function downloadExport(string $exportId)
    {
        try {
            $exportService = app(\App\Services\SearchExportService::class);
            $exportData = $exportService->getExportData($exportId);

            if (! $exportData) {
                return response()->json(['success' => false, 'message' => 'Export not found or expired'], 404);
            }

            $filename = "search_results_{$exportData['query']}_{$exportData['format']}_" . now()->format('Y-m-d_H-i-s');
            $mimeType = $this->getMimeType($exportData['format']);

            return response()->streamDownload(function () use ($exportData): void {
                echo $exportData['data'];
            }, $filename, [
                'Content-Type'        => $mimeType,
                'Content-Disposition' => 'attachment',
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Download failed', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle shareSearch functionality with proper error handling.
     */
    #[OA\Post(
        path: '/api/autocomplete/share',
        summary: 'Generate a shareable link for autocomplete results.',
        tags: ['Autocomplete'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                required: ['q'],
                properties: [
                    new OA\Property(property: 'q', type: 'string', minLength: 2, maxLength: 255),
                    new OA\Property(
                        property: 'types',
                        type: 'array',
                        items: new OA\Items(type: 'string'),
                    ),
                    new OA\Property(property: 'options', type: 'object', nullable: true),
                ],
            ),
        ),
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/AutocompleteShare'),
            new OA\Response(response: 422, ref: '#/components/responses/AutocompleteValidationError'),
            new OA\Response(response: 500, ref: '#/components/responses/AutocompleteFailure'),
        ]
    )]
    public function shareSearch(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'q'       => 'required|string|min:2|max:255',
                'types'   => 'array',
                'types.*' => 'string|in:products,categories,brands,collections,attributes,locations,countries,cities,orders,customers,addresses',
                'options' => 'array',
            ]);

            return response()->json(['results' => $results]);
        } catch (\Throwable $e) {
            report($e);

            // Get search results
            $results = $this->autocompleteService->search($query, 100, $types);

            // Generate shareable link
            $exportService = app(\App\Services\SearchExportService::class);
            $shareResult = $exportService->generateShareableLink($results, $query, $options);

            return response()->json($shareResult);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Share failed', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle viewSharedSearch functionality with proper error handling.
     */
    #[OA\Get(
        path: '/api/autocomplete/share/{shareId}',
        summary: 'Retrieve the payload associated with a shared autocomplete search.',
        tags: ['Autocomplete'],
        parameters: [
            new OA\PathParameter(
                name: 'shareId',
                description: 'Identifier of the shared search.',
                required: true,
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/AutocompleteShareView'),
            new OA\Response(response: 404, ref: '#/components/responses/AutocompleteShareNotFound'),
            new OA\Response(response: 500, ref: '#/components/responses/AutocompleteFailure'),
        ]
    )]
    public function viewSharedSearch(string $shareId): JsonResponse
    {
        try {
            $exportService = app(\App\Services\SearchExportService::class);
            $shareData = $exportService->getSharedSearch($shareId);

            if (! $shareData) {
                return response()->json(['success' => false, 'message' => 'Shared search not found or expired'], 404);
            }

            return response()->json([
                'success'    => true,
                'share_data' => $shareData,
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'View shared search failed', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle getAvailableFilters functionality with proper error handling.
     */
    #[OA\Get(
        path: '/api/autocomplete/filters',
        summary: 'Derive available filters from a search result set.',
        tags: ['Autocomplete'],
        parameters: [
            new OA\QueryParameter(
                name: 'q',
                description: 'Search query string.',
                required: true,
                schema: new OA\Schema(type: 'string', minLength: 2, maxLength: 255),
            ),
            new OA\QueryParameter(
                name: 'types',
                description: 'Restrict search to specific entity types before deriving filters.',
                required: false,
                schema: new OA\Schema(
                    type: 'array',
                    items: new OA\Items(type: 'string'),
                ),
                style: 'form',
                explode: true,
            ),
        ],
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/AutocompleteFilters'),
            new OA\Response(response: 422, ref: '#/components/responses/AutocompleteValidationError'),
            new OA\Response(response: 500, ref: '#/components/responses/AutocompleteFailure'),
        ]
    )]
    public function getAvailableFilters(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'q'       => 'required|string|min:2|max:255',
                'types'   => 'array',
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
                'query'   => $query,
            ]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Get filters failed', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle getMimeType functionality with proper error handling.
     */
    private function getMimeType(string $format): string
    {
        return match ($format) {
            'json'  => 'application/json',
            'csv'   => 'text/csv',
            'xml'   => 'application/xml',
            'xlsx'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            default => 'application/octet-stream',
        };
    }

    /**
     * Handle getSearchInsights functionality with proper error handling.
     */
    #[OA\Get(
        path: '/api/autocomplete/insights',
        summary: 'Return search insights for a query.',
        tags: ['Autocomplete'],
        parameters: [
            new OA\QueryParameter(
                name: 'q',
                description: 'Search query string.',
                required: true,
                schema: new OA\Schema(type: 'string', minLength: 2, maxLength: 255),
            ),
            new OA\QueryParameter(
                name: 'context',
                description: 'Optional context metadata to tailor insights.',
                required: false,
                style: 'deepObject',
                explode: true,
                schema: new OA\Schema(type: 'object'),
            ),
        ],
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/AutocompleteInsights'),
            new OA\Response(response: 422, ref: '#/components/responses/AutocompleteValidationError'),
            new OA\Response(response: 500, ref: '#/components/responses/AutocompleteFailure'),
        ]
    )]
    public function getSearchInsights(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'q'       => 'required|string|min:2|max:255',
                'context' => 'array',
            ]);

            $query = $validated['q'];
            $context = $validated['context'] ?? [];

            $insightsService = app(\App\Services\SearchInsightsService::class);
            $insights = $insightsService->getSearchInsights($query, $context);

            return response()->json([
                'success'  => true,
                'insights' => $insights,
                'query'    => $query,
            ]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Get insights failed', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle getSearchRecommendations functionality with proper error handling.
     */
    #[OA\Get(
        path: '/api/autocomplete/recommendations',
        summary: 'Return recommendations derived from search context.',
        tags: ['Autocomplete'],
        parameters: [
            new OA\QueryParameter(
                name: 'q',
                description: 'Search query string.',
                required: true,
                schema: new OA\Schema(type: 'string', minLength: 2, maxLength: 255),
            ),
            new OA\QueryParameter(
                name: 'context',
                description: 'Optional context metadata to tailor recommendations.',
                required: false,
                style: 'deepObject',
                explode: true,
                schema: new OA\Schema(type: 'object'),
            ),
        ],
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/AutocompleteRecommendations'),
            new OA\Response(response: 422, ref: '#/components/responses/AutocompleteValidationError'),
            new OA\Response(response: 500, ref: '#/components/responses/AutocompleteFailure'),
        ]
    )]
    public function getSearchRecommendations(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'q'       => 'required|string|min:2|max:255',
                'context' => 'array',
            ]);

            $query = $validated['q'];
            $context = $validated['context'] ?? [];

            $recommendationsService = app(\App\Services\SearchRecommendationsService::class);
            $recommendations = $recommendationsService->getSearchRecommendations($query, $context);

            return response()->json([
                'success'         => true,
                'recommendations' => $recommendations,
                'query'           => $query,
            ]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Get recommendations failed', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle getSearchAnalytics functionality with proper error handling.
     */
    #[OA\Get(
        path: '/api/autocomplete/analytics',
        summary: 'Return analytics metrics for autocomplete usage.',
        tags: ['Autocomplete'],
        parameters: [
            new OA\QueryParameter(
                name: 'period',
                description: 'Reporting period to evaluate.',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['today', 'week', 'month', 'quarter', 'year'], default: 'month'),
            ),
            new OA\QueryParameter(
                name: 'metrics',
                description: 'Specific metric buckets to include.',
                required: false,
                schema: new OA\Schema(
                    type: 'array',
                    items: new OA\Items(type: 'string', enum: ['searches', 'clicks', 'conversions', 'revenue', 'users']),
                ),
                style: 'form',
                explode: true,
            ),
        ],
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/AutocompleteAnalytics'),
            new OA\Response(response: 422, ref: '#/components/responses/AutocompleteValidationError'),
            new OA\Response(response: 500, ref: '#/components/responses/AutocompleteFailure'),
        ]
    )]
    public function getSearchAnalytics(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'period'    => 'string|in:today,week,month,quarter,year',
                'metrics'   => 'array',
                'metrics.*' => 'string|in:searches,clicks,conversions,revenue,users',
            ]);

            $period = $validated['period'] ?? 'month';
            $metrics = $validated['metrics'] ?? ['searches', 'clicks', 'conversions'];

            $analyticsService = app(SearchAnalyticsService::class);
            $performanceService = app(\App\Services\SearchPerformanceService::class);

            $analytics = [
                'period'  => $period,
                'metrics' => $metrics,
                'data'    => [],
            ];

            foreach ($metrics as $metric) {
                $analytics['data'][$metric] = match ($metric) {
                    'searches'    => $this->getSearchMetrics($analyticsService, $period),
                    'clicks'      => $this->getClickMetrics($analyticsService, $period),
                    'conversions' => $this->getConversionMetrics($analyticsService, $period),
                    'revenue'     => $this->getRevenueMetrics($analyticsService, $period),
                    'users'       => $this->getUserMetrics($analyticsService, $period),
                    default       => [],
                };
            }

            return response()->json([
                'success'   => true,
                'analytics' => $analytics,
            ]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Get analytics failed', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle getSearchMetrics functionality with proper error handling.
     */
    private function getSearchMetrics(SearchAnalyticsService $analyticsService, string $period): array
    {
        try {
            $since = match ($period) {
                'today'   => now()->startOfDay(),
                'week'    => now()->subWeek(),
                'month'   => now()->subMonth(),
                'quarter' => now()->subQuarter(),
                'year'    => now()->subYear(),
                default   => now()->subMonth(),
            };

            return [
                'total_searches'     => $analyticsService->getTotalSearches($since),
                'unique_searches'    => $analyticsService->getUniqueSearches($since),
                'no_result_searches' => $analyticsService->getNoResultSearchesCount($since),
                'average_results'    => $analyticsService->getAverageResultsPerSearch($since),
                'popular_searches'   => $analyticsService->getPopularSearchesForDateRange(10, $since),
                'search_trends'      => $analyticsService->getSearchTrendsForDateRange(30),
            ];
        } catch (Exception $e) {
            Log::warning('Search metrics failed: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Handle getClickMetrics functionality with proper error handling.
     */
    private function getClickMetrics(SearchAnalyticsService $analyticsService, string $period): array
    {
        try {
            // This would typically get click metrics from analytics data
            return [
                'total_clicks'        => rand(1000, 10000),
                'click_through_rate'  => rand(10, 25) / 100,
                'top_clicked_results' => [],
                'click_trends'        => [],
            ];
        } catch (Exception $e) {
            Log::warning('Click metrics failed: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Handle getConversionMetrics functionality with proper error handling.
     */
    private function getConversionMetrics(SearchAnalyticsService $analyticsService, string $period): array
    {
        try {
            // This would typically get conversion metrics from analytics data
            return [
                'total_conversions' => rand(50, 500),
                'conversion_rate'   => rand(2, 8) / 100,
                'conversion_value'  => rand(1000, 10000),
                'conversion_trends' => [],
            ];
        } catch (Exception $e) {
            Log::warning('Conversion metrics failed: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Handle getRevenueMetrics functionality with proper error handling.
     */
    private function getRevenueMetrics(SearchAnalyticsService $analyticsService, string $period): array
    {
        try {
            // This would typically get revenue metrics from analytics data
            return [
                'total_revenue'       => rand(5000, 50000),
                'average_order_value' => rand(50, 200),
                'revenue_per_search'  => rand(1, 10),
                'revenue_trends'      => [],
            ];
        } catch (Exception $e) {
            Log::warning('Revenue metrics failed: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Handle getUserMetrics functionality with proper error handling.
     */
    private function getUserMetrics(SearchAnalyticsService $analyticsService, string $period): array
    {
        try {
            // This would typically get user metrics from analytics data
            return [
                'total_users'     => rand(500, 5000),
                'new_users'       => rand(100, 1000),
                'returning_users' => rand(200, 2000),
                'user_engagement' => rand(60, 90) / 100,
            ];
        } catch (Exception $e) {
            Log::warning('User metrics failed: ' . $e->getMessage());

            return [];
        }
    }
}
