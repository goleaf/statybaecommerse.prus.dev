<?php

use App\Http\Controllers\Api\CampaignClickController;
use Illuminate\Support\Facades\Route;

Route::prefix('campaign-clicks')->group(function () {
    Route::get('/', [CampaignClickController::class, 'index']);
    Route::post('/', [CampaignClickController::class, 'store']);
    Route::get('/statistics', [CampaignClickController::class, 'statistics']);
    Route::get('/analytics', [CampaignClickController::class, 'analytics']);
    Route::get('/export', [CampaignClickController::class, 'export']);
    
    Route::get('/{campaignClick}', [CampaignClickController::class, 'show']);
    Route::put('/{campaignClick}', [CampaignClickController::class, 'update']);
    Route::delete('/{campaignClick}', [CampaignClickController::class, 'destroy']);
});

// Campaign-specific clicks
Route::prefix('campaigns/{campaign}/clicks')->group(function () {
    Route::get('/', [CampaignClickController::class, 'index']);
});

// User's own clicks
Route::prefix('my/campaign-clicks')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [CampaignClickController::class, 'index']);
});
