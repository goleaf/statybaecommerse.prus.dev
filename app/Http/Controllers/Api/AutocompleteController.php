<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AutocompleteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;
use Throwable;

#[OA\Tag(
    name: 'Autocomplete',
    description: 'Endpoints providing autocomplete suggestions for various storefront resources.'
)]
final class AutocompleteController extends Controller
{
    public function __construct(private readonly AutocompleteService $autocompleteService) {}

    #[OA\Get(
        path: '/api/autocomplete/search',
        summary: 'Search across autocomplete resources.',
        tags: ['Autocomplete']
    )]
    public function search(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'q'       => ['required', 'string', 'min:1', 'max:255'],
            'limit'   => ['sometimes', 'integer', 'min:1', 'max:50'],
            'types'   => ['sometimes', 'array'],
            'types.*' => ['string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        $query = (string) $validated['q'];
        $limit = isset($validated['limit']) ? (int) $validated['limit'] : 10;
        $types = $validated['types'] ?? [];

        try {
            $results = $this->autocompleteService->search($query, $limit, $types);

            return response()->json([
                'success' => true,
                'data'    => $results,
                'meta'    => [
                    'query' => $query,
                    'limit' => $limit,
                    'types' => $types,
                    'count' => count($results),
                ],
            ]);
        } catch (Throwable $exception) {
            Log::error('Autocomplete search failed.', [
                'query'     => $query,
                'limit'     => $limit,
                'types'     => $types,
                'exception' => $exception,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Search failed.',
            ], 500);
        }
    }

    #[OA\Get(
        path: '/api/autocomplete/products',
        summary: 'Search for product suggestions.',
        tags: ['Autocomplete']
    )]
    public function products(Request $request): JsonResponse
    {
        return $this->handleTypedSearch($request, fn (string $query, int $limit): array => $this->autocompleteService->searchProducts($query, $limit), 'products');
    }

    #[OA\Get(
        path: '/api/autocomplete/categories',
        summary: 'Search for category suggestions.',
        tags: ['Autocomplete']
    )]
    public function categories(Request $request): JsonResponse
    {
        return $this->handleTypedSearch($request, fn (string $query, int $limit): array => $this->autocompleteService->searchCategories($query, $limit), 'categories');
    }

    /**
     * @param callable(string, int): array $searchCallback
     */
    private function handleTypedSearch(Request $request, callable $searchCallback, string $type): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'q'     => ['required', 'string', 'min:1', 'max:255'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        $query = (string) $validated['q'];
        $limit = isset($validated['limit']) ? (int) $validated['limit'] : 10;

        try {
            $results = $searchCallback($query, $limit);

            return response()->json([
                'success' => true,
                'data'    => $results,
                'meta'    => [
                    'query' => $query,
                    'limit' => $limit,
                    'type'  => $type,
                    'count' => count($results),
                ],
            ]);
        } catch (Throwable $exception) {
            Log::error('Autocomplete typed search failed.', [
                'query'     => $query,
                'limit'     => $limit,
                'type'      => $type,
                'exception' => $exception,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Search failed.',
            ], 500);
        }
    }
}
