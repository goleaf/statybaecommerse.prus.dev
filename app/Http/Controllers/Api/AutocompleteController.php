<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Autocomplete', description: 'Reusable autocomplete search endpoints')]
final class AutocompleteController extends Controller
{
    #[OA\Post(
        path: '/api/autocomplete-search',
        operationId: 'autocompleteSearch',
        summary: 'Perform an autocomplete lookup on a given model.',
        tags: ['Autocomplete'],
        requestBody: new OA\RequestBody(
            required: true,
            description: 'Search configuration.',
            content: new OA\JsonContent(
                type: 'object',
                required: ['model_class', 'search_query'],
                properties: [
                    new OA\Property(property: 'model_class', type: 'string', description: 'Fully qualified model class name.'),
                    new OA\Property(property: 'search_field', type: 'string', nullable: true),
                    new OA\Property(property: 'search_query', type: 'string', description: 'User provided search query.'),
                    new OA\Property(property: 'value_field', type: 'string', nullable: true),
                    new OA\Property(property: 'label_field', type: 'string', nullable: true),
                    new OA\Property(property: 'limit', type: 'integer', minimum: 1, maximum: 100, nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Autocomplete results returned.',
                content: new OA\JsonContent(ref: '#/components/schemas/AutocompleteResponse')
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid model supplied.',
                content: new OA\JsonContent(ref: '#/components/schemas/AutocompleteResponse')
            ),
            new OA\Response(
                response: 500,
                description: 'Server error during search.',
                content: new OA\JsonContent(ref: '#/components/schemas/AutocompleteResponse')
            ),
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

        try {
            $modelClass = $validated['model_class'];
            $searchField = $validated['search_field'] ?? $validated['label_field'] ?? 'name';
            $searchQuery = $validated['search_query'];
            $valueField = $validated['value_field'] ?? 'id';
            $labelField = $validated['label_field'] ?? 'name';
            $limit = $validated['limit'] ?? 10;

            if (! class_exists($modelClass) || ! is_subclass_of($modelClass, Model::class)) {
                return response()->json(['results' => []], 400);
            }

            /** @var class-string<Model> $modelClass */
            $model = new $modelClass;

            $query = $model
                ->query()
                ->where($searchField, 'like', '%'.$searchQuery.'%')
                ->limit($limit);

            $results = $query->get()->map(static function (Model $item) use ($valueField, $labelField) {
                return [
                    'value' => Arr::get($item, $valueField),
                    'label' => Arr::get($item, $labelField),
                    'data' => $item->toArray(),
                ];
            });

            return response()->json(['results' => $results]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['results' => []], 500);
        }
    }
}
