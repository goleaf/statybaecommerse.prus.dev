<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AutocompleteSearchRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Autocomplete', description: 'Internal autocomplete utilities exposed over the API.')]
final class AutocompleteSearchController extends Controller
{
    #[OA\Post(
        path: '/api/v1/autocomplete-search',
        summary: 'Perform a generic autocomplete lookup for the requested model.',
        tags: ['Autocomplete'],
        security: [
            ['SanctumToken' => []],
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/AutocompleteSearchRequest'),
        ),
        responses: [
            new OA\Response(response: 200, ref: '#/components/responses/AutocompleteSearchResults'),
            new OA\Response(response: 401, ref: '#/components/responses/AuthenticationError'),
            new OA\Response(response: 422, ref: '#/components/responses/AutocompleteValidationError'),
            new OA\Response(response: 500, ref: '#/components/responses/AutocompleteFailure'),
        ]
    )]
    public function __invoke(AutocompleteSearchRequest $request): JsonResponse
    {
        $validated = $request->validated();

        /** @var class-string<Model> $modelClass */
        $modelClass = $validated['model_class'];

        $searchField = Arr::get($validated, 'search_field', Arr::get($validated, 'label_field', 'name'));
        $searchQuery = $validated['search_query'];
        $valueField = Arr::get($validated, 'value_field', 'id');
        $labelField = Arr::get($validated, 'label_field', 'name');
        $limit = Arr::get($validated, 'limit', 10);

        /** @var Model $model */
        $model = new $modelClass;

        $query = $model->newQuery()
            ->where($searchField, 'like', '%' . Str::of($searchQuery)->trim() . '%')
            ->limit($limit);

        $results = $query->get()->map(static fn (Model $item): array => [
            'value' => $item->getAttribute($valueField),
            'label' => $item->getAttribute($labelField),
            'data'  => $item->toArray(),
        ]);

        return response()->json([
            'success' => true,
            'results' => $results,
        ]);
    }
}
