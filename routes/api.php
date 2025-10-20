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

Route::get('exports/download/{export:uuid}', SignedExportDownloadController::class)
    ->middleware(['signed', 'auth:sanctum', 'abilities:exports.download', 'throttle:api.exports'])
    ->name('exports.signed-download');
