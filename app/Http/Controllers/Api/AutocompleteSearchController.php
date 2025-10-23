<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AutocompleteSearchRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Throwable;

final class AutocompleteSearchController extends Controller
{
    public function __invoke(AutocompleteSearchRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $modelClass = $validated['model_class'];

        if (! class_exists($modelClass) || ! is_subclass_of($modelClass, Model::class)) {
            return response()->json([
                'success' => false,
                'message' => 'The selected model_class is invalid.',
                'errors' => ['model_class' => ['The selected model_class must be an Eloquent model.']],
            ], 422);
        }

        $searchField = Arr::get($validated, 'search_field', Arr::get($validated, 'label_field', 'name'));
        $searchQuery = $validated['search_query'];
        $valueField = Arr::get($validated, 'value_field', 'id');
        $labelField = Arr::get($validated, 'label_field', 'name');
        $limit = Arr::get($validated, 'limit', 10);

        try {
            /** @var Model $model */
            $model = new $modelClass();

            $query = $model->newQuery()
                ->where($searchField, 'like', '%'.Str::of($searchQuery)->trim().'%')
                ->limit($limit);

            $results = $query->get()->map(static fn (Model $item): array => [
                'value' => $item->getAttribute($valueField),
                'label' => $item->getAttribute($labelField),
                'data' => $item->toArray(),
            ]);

            return response()->json([
                'success' => true,
                'results' => $results,
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'message' => 'Unable to complete the autocomplete search.',
            ], 500);
        }
    }
}
