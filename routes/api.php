<?php

use App\Http\Controllers\Api\NotificationStreamController;
use App\Http\Controllers\ReferralController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Live notification stream for Server-Sent Events
Route::middleware(['auth:web'])->group(function () {
    Route::get('/notifications/stream', [NotificationStreamController::class, 'stream']);
});

// Discount Code API Routes
Route::prefix('discount-codes')->group(function () {
    Route::post('/validate', [App\Http\Controllers\Frontend\DiscountCodeController::class, 'validate']);
    Route::post('/apply', [App\Http\Controllers\Frontend\DiscountCodeController::class, 'apply']);
    Route::post('/remove', [App\Http\Controllers\Frontend\DiscountCodeController::class, 'remove']);
    Route::get('/available', [App\Http\Controllers\Frontend\DiscountCodeController::class, 'available']);
    Route::post('/{discountCode}/generate-document', [App\Http\Controllers\Frontend\DiscountCodeController::class, 'generateDocument']);
});

// Referral System API Routes
Route::prefix('referrals')->group(function () {
    // Public routes (no authentication required)
    Route::post('/validate-code', [ReferralController::class, 'validateCode']);
    Route::post('/process', [ReferralController::class, 'processReferral']);
    Route::get('/code-statistics', [ReferralController::class, 'codeStatistics']);
    Route::get('/referral-url', [ReferralController::class, 'getReferralUrl']);

    // Authenticated routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/dashboard', [ReferralController::class, 'dashboard']);
        Route::get('/statistics', [ReferralController::class, 'statistics']);
        Route::post('/generate-code', [ReferralController::class, 'generateCode']);
        Route::get('/pending-rewards', [ReferralController::class, 'pendingRewards']);
        Route::get('/applied-rewards', [ReferralController::class, 'appliedRewards']);
        Route::get('/recent-referrals', [ReferralController::class, 'recentReferrals']);
    });
});
