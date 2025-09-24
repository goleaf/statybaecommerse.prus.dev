<?php

declare(strict_types=1);

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
    Route::get('/', [App\Http\Controllers\Frontend\HomeController::class, 'index'])->name('home');

    // Products
    Route::prefix('products')->name('frontend.products.')->group(function () {
        Route::get('/', [App\Http\Controllers\Frontend\ProductController::class, 'index'])->name('index');
        Route::get('/search', [App\Http\Controllers\Frontend\ProductController::class, 'search'])->name('search');
        Route::get('/category/{category}', [App\Http\Controllers\Frontend\ProductController::class, 'byCategory'])->name('by-category');
        Route::get('/brand/{brand}', [App\Http\Controllers\Frontend\ProductController::class, 'byBrand'])->name('by-brand');
        Route::get('/{product}', [App\Http\Controllers\Frontend\ProductController::class, 'show'])->name('show');
        Route::post('/{product}/review', [App\Http\Controllers\Frontend\ProductController::class, 'addReview'])->name('add-review');
    });

    // Categories
    Route::prefix('categories')->name('frontend.categories.')->group(function () {
        Route::get('/', [App\Http\Controllers\Frontend\CategoryController::class, 'index'])->name('index');
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
        Route::post('/{order}/return', [App\Http\Controllers\Frontend\OrderController::class, 'return'])->name('return');
    });

    // Cart
    Route::prefix('cart')->name('frontend.cart.')->group(function () {
        Route::get('/', [App\Http\Controllers\Frontend\CartController::class, 'index'])->name('index');
        Route::post('/add', [App\Http\Controllers\Frontend\CartController::class, 'add'])->name('add');
        Route::post('/update', [App\Http\Controllers\Frontend\CartController::class, 'update'])->name('update');
        Route::post('/remove', [App\Http\Controllers\Frontend\CartController::class, 'remove'])->name('remove');
        Route::post('/clear', [App\Http\Controllers\Frontend\CartController::class, 'clear'])->name('clear');
    });

    // Checkout
    Route::middleware(['auth'])->prefix('checkout')->name('frontend.checkout.')->group(function () {
        Route::get('/', [App\Http\Controllers\Frontend\CheckoutController::class, 'index'])->name('index');
        Route::post('/process', [App\Http\Controllers\Frontend\CheckoutController::class, 'process'])->name('process');
        Route::get('/success', [App\Http\Controllers\Frontend\CheckoutController::class, 'success'])->name('success');
        Route::get('/cancel', [App\Http\Controllers\Frontend\CheckoutController::class, 'cancel'])->name('cancel');
    });

    // User Profile
    Route::middleware(['auth'])->prefix('profile')->name('frontend.profile.')->group(function () {
        Route::get('/', [App\Http\Controllers\Frontend\ProfileController::class, 'index'])->name('index');
        Route::get('/edit', [App\Http\Controllers\Frontend\ProfileController::class, 'edit'])->name('edit');
        Route::put('/update', [App\Http\Controllers\Frontend\ProfileController::class, 'update'])->name('update');
        Route::get('/addresses', [App\Http\Controllers\Frontend\ProfileController::class, 'addresses'])->name('addresses');
        Route::post('/addresses', [App\Http\Controllers\Frontend\ProfileController::class, 'storeAddress'])->name('store-address');
        Route::put('/addresses/{address}', [App\Http\Controllers\Frontend\ProfileController::class, 'updateAddress'])->name('update-address');
        Route::delete('/addresses/{address}', [App\Http\Controllers\Frontend\ProfileController::class, 'deleteAddress'])->name('delete-address');
    });

    // Campaigns
    Route::prefix('campaigns')->name('frontend.campaigns.')->group(function () {
        Route::get('/', [App\Http\Controllers\Frontend\CampaignController::class, 'index'])->name('index');
        Route::get('/featured', [App\Http\Controllers\Frontend\CampaignController::class, 'featured'])->name('featured');
        Route::get('/search', [App\Http\Controllers\Frontend\CampaignController::class, 'search'])->name('search');
        Route::get('/type/{type}', [App\Http\Controllers\Frontend\CampaignController::class, 'byType'])->name('by-type');
        Route::get('/{campaign}', [App\Http\Controllers\Frontend\CampaignController::class, 'show'])->name('show');
        Route::post('/{campaign}/click', [App\Http\Controllers\Frontend\CampaignController::class, 'click'])->name('click');
        Route::post('/{campaign}/conversion', [App\Http\Controllers\Frontend\CampaignController::class, 'conversion'])->name('conversion');
    });

    // Discounts & Coupons
    Route::prefix('discounts')->name('frontend.discounts.')->group(function () {
        Route::get('/', [App\Http\Controllers\Frontend\DiscountController::class, 'index'])->name('index');
        Route::get('/coupons', [App\Http\Controllers\Frontend\DiscountController::class, 'coupons'])->name('coupons');
        Route::post('/apply-coupon', [App\Http\Controllers\Frontend\DiscountController::class, 'applyCoupon'])->name('apply-coupon');
        Route::post('/remove-coupon', [App\Http\Controllers\Frontend\DiscountController::class, 'removeCoupon'])->name('remove-coupon');
    });

    // Collections
    Route::prefix('collections')->name('frontend.collections.')->group(function () {
        Route::get('/', [App\Http\Controllers\Frontend\CollectionController::class, 'index'])->name('index');
        Route::get('/{collection}', [App\Http\Controllers\Frontend\CollectionController::class, 'show'])->name('show');
    });

    // News & Content
    Route::prefix('news')->name('frontend.news.')->group(function () {
        Route::get('/', [App\Http\Controllers\Frontend\NewsController::class, 'index'])->name('index');
        Route::get('/{news}', [App\Http\Controllers\Frontend\NewsController::class, 'show'])->name('show');
    });

    Route::prefix('posts')->name('frontend.posts.')->group(function () {
        Route::get('/', [App\Http\Controllers\Frontend\PostController::class, 'index'])->name('index');
        Route::get('/{post}', [App\Http\Controllers\Frontend\PostController::class, 'show'])->name('show');
    });

    // Legal Pages
    Route::prefix('legal')->name('frontend.legal.')->group(function () {
        Route::get('/privacy', [App\Http\Controllers\Frontend\LegalController::class, 'privacy'])->name('privacy');
        Route::get('/terms', [App\Http\Controllers\Frontend\LegalController::class, 'terms'])->name('terms');
        Route::get('/cookies', [App\Http\Controllers\Frontend\LegalController::class, 'cookies'])->name('cookies');
        Route::get('/returns', [App\Http\Controllers\Frontend\LegalController::class, 'returns'])->name('returns');
    });

    // Search
    Route::prefix('search')->name('frontend.search.')->group(function () {
        Route::get('/', [App\Http\Controllers\Frontend\SearchController::class, 'index'])->name('index');
        Route::get('/suggestions', [App\Http\Controllers\Frontend\SearchController::class, 'suggestions'])->name('suggestions');
        Route::get('/autocomplete', [App\Http\Controllers\Frontend\SearchController::class, 'autocomplete'])->name('autocomplete');
    });

    // API Routes for AJAX
    Route::prefix('api')->name('frontend.api.')->group(function () {
        Route::get('/products/search', [App\Http\Controllers\Frontend\ApiController::class, 'searchProducts'])->name('products.search');
        Route::get('/categories/tree', [App\Http\Controllers\Frontend\ApiController::class, 'getCategoryTree'])->name('categories.tree');
        Route::get('/cart/count', [App\Http\Controllers\Frontend\ApiController::class, 'getCartCount'])->name('cart.count');
        Route::get('/wishlist/count', [App\Http\Controllers\Frontend\ApiController::class, 'getWishlistCount'])->name('wishlist.count');
        Route::post('/wishlist/toggle', [App\Http\Controllers\Frontend\ApiController::class, 'toggleWishlist'])->name('wishlist.toggle');
        Route::get('/recently-viewed', [App\Http\Controllers\Frontend\ApiController::class, 'getRecentlyViewed'])->name('recently-viewed');
        Route::post('/recently-viewed/add', [App\Http\Controllers\Frontend\ApiController::class, 'addRecentlyViewed'])->name('recently-viewed.add');
    });

    // Wishlist
    Route::middleware(['auth'])->prefix('wishlist')->name('frontend.wishlist.')->group(function () {
        Route::get('/', [App\Http\Controllers\Frontend\WishlistController::class, 'index'])->name('index');
        Route::post('/add', [App\Http\Controllers\Frontend\WishlistController::class, 'add'])->name('add');
        Route::delete('/remove', [App\Http\Controllers\Frontend\WishlistController::class, 'remove'])->name('remove');
        Route::delete('/clear', [App\Http\Controllers\Frontend\WishlistController::class, 'clear'])->name('clear');
    });

    // Reviews
    Route::prefix('reviews')->name('frontend.reviews.')->group(function () {
        Route::post('/{product}', [App\Http\Controllers\Frontend\ReviewController::class, 'store'])->name('store');
        Route::put('/{review}', [App\Http\Controllers\Frontend\ReviewController::class, 'update'])->name('update');
        Route::delete('/{review}', [App\Http\Controllers\Frontend\ReviewController::class, 'destroy'])->name('destroy');
        Route::post('/{review}/like', [App\Http\Controllers\Frontend\ReviewController::class, 'like'])->name('like');
        Route::post('/{review}/report', [App\Http\Controllers\Frontend\ReviewController::class, 'report'])->name('report');
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

    // Sitemap
    Route::get('/sitemap.xml', [App\Http\Controllers\Frontend\SitemapController::class, 'index'])->name('sitemap');

    // Robots
    Route::get('/robots.txt', [App\Http\Controllers\Frontend\RobotsController::class, 'index'])->name('robots');
});
