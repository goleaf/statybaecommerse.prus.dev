<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')
    ->name('api.v1.')
    ->middleware('auth:sanctum')
    ->group(function (): void {
        Route::get('/user', AuthenticatedUserController::class)
            ->middleware(['abilities:profile.read', 'throttle:api.default'])
            ->name('user.show');

        Route::post('/autocomplete-search', AutocompleteSearchController::class)
            ->middleware(['abilities:system.autocomplete', 'throttle:api.autocomplete'])
            ->name('autocomplete.search');

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

require __DIR__.'/api/notifications.php';
