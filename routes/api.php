<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\AuthenticatedUserController;
use App\Http\Controllers\Api\AutocompleteSearchController;
use App\Http\Controllers\Api\ExportDownloadController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\SearchController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
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
                // Ability checks are handled inside ShowAuthenticatedUserRequest so we only keep the
                // dedicated profile rate limit middleware on the route definition itself.
                ->middleware(['throttle:api.profile'])
                ->name('user.show');

            Route::post('/autocomplete-search', AutocompleteSearchController::class)
                // AutocompleteRequest performs the Sanctum ability validation which keeps response
                // messaging consistent with the rest of our API layer while we retain rate limiting.
                ->middleware(['throttle:api.autocomplete'])
                ->withoutMiddleware('throttle:api.default')
                ->name('autocomplete.search');

            require __DIR__ . '/api/notifications.php';
        });
    });

Route::get('exports/download/{export:uuid}', ExportDownloadController::class)
    ->middleware(['signed', 'auth:sanctum', 'abilities:exports.download', 'throttle:api.exports'])
    ->name('exports.signed-download');

Route::prefix('partner')
    ->middleware(['partner.api.auth', 'partner.api.rate_limit'])
    ->name('api.partner.')
    ->group(function (): void {
        require __DIR__ . '/api/partner.php';
    });

Route::get('audit-logs', [AuditLogController::class, 'index'])
    ->middleware(['throttle:api.read'])
    ->name('api.audit-logs.index');

// Pull in the legacy campaign click endpoints until we consolidate them under the versioned API namespace.
require base_path('routes/campaign-clicks.php');
