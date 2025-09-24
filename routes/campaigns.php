<?php

use App\Http\Controllers\Frontend\CampaignController;
use Illuminate\Support\Facades\Route;

Route::prefix('campaigns')->name('campaigns.')->group(function () {
    Route::get('/', [CampaignController::class, 'index'])->name('index');
    Route::get('/featured', [CampaignController::class, 'featured'])->name('featured');
    Route::get('/{campaign:slug}', [CampaignController::class, 'show'])->name('show');
    Route::get('/{campaign:slug}/products', [CampaignController::class, 'products'])->name('products');
    Route::get('/{campaign:slug}/analytics', [CampaignController::class, 'analytics'])->name('analytics');

    // AJAX routes for tracking
    Route::post('/{campaign}/click', [CampaignController::class, 'click'])->name('click');
    Route::post('/{campaign}/conversion', [CampaignController::class, 'conversion'])->name('conversion');
});
