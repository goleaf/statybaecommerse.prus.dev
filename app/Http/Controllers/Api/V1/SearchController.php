<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Data\SearchQueryData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\SearchRequest;
use App\Http\Resources\SearchResultResource;
use App\Services\SearchService;
use Illuminate\Http\JsonResponse;

final class SearchController extends Controller
{
    public function __invoke(SearchRequest $request, SearchService $service): JsonResponse
    {
        $payload = $request->validated();

        // Aggregation hints are calculated server-side; strip any stray client input.
        unset($payload['types']);

        $queryData = SearchQueryData::fromArray($payload, [
            'ip' => $request->ip(),
            'user_id' => $request->user()?->getKey(),
            'user_agent' => $request->userAgent(),
            'locale' => app()->getLocale(),
        ]);

        $results = $service->search($queryData);

        // Normalise the payload via an API resource to keep response shape stable.
        return SearchResultResource::make($results)->toResponse($request);
    }
}
