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
        path: '/v1/autocomplete-search',
        summary: 'Generic autocomplete endpoint',
        description: 'Resolve autocomplete suggestions for arbitrary models using a validated configuration payload.',
        tags: ['Autocomplete'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                type: 'object',
                required: ['model_class', 'search_query'],
                properties: [
                    new OA\Property(property: 'model_class', type: 'string', description: 'Fully qualified model class to query.'),
                    new OA\Property(property: 'search_query', type: 'string', description: 'Query string to search for.'),
                    new OA\Property(property: 'search_field', type: 'string', nullable: true),
                    new OA\Property(property: 'label_field', type: 'string', nullable: true),
                    new OA\Property(property: 'value_field', type: 'string', nullable: true),
                    new OA\Property(property: 'limit', type: 'integer', minimum: 1, maximum: 100, nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Autocomplete results returned successfully.',
                content: new OA\JsonContent(ref: '#/components/schemas/AutocompleteSearchResponse')
            ),
            new OA\Response(
                response: 422,
                description: 'Validation failed.',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationProblemDetails')
            ),
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
