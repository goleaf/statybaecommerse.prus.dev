<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AutocompleteSearchRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

final class AutocompleteSearchController extends Controller
{
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
    }
}
