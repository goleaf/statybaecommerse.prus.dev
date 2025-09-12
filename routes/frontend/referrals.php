<?php

use App\Http\Controllers\Frontend\ReferralController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    // Referral management routes
    Route::prefix('referrals')->name('referrals.')->group(function () {
        Route::get('/', [ReferralController::class, 'index'])->name('index');
        Route::get('/create', [ReferralController::class, 'create'])->name('create');
        Route::post('/', [ReferralController::class, 'store'])->name('store');
        Route::get('/share', [ReferralController::class, 'shareCode'])->name('share');
        Route::get('/statistics', [ReferralController::class, 'statistics'])->name('statistics');
        Route::get('/rewards', [ReferralController::class, 'rewards'])->name('rewards');
        
        // AJAX routes
        Route::post('/generate-code', [ReferralController::class, 'generateCode'])->name('generate_code');
        Route::post('/apply-code', [ReferralController::class, 'applyCode'])->name('apply_code');
    });
});

// Public referral application route
Route::get('/referral/{code}', [ReferralController::class, 'show'])->name('referrals.apply');


