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

// Enhanced frontend routes
Route::get('/home', Pages\Home::class)->name('home');
Route::get('/products', Pages\ProductCatalog::class)->name('products.index');
Route::get('/categories', Pages\Category\Index::class)->name('categories.index');
Route::get('/categories/{slug}', Pages\Category\Show::class)->name('categories.show');
Route::get('/collections', Pages\Collection\Index::class)->name('collections.index');
Route::get('/collections/{slug}', Pages\Collection\Show::class)->name('collections.show');
Route::get('/products/{slug}', Pages\SingleProduct::class)->name('products.show');
Route::get('/search', Pages\Search::class)->name('search.index');
Route::get('/cart', Pages\Cart::class)->name('cart.index');
Route::get('/legal/{slug}', Pages\LegalPage::class)->name('legal.show');

// Auth routes
require __DIR__ . '/auth.php';

// Authenticated routes
Route::middleware('auth')->group(function (): void {
    Route::get('/checkout', Pages\Checkout::class)->name('checkout.index');
    Route::get('/account/orders', Pages\Account\Orders::class)->name('account.orders');
    Route::get('/account/addresses', Pages\Account\Addresses::class)->name('account.addresses');
});

// Impersonation routes for admin support
Route::middleware(['auth', 'permission:impersonate users'])->group(function () {
    Route::get('/admin/impersonate/{user}', function (App\Models\User $user) {
        if ($user->id !== auth()->id()) {
            session(['impersonating' => $user->id, 'original_user' => auth()->id()]);
            return redirect()->to('/')->with('success', __('Now impersonating :name', ['name' => $user->name]));
        }
        return redirect()->back()->with('error', __('Cannot impersonate yourself'));
    })->name('admin.impersonate');
    
    Route::get('/admin/stop-impersonating', function () {
        $originalUserId = session('original_user');
        session()->forget(['impersonating', 'original_user']);
        
        if ($originalUserId) {
            $originalUser = App\Models\User::find($originalUserId);
            if ($originalUser) {
                auth()->login($originalUser);
            }
        }
        
        return redirect()->to('/admin')->with('success', __('Stopped impersonating user'));
    })->name('admin.stop-impersonating');
});