<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\AuthenticatedUserController;
use App\Http\Controllers\Api\AutocompleteSearchController;
use App\Http\Controllers\Api\ExportDownloadController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductHistoryController as ApiProductHistoryController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\SearchController;
use App\Http\Controllers\Webhooks\PaymentWebhookController;
use App\Support\Authorization\AuthorizationMatrix;
use Illuminate\Support\Facades\Route;

Route::prefix('webhooks')
    ->name('webhooks.')
    ->group(function (): void {
        Route::post('/stripe', [PaymentWebhookController::class, 'handleStripe'])->name('stripe');
        Route::post('/notchpay', [PaymentWebhookController::class, 'handleNotchPay'])->name('notchpay');
    });

Route::middleware('auth:sanctum')
    ->prefix('notifications')
    ->as('api.notifications.')
    ->withoutMiddleware(['throttle:api.default', 'throttle:api.read'])
    ->group(function (): void {
        Route::middleware('throttle:api.notifications.read')->group(function (): void {
            Route::get('/', [NotificationController::class, 'index'])->name('index');
            Route::get('/stats', [NotificationController::class, 'stats'])->name('stats');
            Route::get('/search', [NotificationController::class, 'search'])->name('search');
            Route::get('/{notification}', [NotificationController::class, 'show'])->name('show');
        });

        Route::middleware('throttle:api.notifications.write')->group(function (): void {
            Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
            Route::post('/mark-all-unread', [NotificationController::class, 'markAllAsUnread'])->name('mark-all-unread');
            Route::post('/{notification}/mark-read', [NotificationController::class, 'markAsRead'])->name('mark-as-read');
            Route::post('/{notification}/mark-unread', [NotificationController::class, 'markAsUnread'])->name('mark-as-unread');
            Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('destroy');
        });
    });

Route::prefix('products')
    ->name('api.products.')
    ->group(function (): void {
        Route::get('/', [ProductController::class, 'index'])
            ->middleware('throttle:api.read')
            ->name('index');

        Route::get('search', [ProductController::class, 'search'])
            ->middleware('throttle:api.read')
            ->name('search');

        Route::get('catalog', [ProductController::class, 'index'])
            ->middleware('throttle:api.read')
            ->name('catalog');

        Route::get('{product:slug}', [ProductController::class, 'show'])
            ->middleware('throttle:api.read')
            ->name('show');
    });

Route::prefix('v1')
    ->middleware('throttle:api.read')
    ->name('api.v1.')
    ->group(function (): void {
        Route::get('/health', [HealthController::class, 'health'])
            ->name('health');

        Route::get('/ready', [HealthController::class, 'ready'])
            ->name('ready');

        Route::get('/search', SearchController::class)
            ->middleware('throttle:api.search')
            ->withoutMiddleware('throttle:api.read')
            ->name('search');

        Route::middleware('auth:sanctum')->group(function (): void {
            Route::get('/user', AuthenticatedUserController::class)
                // Ability checks are handled inside ShowAuthenticatedUserRequest so we keep the dedicated
                // profile limiter while also enforcing the shared default bucket for consistent test coverage.
                ->middleware(['throttle:api.default', 'throttle:api.profile'])
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
    ->middleware(['signed', 'throttle:api.exports'])
    ->name('api.exports.download');

Route::prefix('partner')
    ->middleware(['partner.api.auth', 'partner.api.rate_limit'])
    ->name('api.partner.')
    ->group(function (): void {
        require __DIR__ . '/api/partner.php';
    });

Route::get('audit-logs', [AuditLogController::class, 'index'])
    ->middleware([
        'auth:sanctum',
        'permission:' . AuthorizationMatrix::ability('audit_logs', 'viewAny'),
        'throttle:api.read',
    ])
    ->name('api.audit-logs.index');

Route::prefix('admin/products/{product:id}/histories')
    ->middleware('auth:sanctum')
    ->name('api.admin.product-histories.')
    ->group(function (): void {
        Route::get('/', [ApiProductHistoryController::class, 'index'])
            ->middleware('permission:' . AuthorizationMatrix::ability('product_histories', 'viewAny'))
            ->name('index');

        Route::get('/statistics', [ApiProductHistoryController::class, 'statistics'])
            ->middleware('permission:' . AuthorizationMatrix::ability('product_histories', 'viewAny'))
            ->name('statistics');

        Route::get('/{history}', [ApiProductHistoryController::class, 'show'])
            ->middleware('permission:' . AuthorizationMatrix::ability('product_histories', 'view'))
            ->name('show');

        Route::post('/', [ApiProductHistoryController::class, 'store'])
            ->middleware('permission:' . AuthorizationMatrix::ability('product_histories', 'create'))
            ->name('store');

        Route::post('/export', [ApiProductHistoryController::class, 'export'])
            ->middleware('permission:' . AuthorizationMatrix::ability('product_histories', 'export'))
            ->name('export');
    });

// Pull in the legacy campaign click endpoints until we consolidate them under the versioned API namespace.
require base_path('routes/campaign-clicks.php');
