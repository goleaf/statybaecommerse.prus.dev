<?php

use App\Http\Controllers\Api\AuthenticatedUserController;
use App\Http\Controllers\Api\AutocompleteSearchController;
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

        require __DIR__.'/api/notifications.php';
    });

Route::get('api/v1/exports/{export}/download', \App\Http\Controllers\Api\ExportDownloadController::class)
    ->middleware('signed')
    ->name('api.exports.download');
