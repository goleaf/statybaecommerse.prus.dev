<?php

use App\Http\Controllers\Api\AuditLogController;
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

    try {
        $modelClass = $validated['model_class'];
        $searchField = $validated['search_field'] ?? $validated['label_field'] ?? 'name';
        $searchQuery = $validated['search_query'];
        $valueField = $validated['value_field'] ?? 'id';
        $labelField = $validated['label_field'] ?? 'name';
        $limit = $validated['limit'] ?? 10;

        // Check if the model class exists and is a valid Eloquent model
        if (! class_exists($modelClass) || ! is_subclass_of($modelClass, 'Illuminate\Database\Eloquent\Model')) {
            return response()->json(['results' => []], 400);
        }

        $model = new $modelClass;

        $query = $model
            ->query()
            ->where($searchField, 'like', '%'.$searchQuery.'%')
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

Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('api.audit-logs.index');
