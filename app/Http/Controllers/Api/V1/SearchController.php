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
        $queryData = SearchQueryData::fromArray($request->validated(), [
            'ip'         => $request->ip(),
            'user_id'    => $request->user()?->getKey(),
            'user_agent' => $request->userAgent(),
            'locale'     => app()->getLocale(),
        ]);

        $results = $service->search($queryData);

        return response()->json($results);
    }
}
