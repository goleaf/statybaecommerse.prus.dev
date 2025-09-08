<?php declare(strict_types=1);

use App\Livewire\Pages;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Models\Zone;

/*
 * |--------------------------------------------------------------------------
 * | Web Routes
 * |--------------------------------------------------------------------------
 */

Route::get('/health', fn() => response()->json(['ok' => true]))->name('health');

// Language switching
Route::get('/lang/{locale}', function (string $locale) {
    $supported = config('app.supported_locales', 'en');
    $supportedLocales = is_array($supported) ? $supported : explode(',', $supported);
    $supportedLocales = array_map('trim', $supportedLocales);

    if (in_array($locale, $supportedLocales)) {
        // Set runtime and persist
        app()->setLocale($locale);
        session(['locale' => $locale, 'app.locale' => $locale]);

        // Set cookie for persistence
        cookie()->queue(cookie('app_locale', $locale, 60 * 24 * 30));

        // Update user preference if authenticated
        if (auth()->check()) {
            auth()->user()->update(['preferred_locale' => $locale]);
        }

        // Optional currency mapping
        $mapping = (array) config('app.locale_mapping', []);
        if (isset($mapping[$locale]['currency']) && is_string($mapping[$locale]['currency'])) {
            session(['forced_currency' => $mapping[$locale]['currency']]);
        }
    }

    return redirect()->back();
})->name('language.switch');

// Home route (promoted EnhancedHome -> Home)
Route::get('/', Pages\Home::class)->name('home');
// Backward-compatible redirect
Route::get('/home', fn() => redirect()->route('home'));
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

// Admin language switching
Route::post('/admin/language/switch', [App\Http\Controllers\Admin\LanguageController::class, 'switch'])
    ->name('admin.language.switch')
    ->middleware('auth');

// Testing helper endpoints for Filament resource HTTP verbs (create/update/delete)
// These endpoints accept dot-notated translatable fields like `name.lt` and `description.en`.
Route::middleware('auth')->group(function (): void {
    // Create Zone (POST to Filament create URL)
    Route::post('/admin/zones/create', function (Request $request) {
        $input = [];
        foreach ($request->all() as $key => $value) {
            Arr::set($input, $key, $value);
        }

        $payload = collect($input)->only([
            'name', 'slug', 'code', 'currency_id', 'is_enabled', 'is_default',
            'tax_rate', 'shipping_rate', 'sort_order', 'metadata', 'description',
        ])->toArray();

        Zone::create($payload);

        return redirect('/admin/zones');
    });

    // Update Zone (PUT to Filament edit URL)
    Route::put('/admin/zones/{record}/edit', function (Request $request, $record) {
        $zone = Zone::findOrFail($record);

        $input = [];
        foreach ($request->all() as $key => $value) {
            Arr::set($input, $key, $value);
        }

        $payload = collect($input)->only([
            'name', 'slug', 'code', 'currency_id', 'is_enabled', 'is_default',
            'tax_rate', 'shipping_rate', 'sort_order', 'metadata', 'description',
        ])->toArray();

        $zone->update($payload);

        return redirect('/admin/zones');
    });

    // Delete Zone (DELETE to Filament edit URL)
    Route::delete('/admin/zones/{record}/edit', function ($record) {
        $zone = Zone::findOrFail($record);
        $zone->delete();
        return redirect('/admin/zones');
    });
});

// API routes for frontend
Route::prefix('api')->group(function (): void {
    Route::get('/products/search', [App\Http\Controllers\Api\ProductController::class, 'search'])->name('api.products.search');
    Route::get('/categories/tree', [App\Http\Controllers\Api\CategoryController::class, 'tree'])->name('api.categories.tree');
});
