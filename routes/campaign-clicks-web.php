<?php

use App\Http\Controllers\Frontend\CampaignClickController;
use Illuminate\Support\Facades\Route;

Route::prefix('campaign-clicks')->name('campaign-clicks.')->group(function () {
    Route::get('/', [CampaignClickController::class, 'index'])->name('index');
    Route::get('/{campaignClick}', [CampaignClickController::class, 'show'])->name('show');
});
