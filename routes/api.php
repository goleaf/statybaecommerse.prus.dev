<?php

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

    try {
        $modelClass = $validated['model_class'];
        $searchField = $validated['search_field'] ?? $validated['label_field'] ?? 'name';
        $searchQuery = $validated['search_query'];
        $valueField = $validated['value_field'] ?? 'id';
        $labelField = $validated['label_field'] ?? 'name';
        $limit = $validated['limit'] ?? 10;

        // Check if the model class exists and is a valid Eloquent model
        if (!class_exists($modelClass) || !is_subclass_of($modelClass, 'Illuminate\Database\Eloquent\Model')) {
            return response()->json(['results' => []], 400);
        }

        $model = new $modelClass;

        $query = $model
            ->query()
            ->where($searchField, 'like', '%' . $searchQuery . '%')
            ->limit($limit);

        $results = $query->get()->map(function ($item) use ($valueField, $labelField) {
            return [
                'value' => $item->{$valueField},
                'label' => $item->{$labelField},
                'data' => $item->toArray(),
            ];
        });

        return response()->json(['results' => $results]);
    } catch (\Exception $e) {
        return response()->json(['results' => []], 500);
    }
})->name('api.autocomplete.search');
