<?php

use App\Http\Controllers\Api\NotificationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Notification API Routes
|--------------------------------------------------------------------------
|
| Here are the API routes for the notification system. These routes are
| protected by authentication middleware and provide endpoints for
| managing user notifications.
|
*/

Route::middleware(['auth:sanctum'])->group(function () {
    // Get user's notifications
    Route::get('/notifications', [NotificationController::class, 'index']);

    // Get notification statistics
    Route::get('/notifications/stats', [NotificationController::class, 'stats']);

    // Search notifications
    Route::get('/notifications/search', [NotificationController::class, 'search']);

    // Mark all notifications as read
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);

    // Mark all notifications as unread
    Route::post('/notifications/mark-all-unread', [NotificationController::class, 'markAllAsUnread']);

    // Get specific notification
    Route::get('/notifications/{notification}', [NotificationController::class, 'show']);

    // Mark notification as read
    Route::post('/notifications/{notification}/mark-read', [NotificationController::class, 'markAsRead']);

    // Mark notification as unread
    Route::post('/notifications/{notification}/mark-unread', [NotificationController::class, 'markAsUnread']);

    // Delete notification
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy']);
});
