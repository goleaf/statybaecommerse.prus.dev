<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Autocomplete search endpoint for AutocompleteSelect component
Route::post('/autocomplete-search', function (Request $request) {
    $validated = $request->validate([
        'model_class' => 'required|string',
        'search_field' => 'nullable|string',
        'search_query' => 'required|string',
        'value_field' => 'nullable|string',
        'label_field' => 'nullable|string',
        'limit' => 'nullable|integer|min:1|max:100',
    ]);

    $modelClass = $validated['model_class'];
    $searchField = $validated['search_field'] ?? $validated['label_field'] ?? 'name';
    $searchQuery = $validated['search_query'];
    $valueField = $validated['value_field'] ?? 'id';
    $labelField = $validated['label_field'] ?? 'name';
    $limit = $validated['limit'] ?? 10;

    if (! class_exists($modelClass) || ! is_subclass_of($modelClass, Model::class)) {
        throw new \DomainException(__('The requested model is not searchable.'));
    }

    /** @var Model $model */
    $model = new $modelClass();

    $results = $model
        ->query()
        ->where($searchField, 'like', '%'.$searchQuery.'%')
        ->limit($limit)
        ->get()
        ->map(static function (Model $item) use ($valueField, $labelField) {
            return [
                'value' => $item->{$valueField},
                'label' => $item->{$labelField},
                'data' => $item->toArray(),
            ];
        });

    return response()->json(['results' => $results]);
})->name('api.autocomplete.search');
