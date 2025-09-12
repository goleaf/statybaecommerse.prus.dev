<?php

declare(strict_types=1);

use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Report Routes
|--------------------------------------------------------------------------
|
| Here are the routes for the report functionality. These routes are
| loaded by the RouteServiceProvider within a group which contains
| the "web" middleware group.
|
*/

Route::prefix('reports')->name('reports.')->group(function () {
    // Public routes
    Route::get('/', [ReportController::class, 'index'])->name('index');
    Route::get('/{report:slug}', [ReportController::class, 'show'])->name('show');
    Route::get('/{report:slug}/download', [ReportController::class, 'download'])->name('download');

    // Authenticated routes
    Route::middleware('auth')->group(function () {
        Route::post('/{report:slug}/generate', [ReportController::class, 'generate'])->name('generate');
    });
});
