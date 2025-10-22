<?php

declare(strict_types=1);

use App\Http\Controllers\Api\NotificationController;
use Illuminate\Support\Facades\Route;

Route::prefix('notifications')
    ->as('notifications.')
    ->withoutMiddleware(['throttle:api.default', 'throttle:api.read'])
    ->group(function (): void {
        Route::middleware(['abilities:notifications.read', 'throttle:api.notifications.read'])->group(function (): void {
            Route::get('/', [NotificationController::class, 'index'])->name('index');
            Route::get('/stats', [NotificationController::class, 'stats'])->name('stats');
            Route::get('/search', [NotificationController::class, 'search'])->name('search');
            Route::get('/{notification}', [NotificationController::class, 'show'])->name('show');
        });

        Route::middleware(['abilities:notifications.manage', 'throttle:api.notifications.write'])->group(function (): void {
            Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
            Route::post('/mark-all-unread', [NotificationController::class, 'markAllAsUnread'])->name('mark-all-unread');
            Route::post('/{notification}/mark-read', [NotificationController::class, 'markAsRead'])->name('mark-as-read');
            Route::post('/{notification}/mark-unread', [NotificationController::class, 'markAsUnread'])->name('mark-as-unread');
            Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('destroy');
        });
    });
