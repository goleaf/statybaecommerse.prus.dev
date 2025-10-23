<?php

use App\Http\Controllers\Api\AuthenticatedUserController;
use App\Http\Controllers\Api\AutocompleteSearchController;
use App\Http\Controllers\Api\ExportDownloadController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\SearchController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')
    ->middleware('throttle:api.read')
    ->name('api.v1.')
    ->group(function (): void {
        Route::get('/health', [HealthController::class, 'health'])
            ->name('health');

        Route::get('/ready', [HealthController::class, 'ready'])
            ->name('ready');

        Route::get('/search', SearchController::class)
            ->name('search');

        Route::middleware('auth:sanctum')->group(function (): void {
            Route::get('/user', AuthenticatedUserController::class)
                ->middleware(['abilities:profile.read'])
                ->name('user.show');

            Route::post('/autocomplete-search', AutocompleteSearchController::class)
                ->middleware(['abilities:system.autocomplete', 'throttle:api.autocomplete'])
                ->withoutMiddleware(['throttle:api.default', 'throttle:api.read'])
                ->name('autocomplete.search');

            require __DIR__.'/api/notifications.php';
        });
    });

Route::get('exports/download/{export:uuid}', SignedExportDownloadController::class)
    ->middleware(['signed', 'auth:sanctum', 'abilities:exports.download', 'can:exports.view', 'throttle:api.exports'])
    ->name('exports.signed-download');

Route::prefix('partner')
    ->middleware(['partner.api.auth', 'partner.api.rate_limit'])
    ->name('api.partner.')
    ->group(function (): void {
        require __DIR__.'/api/partner.php';
    });
