<?php

use App\Http\Controllers\Api\AuthenticatedUserController;
use App\Http\Controllers\Api\AutocompleteSearchController;
use App\Http\Controllers\Api\SignedExportDownloadController;
use App\Http\Controllers\Api\V1\HealthController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')
    ->name('api.v1.')
    ->group(function (): void {
        Route::get('/health', [HealthController::class, 'health'])
            ->middleware(['throttle:api.default'])
            ->name('health');

        Route::get('/ready', [HealthController::class, 'ready'])
            ->middleware(['throttle:api.default'])
            ->name('ready');

        Route::middleware('auth:sanctum')->group(function (): void {
            Route::get('/user', AuthenticatedUserController::class)
                ->middleware(['abilities:profile.read', 'throttle:api.default'])
                ->name('user.show');

            Route::post('/autocomplete-search', AutocompleteSearchController::class)
                ->middleware(['abilities:system.autocomplete', 'throttle:api.autocomplete'])
                ->name('autocomplete.search');

            require __DIR__.'/api/notifications.php';
        });
    });

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
});
