<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AutocompleteSearchRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
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

        // Resolve the incoming configuration while providing sensible defaults.
        $searchField = Arr::get($validated, 'search_field', Arr::get($validated, 'label_field', 'name'));
        $valueField = Arr::get($validated, 'value_field', 'id');
        $labelField = Arr::get($validated, 'label_field', 'name');
        $limit = (int) Arr::get($validated, 'limit', 10);

        // Trim the search query to avoid queries that only contain whitespace.
        $searchQuery = Str::of($validated['search_query'])->trim();

        // Fail fast when the trimmed query is empty to avoid returning entire tables.
        if ($searchQuery->isEmpty()) {
            return response()->json([
                'success' => true,
                'results' => [],
            ]);
        }

        // Resolve the sanitized search string once to reuse in the query builder.
        $searchTerm = $searchQuery->value();

        /** @var Model $model */
        $model = new $modelClass;

        // Guard against SQL errors by ensuring that all referenced columns exist on the model's table.
        $table = $model->getTable();
        $columnsToValidate = array_unique([$searchField, $valueField, $labelField]);

        foreach ($columnsToValidate as $column) {
            if (! Schema::hasColumn($table, $column)) {
                // Map the failing column back to the most relevant request attribute for better feedback.
                $messageKey = match ($column) {
                    $searchField => 'search_field',
                    $valueField => 'value_field',
                    default => 'label_field',
                };

                throw ValidationException::withMessages([
                    $messageKey => [__('The requested column :column is not available for autocomplete searches.', ['column' => $column])],
                ]);
            }
        }

        // Build a list of columns that should be retrieved for the response payload.
        $columnsToSelect = array_values(array_unique([$valueField, $labelField]));

        $query = $model->newQuery()
            // Only perform the LIKE query against validated columns and sanitized input.
            ->where($searchField, 'like', '%' . $searchTerm . '%')
            ->select($columnsToSelect)
            ->limit($limit);

        $results = $query->get()->map(static fn (Model $item): array => [
            // Include the configured value and label fields.
            'value' => $item->getAttribute($valueField),
            'label' => $item->getAttribute($labelField),
            // Expose only the selected columns to the consumer to avoid leaking sensitive attributes.
            'data'  => $item->only($columnsToSelect),
        ]);

        return response()->json([
            'success' => true,
            'results' => $results,
        ]);
    }
}
