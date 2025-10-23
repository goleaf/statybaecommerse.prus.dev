<?php

use App\Http\Controllers\Api\AutocompleteController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->middleware('throttle:api.default')
    ->name('api.v1.')
    ->group(function (): void {
        Route::get('/health', [HealthController::class, 'health'])
            ->name('health');

// Autocomplete search endpoint for AutocompleteSelect component
Route::post('/autocomplete-search', [AutocompleteController::class, 'search'])
    ->name('api.autocomplete.search');
