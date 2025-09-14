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

// User API Routes with safe serialization using except() method
Route::prefix('users')->middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [App\Http\Controllers\Api\UserController::class, 'profile']);
    Route::put('/profile', [App\Http\Controllers\Api\UserController::class, 'updateProfile']);
});

// Admin User API Routes
Route::prefix('admin/users')->middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/', [App\Http\Controllers\Api\UserController::class, 'index']);
    Route::get('/statistics', [App\Http\Controllers\Api\UserController::class, 'statistics']);
    Route::get('/{user}', [App\Http\Controllers\Api\UserController::class, 'show']);
    Route::get('/{user}/activity', [App\Http\Controllers\Api\UserController::class, 'activity']);
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

// Product History API Routes
Route::prefix('products/{product}/history')->group(function () {
    Route::get('/', [App\Http\Controllers\Api\ProductHistoryController::class, 'index']);
    Route::get('/statistics', [App\Http\Controllers\Api\ProductHistoryController::class, 'statistics']);
    Route::get('/export', [App\Http\Controllers\Api\ProductHistoryController::class, 'export'])->name('api.products.history.export');
    Route::post('/', [App\Http\Controllers\Api\ProductHistoryController::class, 'create'])->middleware('auth:sanctum');
    Route::get('/{history}', [App\Http\Controllers\Api\ProductHistoryController::class, 'show']);
});

// Product API Routes with Content Negotiation
Route::prefix('products')->group(function () {
    Route::get('/search', [App\Http\Controllers\Api\ProductController::class, 'search']);
    Route::get('/catalog', [App\Http\Controllers\Api\ProductController::class, 'catalog']);
    Route::get('/{product}', [App\Http\Controllers\Api\ProductController::class, 'show']);
});

// Category API Routes with Content Negotiation
Route::prefix('categories')->group(function () {
    Route::get('/', [App\Http\Controllers\Api\CategoryController::class, 'index']);
    Route::get('/tree', [App\Http\Controllers\Api\CategoryController::class, 'tree']);
    Route::get('/{category}', [App\Http\Controllers\Api\CategoryController::class, 'show']);
});

// Menu API Routes
Route::prefix('menus')->group(function () {
    Route::get('/', [App\Http\Controllers\Frontend\MenuController::class, 'index']);
    Route::get('/location/{location}', [App\Http\Controllers\Frontend\MenuController::class, 'byLocation']);
    Route::get('/{key}', [App\Http\Controllers\Frontend\MenuController::class, 'show']);
});

// News API Routes
Route::prefix('news')->group(function () {
    Route::get('/', [App\Http\Controllers\Frontend\NewsController::class, 'index']);
    Route::get('/featured', [App\Http\Controllers\Frontend\NewsController::class, 'featured']);
    Route::get('/categories', [App\Http\Controllers\Frontend\NewsController::class, 'categories']);
    Route::get('/tags', [App\Http\Controllers\Frontend\NewsController::class, 'tags']);
    Route::get('/{slug}/related', [App\Http\Controllers\Frontend\NewsController::class, 'related']);
    Route::get('/{slug}', [App\Http\Controllers\Frontend\NewsController::class, 'show']);
});

// Autocomplete API Routes
Route::prefix('autocomplete')->group(function () {
    Route::get('/search', [App\Http\Controllers\Api\AutocompleteController::class, 'search']);
    Route::get('/fuzzy', [App\Http\Controllers\Api\AutocompleteController::class, 'fuzzySearch']);
    Route::get('/personalized', [App\Http\Controllers\Api\AutocompleteController::class, 'personalized'])->middleware('auth:sanctum');
    Route::get('/products', [App\Http\Controllers\Api\AutocompleteController::class, 'products']);
    Route::get('/categories', [App\Http\Controllers\Api\AutocompleteController::class, 'categories']);
    Route::get('/brands', [App\Http\Controllers\Api\AutocompleteController::class, 'brands']);
    Route::get('/collections', [App\Http\Controllers\Api\AutocompleteController::class, 'collections']);
    Route::get('/attributes', [App\Http\Controllers\Api\AutocompleteController::class, 'attributes']);
    Route::get('/customers', [App\Http\Controllers\Api\AutocompleteController::class, 'customers'])->middleware('auth:sanctum');
    Route::get('/addresses', [App\Http\Controllers\Api\AutocompleteController::class, 'addresses'])->middleware('auth:sanctum');
    Route::get('/locations', [App\Http\Controllers\Api\AutocompleteController::class, 'locations'])->middleware('auth:sanctum');
    Route::get('/countries', [App\Http\Controllers\Api\AutocompleteController::class, 'countries'])->middleware('auth:sanctum');
    Route::get('/cities', [App\Http\Controllers\Api\AutocompleteController::class, 'cities'])->middleware('auth:sanctum');
    Route::get('/orders', [App\Http\Controllers\Api\AutocompleteController::class, 'orders'])->middleware('auth:sanctum');
    Route::get('/popular', [App\Http\Controllers\Api\AutocompleteController::class, 'popular']);
    Route::get('/recent', [App\Http\Controllers\Api\AutocompleteController::class, 'recent']);
    Route::get('/suggestions', [App\Http\Controllers\Api\AutocompleteController::class, 'suggestions']);
    Route::delete('/recent', [App\Http\Controllers\Api\AutocompleteController::class, 'clearRecent']);
    
    // Advanced search features
    Route::get('/paginated', [App\Http\Controllers\Api\AutocompleteController::class, 'paginatedSearch']);
    Route::get('/filters', [App\Http\Controllers\Api\AutocompleteController::class, 'getAvailableFilters']);
    Route::post('/export', [App\Http\Controllers\Api\AutocompleteController::class, 'exportSearch']);
    Route::get('/export/{exportId}/download', [App\Http\Controllers\Api\AutocompleteController::class, 'downloadExport'])->name('api.autocomplete.export.download');
    Route::post('/share', [App\Http\Controllers\Api\AutocompleteController::class, 'shareSearch']);
    Route::get('/share/{shareId}', [App\Http\Controllers\Api\AutocompleteController::class, 'viewSharedSearch'])->name('api.autocomplete.share.view');
    
    // Advanced analytics and insights
    Route::get('/insights', [App\Http\Controllers\Api\AutocompleteController::class, 'getSearchInsights']);
    Route::get('/recommendations', [App\Http\Controllers\Api\AutocompleteController::class, 'getSearchRecommendations']);
    Route::get('/analytics', [App\Http\Controllers\Api\AutocompleteController::class, 'getSearchAnalytics']);
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
