<?php declare(strict_types=1);

use App\Livewire\Pages;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/health', fn() => response()->json(['ok' => true]))->name('health');

// Root redirect to home
Route::get('/', fn() => redirect('/home'))->name('root.redirect');

// Basic routes
Route::get('/home', Pages\Home::class)->name('home');
Route::get('/cart', Pages\Cart::class)->name('cart.index');

// Auth routes
require __DIR__ . '/auth.php';

// Authenticated routes
Route::middleware('auth')->group(function (): void {
    Route::get('checkout', Pages\Checkout::class)->name('checkout.index');
});