<?php

use App\Http\Controllers\Api\AutocompleteController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Autocomplete search endpoint for AutocompleteSelect component
Route::post('/autocomplete-search', [AutocompleteController::class, 'search'])
    ->name('api.autocomplete.search');
