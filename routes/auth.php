<?php declare(strict_types=1);

use App\Http\Controllers\Auth\VerifyEmailController;
use App\Livewire\Pages;
use Illuminate\Support\Facades\Route;

// Avoid importing Volt directly; resolve via FQCN checks to prevent CLI context failures

Route::middleware('guest')->group(function (): void {
    Route::view('register', 'livewire.pages.auth.register')->name('register');
    Route::view('login', 'livewire.pages.auth.login')->name('login');
    Route::view('forgot-password', 'livewire.pages.auth.forgot-password')->name('password.request');
    Route::view('reset-password/{token}', 'livewire.pages.auth.reset-password')->name('password.reset');
});

Route::middleware('auth')->group(function (): void {
    Route::view('verify-email', 'livewire.pages.auth.verify-email')->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::view('confirm-password', 'livewire.pages.auth.confirm-password')->name('password.confirm');

    Route::view('/account', 'livewire.pages.account.index')->name('account');

    Route::prefix('account')->as('account.')->group(function (): void {
        Route::view('profile', 'livewire.pages.account.profile')->name('profile');
        Route::get('addresses', Pages\Account\Addresses::class)->name('addresses');
        Route::get('orders', Pages\Account\Orders::class)->name('orders');
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
    });
});
