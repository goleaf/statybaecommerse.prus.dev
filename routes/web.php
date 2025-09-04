<?php declare(strict_types=1);

use App\Livewire\Pages;
use Illuminate\Support\Facades\Route;

/*
 * |--------------------------------------------------------------------------
 * | Web Routes
 * |--------------------------------------------------------------------------
 */

Route::get('/health', fn() => response()->json(['ok' => true]))->name('health');

// Root redirect to home
Route::get('/', fn() => redirect('/home'))->name('root.redirect');

// Enhanced frontend routes
Route::get('/home', Pages\TestHome::class)->name('home');
Route::get('/products', Pages\ProductCatalog::class)->name('products.index');
Route::get('/products/{product}', Pages\SingleProduct::class)->name('products.show');
Route::get('/categories', Pages\Category\Index::class)->name('categories.index');
Route::get('/categories/{category}', Pages\Category\Show::class)->name('categories.show');
Route::get('/brands', Pages\Brand\Index::class)->name('brands.index');
Route::get('/brands/{brand}', Pages\Brand\Show::class)->name('brands.show');
Route::get('/cart', Pages\Cart::class)->name('cart.index');
Route::get('/search', Pages\Search::class)->name('search');

// Auth routes
require __DIR__ . '/auth.php';

// Authenticated routes
Route::middleware('auth')->group(function (): void {
    Route::get('/checkout', Pages\Checkout::class)->name('checkout.index');
    Route::get('/account', Pages\Account\Orders::class)->name('account.index');
    Route::get('/orders', Pages\Account\Orders::class)->name('orders.index');
});

// API routes for frontend
Route::prefix('api')->group(function (): void {
    Route::get('/products/search', [App\Http\Controllers\Api\ProductController::class, 'search'])->name('api.products.search');
    Route::get('/categories/tree', [App\Http\Controllers\Api\CategoryController::class, 'tree'])->name('api.categories.tree');
});
