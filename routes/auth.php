<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\VerifyEmailController;
use App\Livewire\Pages;
use Illuminate\Support\Facades\Route;

// Avoid importing Volt directly; resolve via FQCN checks to prevent CLI context failures

Route::middleware('guest')->group(function (): void {
    Route::get('register', \App\Livewire\Auth\Register::class)->name('register');
    Route::get('login', \App\Livewire\Auth\Login::class)
        ->middleware('throttle:auth.login')
        ->name('login');
    Route::get('forgot-password', \App\Livewire\Pages\Auth\ForgotPassword::class)
        ->middleware('throttle:auth.password-reset')
        ->name('password.request');
    Route::view('reset-password/{token}', 'livewire.pages.auth.reset-password')
        ->middleware('throttle:auth.password-reset')
        ->name('password.reset');
});

Route::middleware('auth')->group(function (): void {
    // Logout action
    Route::post('logout', \App\Livewire\Actions\Logout::class)->name('logout');
    // Graceful GET logout for direct URL visits
    Route::get('logout', \App\Livewire\Actions\Logout::class)->name('logout.get');
    Route::view('verify-email', 'livewire.pages.auth.verify-email')->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::view('confirm-password', 'livewire.pages.auth.confirm-password')->name('password.confirm');

    // Account dashboard routes are defined below to keep them under auth middleware.

    Route::prefix('account')->as('account.')->group(function (): void {
        // Use Livewire component for account index
        Route::get('/', \App\Livewire\Pages\Account\Index::class)->name('index');
        Route::get('profile', \App\Livewire\Pages\Account\Profile::class)->name('profile');
        Route::get('addresses', Pages\Account\Addresses::class)->name('addresses');
        Route::get('orders', Pages\Account\Orders::class)->name('orders');
        // Orders invoice view
        Route::view('orders/{number}/invoice', 'livewire.pages.account.orders.invoice')->name('orders.invoice');
        // Order details page
        Route::get('orders/{number}', \App\Livewire\Pages\Account\Orders\Detail::class)->name('orders.detail');
        // Alias name to satisfy route index: account.order.show
        Route::get('order/{number}', function (string $number) {
            return redirect()->route('account.orders.detail', ['number' => $number]);
        })->name('order.show');

        // Documents page
        Route::view('documents', 'livewire.pages.account.documents')->name('documents');

        // Notifications page (graceful if DB notifications not set up)
        Route::view('notifications', 'livewire.pages.account.notifications')->name('notifications');
    });
});
