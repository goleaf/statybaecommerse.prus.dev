<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\VerifyEmailController;
use App\Livewire\Pages;
use Illuminate\Support\Facades\Route;

// Avoid importing Volt directly; resolve via FQCN checks to prevent CLI context failures

Route::middleware('guest')->group(function (): void {
    Route::get('register', \App\Livewire\Auth\Register::class)->name('register');
    Route::get('login', \App\Livewire\Auth\Login::class)->name('login');
    Route::view('forgot-password', 'livewire.pages.auth.forgot-password')->name('password.request');
    Route::view('reset-password/{token}', 'livewire.pages.auth.reset-password')->name('password.reset');
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

    // Account dashboard (use view to avoid Volt alias issues)
    Route::view('/account', 'livewire.pages.account.index')->name('account');

    Route::prefix('account')->as('account.')->group(function (): void {
        Route::view('profile', 'livewire.pages.account.profile')->name('profile');
        Route::get('addresses', Pages\Account\Addresses::class)->name('addresses');
        Route::get('orders', Pages\Account\Orders::class)->name('orders');
        // Orders invoice view
        Route::view('orders/{number}/invoice', 'livewire.pages.account.orders.invoice')->name('orders.invoice');
        // Ensure Volt is available before registering Volt routes to avoid CLI context errors
        if (class_exists(\Livewire\Volt\Volt::class)) {
            \Livewire\Volt\Volt::route('orders/{number}', 'pages.account.orders.detail')->name('orders.detail');
        } else {
            Route::view('orders/{number}', 'livewire.pages.account.orders.detail')->name('orders.detail');
        }
        // Alias name to satisfy route index: account.order.show
        Route::get('order/{number}', function (string $number) {
            return redirect()->route('account.orders.detail', ['number' => $number]);
        })->name('order.show');
        // Account reviews page (list user's reviews)
        if (class_exists(\Livewire\Volt\Volt::class)) {
            \Livewire\Volt\Volt::route('reviews', 'pages.account.reviews')->name('reviews');
        } else {
            Route::view('reviews', 'livewire.pages.account.reviews')->name('reviews');
        }

        // Wishlist page
        Route::view('wishlist', 'livewire.pages.account.wishlist')->name('wishlist');

        // Documents page
        Route::view('documents', 'livewire.pages.account.documents')->name('documents');

        // Notifications page (graceful if DB notifications not set up)
        Route::view('notifications', 'livewire.pages.account.notifications')->name('notifications');
    });
});
