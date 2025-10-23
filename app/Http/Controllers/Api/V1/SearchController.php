<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Data\SearchQueryData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\SearchRequest;
use App\Services\SearchService;
use Illuminate\Http\JsonResponse;

final class SearchController extends Controller
{
    public function __invoke(SearchRequest $request, SearchService $service): JsonResponse
    {
        // Mark strict sanitisation when clients send the v1 "query" parameter so the service can short-circuit risky payloads
        // without impacting legacy integrations that still rely on the "q" alias.
        $queryData = SearchQueryData::fromArray($request->validated(), [
            'ip' => $request->ip(),
            'user_id' => $request->user()?->getKey(),
            'user_agent' => $request->userAgent(),
            'locale' => app()->getLocale(),
            'strict_sanitization' => $request->has('query') && ! $request->has('q'),
        ]);

        $results = $service->search($queryData);

        if ($queryData->context()['strict_sanitization'] ?? false) {
            // Flatten the aggregated payload into the legacy list structure that older
            // storefront clients depend on while still exposing the enriched meta block.
            $flattened = [
                'data' => data_get($results, 'data.products.items', []),
                'meta' => $results['meta'] ?? [],
            ];

            if (isset($results['buckets'])) {
                $flattened['buckets'] = $results['buckets'];
            }

            return response()->json($flattened);
        }

        return response()->json($results);
    }
}
