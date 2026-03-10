<?php

declare(strict_types=1);

use App\Http\Controllers\Frontend\Partner\OrderDashboardController;
use Illuminate\Support\Facades\Route;

/*
 * |--------------------------------------------------------------------------
 * | Frontend Routes
 * |--------------------------------------------------------------------------
 * |
 * | Here is where you can register frontend routes for your application.
 * | These routes are loaded by the RouteServiceProvider and all of them will
 * | be assigned to the "web" middleware group.
 * |
 */

Route::middleware(['web'])->group(function () {
    // Homepage
    Route::get('/', \App\Livewire\Pages\Home::class)->name('home');

    // About page
    Route::view('/about', 'frontend.about.index')->name('frontend.about.index');

    // Products
    Route::prefix('products')->name('frontend.products.')->group(function () {
        Route::get('/', [App\Http\Controllers\Frontend\ProductController::class, 'index'])->name('index');
        Route::get('/search', [App\Http\Controllers\Frontend\ProductController::class, 'search'])->name('search');
        Route::get('/category/{category}', [App\Http\Controllers\Frontend\ProductController::class, 'byCategory'])->name('by-category');
        Route::get('/brand/{brand}', [App\Http\Controllers\Frontend\ProductController::class, 'byBrand'])->name('by-brand');
        Route::get('/{product:slug}', [App\Http\Controllers\Frontend\ProductController::class, 'show'])->name('show');
    });

    // Categories
    Route::prefix('categories')->name('frontend.categories.')->group(function () {
        Route::get('/', \App\Livewire\Pages\Category\Index::class)->name('index');
        Route::get('/{category}', [App\Http\Controllers\Frontend\CategoryController::class, 'show'])->name('show');
    });

    // Brands
    Route::prefix('brands')->name('frontend.brands.')->group(function () {
        Route::get('/', [App\Http\Controllers\Frontend\BrandController::class, 'index'])->name('index');
        Route::get('/{brand}', [App\Http\Controllers\Frontend\BrandController::class, 'show'])->name('show');
    });

    // Orders
    Route::middleware(['auth'])->prefix('orders')->name('frontend.orders.')->group(function () {
        Route::get('/', [App\Http\Controllers\Frontend\OrderController::class, 'index'])->name('index');
        Route::get('/{order}', [App\Http\Controllers\Frontend\OrderController::class, 'show'])->name('show');
        Route::post('/{order}/cancel', [App\Http\Controllers\Frontend\OrderController::class, 'cancel'])->name('cancel');
        Route::post('/{order}/return', [App\Http\Controllers\Frontend\OrderController::class, 'requestReturn'])->name('return');
    });

    // Cart
    Route::prefix('cart')->name('frontend.cart.')->group(function () {
        Route::get('/', \App\Livewire\Pages\Cart::class)->name('index');
        Route::post('/items', [App\Http\Controllers\Frontend\CartController::class, 'add'])->name('add');
        Route::match(['post', 'patch'], '/items/update/{cartItem?}', [App\Http\Controllers\Frontend\CartController::class, 'update'])->name('update');
        Route::match(['post', 'delete'], '/items/remove/{cartItem?}', [App\Http\Controllers\Frontend\CartController::class, 'remove'])->name('remove');
        Route::match(['post', 'delete'], '/clear', [App\Http\Controllers\Frontend\CartController::class, 'clear'])->name('clear');
    });

    // Checkout
    Route::middleware(['auth', 'throttle:frontend.checkout'])->prefix('checkout')->name('frontend.checkout.')->group(function () {
        Route::get('/', [App\Http\Controllers\Frontend\CheckoutController::class, 'index'])->name('index');
        Route::post('/process', [App\Http\Controllers\Frontend\CheckoutController::class, 'process'])->name('process');
        Route::get('/success', [App\Http\Controllers\Frontend\CheckoutController::class, 'success'])->name('success');
        Route::get('/cancel', [App\Http\Controllers\Frontend\CheckoutController::class, 'cancel'])->name('cancel');
        Route::get('/return/montonio', [\App\Http\Controllers\Payments\MontonioReturnController::class, 'handleReturn'])->name('return.montonio');
    });

    // User Profile - Redirects to new Livewire Account Dashboard
    Route::prefix('profile')->name('frontend.profile.')->group(function () {
        Route::get('/', function () {
            return redirect()->route('account.index');
        })->name('index');
        Route::get('/edit', function () {
            return redirect()->route('account.profile');
        })->name('edit');
        Route::get('/addresses', function () {
            return redirect()->route('account.addresses');
        })->name('addresses');

        // Data Privacy Routes
        Route::middleware(['auth'])->group(function () {
            Route::post('/data/export', [App\Http\Controllers\Frontend\DataPrivacyController::class, 'export'])->name('data.export');
            Route::delete('/data', [App\Http\Controllers\Frontend\DataPrivacyController::class, 'destroy'])->name('data.destroy');
        });
    });

    Route::middleware(['auth'])
        ->prefix('partner')
        ->name('frontend.partner.')
        ->group(function (): void {
            // Serve the partner order dashboard that consumes the new contract payload shape.
            Route::get('/orders', OrderDashboardController::class)->name('orders.index');
        });

    // Legacy user profile routes maintained for backwards compatibility with
    // existing blade templates that reference the "users.*" namespace.
    Route::middleware(['auth'])->prefix('users')->name('users.')->group(function () {
        Route::get('/profile', [App\Http\Controllers\Frontend\UserController::class, 'profile'])->name('profile');
        Route::post('/privacy-settings', [App\Http\Controllers\Frontend\UserController::class, 'updatePrivacySettings'])->name('privacy.update');
    });

    // Discounts & Coupons
    Route::prefix('discounts')->name('frontend.discounts.')->group(function () {
        Route::get('/', [App\Http\Controllers\Frontend\DiscountController::class, 'index'])->name('index');
        Route::get('/coupons', [App\Http\Controllers\Frontend\DiscountController::class, 'coupons'])->name('coupons');
        Route::post('/apply-coupon', [App\Http\Controllers\Frontend\DiscountController::class, 'applyCoupon'])->name('apply-coupon');
        Route::post('/remove-coupon', [App\Http\Controllers\Frontend\DiscountController::class, 'removeCoupon'])->name('remove-coupon');
        Route::get('/{discount:slug}', [App\Http\Controllers\Frontend\DiscountController::class, 'show'])->name('show');
    });

    // Collections
    Route::prefix('collections')->name('frontend.collections.')->group(function () {
        Route::get('/', [App\Http\Controllers\CollectionController::class, 'index'])->name('index');
        Route::get('/{collection}', [App\Http\Controllers\CollectionController::class, 'show'])->name('show');
    });

    // News & Content
    Route::prefix('news')->name('frontend.news.')->group(function () {
        Route::get('/', [App\Http\Controllers\NewsController::class, 'index'])->name('index');
        Route::get('/{slug}', [App\Http\Controllers\NewsController::class, 'show'])->name('show');
    });

    Route::prefix('posts')->name('frontend.posts.')->group(function () {
        Route::get('/', [App\Http\Controllers\PostController::class, 'index'])->name('index');
        Route::get('/{post}', [App\Http\Controllers\PostController::class, 'show'])->name('show');
    });

    // Search
    Route::prefix('search')->name('frontend.search.')->group(function () {
        Route::get('/', [App\Http\Controllers\Frontend\SearchController::class, 'index'])->name('index');
        Route::get('/suggestions', [App\Http\Controllers\Frontend\SearchController::class, 'suggestions'])->name('suggestions');
        Route::get('/autocomplete', [App\Http\Controllers\Frontend\SearchController::class, 'autocomplete'])->name('autocomplete');
    });

    // API Routes for AJAX
    Route::prefix('api')->name('frontend.api.')->group(function () {
        // Categories tree route is defined in routes/web.php to avoid conflicts
        Route::get('/cart/count', [App\Http\Controllers\Frontend\ApiController::class, 'getCartCount'])->name('cart.count');
        Route::get('/recently-viewed', [App\Http\Controllers\Frontend\ApiController::class, 'getRecentlyViewed'])->name('recently-viewed');
        Route::post('/recently-viewed/add', [App\Http\Controllers\Frontend\ApiController::class, 'addRecentlyViewed'])->name('recently-viewed.add');
    });

    // Contact
    Route::prefix('contact')->name('frontend.contact.')->group(function () {
        Route::get('/', [App\Http\Controllers\Frontend\ContactController::class, 'index'])->name('index');
        Route::post('/send', [App\Http\Controllers\Frontend\ContactController::class, 'send'])->name('send');
    });

    // Newsletter
    Route::prefix('newsletter')->name('frontend.newsletter.')->group(function () {
        Route::post('/subscribe', [App\Http\Controllers\Frontend\NewsletterController::class, 'subscribe'])->name('subscribe');
        Route::post('/unsubscribe', [App\Http\Controllers\Frontend\NewsletterController::class, 'unsubscribe'])->name('unsubscribe');
    });

    // Informational footer pages.
    Route::prefix('pagalba')->name('frontend.info.')->group(function () {
        Route::get('/duk', [App\Http\Controllers\Frontend\InfoPageController::class, 'show'])->defaults('page', 'faq')->name('faq');
        Route::get('/apmokejimo-budai', [App\Http\Controllers\Frontend\InfoPageController::class, 'show'])->defaults('page', 'payment-methods')->name('payment-methods');
    });

    Route::prefix('katalogas')->name('frontend.info.')->group(function () {
        Route::get('/populiariausios-prekes', [App\Http\Controllers\Frontend\InfoPageController::class, 'show'])->defaults('page', 'popular-products')->name('popular-products');
        Route::get('/statybines-medziagos', [App\Http\Controllers\Frontend\InfoPageController::class, 'show'])->defaults('page', 'building-materials')->name('building-materials');
        Route::get('/irankiai-ir-iranga', [App\Http\Controllers\Frontend\InfoPageController::class, 'show'])->defaults('page', 'tools-equipment')->name('tools-equipment');
        Route::get('/specialus-pasiulymai', [App\Http\Controllers\Frontend\InfoPageController::class, 'show'])->defaults('page', 'special-offers')->name('special-offers');
    });

    Route::get('/paslaugos/meistrams', [App\Http\Controllers\Frontend\InfoPageController::class, 'show'])
        ->defaults('page', 'services-for-craftsmen')
        ->name('frontend.info.services-for-craftsmen');

    // Sitemap
    Route::get('/sitemap.xml', [App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap');

    // Robots
    Route::get('/robots.txt', App\Http\Controllers\RobotsController::class)->name('robots');
});
