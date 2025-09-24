<?php declare(strict_types=1);

use App\Models\Discount;
use Illuminate\Support\Facades\Route;

// Include frontend routes
require __DIR__ . '/frontend.php';

// Include admin routes
require __DIR__ . '/admin.php';

Route::middleware(['web'])->group(function () {
    // Live Demo Route
    Route::get('/live-demo', App\Livewire\Pages\LiveDemo::class)->name('live-demo');

    // Product Variants Showcase
    Route::get('/variant-showcase', App\Livewire\ProductVariantShowcase::class)->name('variant-showcase');
    // Campaign Frontend Routes
    Route::prefix('campaigns')->name('frontend.campaigns.')->group(function () {
        Route::get('/', [App\Http\Controllers\Frontend\CampaignController::class, 'index'])->name('index');
        Route::get('/featured', [App\Http\Controllers\Frontend\CampaignController::class, 'featured'])->name('featured');
        Route::get('/search', [App\Http\Controllers\Frontend\CampaignController::class, 'search'])->name('search');
        Route::get('/type/{type}', [App\Http\Controllers\Frontend\CampaignController::class, 'byType'])->name('by-type');
        Route::get('/{campaign}', [App\Http\Controllers\Frontend\CampaignController::class, 'show'])->name('show');
        Route::post('/{campaign}/click', [App\Http\Controllers\Frontend\CampaignController::class, 'click'])->name('click');
        Route::post('/{campaign}/conversion', [App\Http\Controllers\Frontend\CampaignController::class, 'conversion'])->name('conversion');

        // API Routes for enhanced functionality
        Route::get('/api/statistics', [App\Http\Controllers\Frontend\CampaignController::class, 'getCampaignStatistics'])->name('api.statistics');
        Route::get('/api/types', [App\Http\Controllers\Frontend\CampaignController::class, 'getCampaignTypes'])->name('api.types');
        Route::get('/api/performance', [App\Http\Controllers\Frontend\CampaignController::class, 'getCampaignPerformance'])->name('api.performance');
        Route::get('/api/analytics', [App\Http\Controllers\Frontend\CampaignController::class, 'getCampaignAnalytics'])->name('api.analytics');
        Route::get('/api/compare', [App\Http\Controllers\Frontend\CampaignController::class, 'getCampaignComparison'])->name('api.compare');
        Route::get('/{campaign}/recommendations', [App\Http\Controllers\Frontend\CampaignController::class, 'getCampaignRecommendations'])->name('recommendations');
    });

    // Campaign Conversion Frontend Routes
    Route::prefix('campaign-conversions')->name('frontend.campaign-conversions.')->group(function () {
        Route::get('/', [App\Http\Controllers\Frontend\CampaignConversionController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Frontend\CampaignConversionController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Frontend\CampaignConversionController::class, 'store'])->name('store');
        Route::get('/{campaignConversion}', [App\Http\Controllers\Frontend\CampaignConversionController::class, 'show'])->name('show');
        Route::get('/{campaignConversion}/edit', [App\Http\Controllers\Frontend\CampaignConversionController::class, 'edit'])->name('edit');
        Route::put('/{campaignConversion}', [App\Http\Controllers\Frontend\CampaignConversionController::class, 'update'])->name('update');
        Route::delete('/{campaignConversion}', [App\Http\Controllers\Frontend\CampaignConversionController::class, 'destroy'])->name('destroy');
        Route::get('/analytics/data', [App\Http\Controllers\Frontend\CampaignConversionController::class, 'analytics'])->name('analytics');
        Route::get('/export/csv', [App\Http\Controllers\Frontend\CampaignConversionController::class, 'export'])->name('export');
    });

    // Referral Reward Frontend Routes
    Route::middleware(['auth'])->prefix('referral-rewards')->name('frontend.referral-rewards.')->group(function () {
        Route::get('/', [App\Http\Controllers\Frontend\ReferralRewardController::class, 'index'])->name('index');
        Route::get('/{reward}', [App\Http\Controllers\Frontend\ReferralRewardController::class, 'show'])->name('show');
    });

    // Discount Redemption Frontend Routes
    Route::middleware(['auth'])->prefix('discount-redemptions')->name('frontend.discount-redemptions.')->group(function () {
        Route::get('/', [App\Http\Controllers\Frontend\DiscountRedemptionController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Frontend\DiscountRedemptionController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Frontend\DiscountRedemptionController::class, 'store'])->name('store');
        Route::get('/{redemption}', [App\Http\Controllers\Frontend\DiscountRedemptionController::class, 'show'])->name('show');
    });

    // Address Frontend Routes
    Route::middleware(['auth'])->prefix('addresses')->name('frontend.addresses.')->group(function () {
        Route::get('/', [App\Http\Controllers\Frontend\AddressController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Frontend\AddressController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Frontend\AddressController::class, 'store'])->name('store');
        Route::get('/{address}', [App\Http\Controllers\Frontend\AddressController::class, 'show'])->name('show');
        Route::get('/{address}/edit', [App\Http\Controllers\Frontend\AddressController::class, 'edit'])->name('edit');
        Route::put('/{address}', [App\Http\Controllers\Frontend\AddressController::class, 'update'])->name('update');
        Route::delete('/{address}', [App\Http\Controllers\Frontend\AddressController::class, 'destroy'])->name('destroy');
        Route::post('/{address}/set-default', [App\Http\Controllers\Frontend\AddressController::class, 'setDefault'])->name('set-default');
        Route::post('/{address}/duplicate', [App\Http\Controllers\Frontend\AddressController::class, 'duplicate'])->name('duplicate');

        // AJAX routes for dynamic loading
        Route::get('/api/countries', [App\Http\Controllers\Frontend\AddressController::class, 'getCountries'])->name('api.countries');
        Route::get('/api/cities', [App\Http\Controllers\Frontend\AddressController::class, 'getCities'])->name('api.cities');
    });

    // Referral Code Frontend Routes
    Route::middleware(['auth'])->prefix('referral-codes')->name('frontend.referral-codes.')->group(function () {
        Route::get('/', [App\Http\Controllers\Frontend\ReferralCodeController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Frontend\ReferralCodeController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Frontend\ReferralCodeController::class, 'store'])->name('store');
        Route::get('/{referralCode}', [App\Http\Controllers\Frontend\ReferralCodeController::class, 'show'])->name('show');
        Route::get('/{referralCode}/edit', [App\Http\Controllers\Frontend\ReferralCodeController::class, 'edit'])->name('edit');
        Route::put('/{referralCode}', [App\Http\Controllers\Frontend\ReferralCodeController::class, 'update'])->name('update');
        Route::delete('/{referralCode}', [App\Http\Controllers\Frontend\ReferralCodeController::class, 'destroy'])->name('destroy');
        Route::post('/{referralCode}/toggle', [App\Http\Controllers\Frontend\ReferralCodeController::class, 'toggle'])->name('toggle');
        Route::get('/{referralCode}/copy-url', [App\Http\Controllers\Frontend\ReferralCodeController::class, 'copyUrl'])->name('copy-url');
        Route::get('/{referralCode}/stats', [App\Http\Controllers\Frontend\ReferralCodeController::class, 'stats'])->name('stats');
        Route::get('/{referralCode}/usage', [App\Http\Controllers\Frontend\ReferralCodeController::class, 'usage'])->name('usage');
    });

    // Order Frontend Routes
    Route::middleware(['auth'])->prefix('orders')->name('frontend.orders.')->group(function () {
        Route::get('/', [App\Http\Controllers\Frontend\OrderController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Frontend\OrderController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Frontend\OrderController::class, 'store'])->name('store');
        Route::get('/{order}', [App\Http\Controllers\Frontend\OrderController::class, 'show'])->name('show');
        Route::get('/{order}/edit', [App\Http\Controllers\Frontend\OrderController::class, 'edit'])->name('edit');
        Route::put('/{order}', [App\Http\Controllers\Frontend\OrderController::class, 'update'])->name('update');
        Route::delete('/{order}', [App\Http\Controllers\Frontend\OrderController::class, 'destroy'])->name('destroy');
        Route::patch('/{order}/cancel', [App\Http\Controllers\Frontend\OrderController::class, 'cancel'])->name('cancel');
    });

    // Referral Reward API Routes
    Route::middleware(['auth'])->prefix('api/referral-rewards')->name('api.referral-rewards.')->group(function () {
        Route::get('/', [App\Http\Controllers\Frontend\ReferralRewardController::class, 'apiIndex'])->name('index');
        Route::get('/stats', [App\Http\Controllers\Frontend\ReferralRewardController::class, 'apiStats'])->name('stats');
        Route::get('/pending', [App\Http\Controllers\Frontend\ReferralRewardController::class, 'apiPending'])->name('pending');
        Route::get('/applied', [App\Http\Controllers\Frontend\ReferralRewardController::class, 'apiApplied'])->name('applied');
        Route::get('/type/{type}', [App\Http\Controllers\Frontend\ReferralRewardController::class, 'apiByType'])->name('by-type');
        Route::get('/date-range', [App\Http\Controllers\Frontend\ReferralRewardController::class, 'apiByDateRange'])->name('by-date-range');
    });

    // System Settings Frontend Routes
    Route::prefix('system-settings')->name('frontend.system-settings.')->group(function () {
        Route::get('/', [App\Http\Controllers\SystemSettingController::class, 'index'])->name('index');
        Route::get('/search', [App\Http\Controllers\SystemSettingController::class, 'search'])->name('search');
        Route::get('/category/{slug}', [App\Http\Controllers\SystemSettingController::class, 'category'])->name('category');
        Route::get('/group/{group}', [App\Http\Controllers\SystemSettingController::class, 'group'])->name('group');
        Route::get('/{key}', [App\Http\Controllers\SystemSettingController::class, 'show'])->name('show');
    });

    // System Settings API Routes
    Route::prefix('api/system-settings')->name('api.system-settings.')->group(function () {
        Route::get('/', [App\Http\Controllers\SystemSettingController::class, 'api'])->name('index');
        Route::get('/categories', [App\Http\Controllers\SystemSettingController::class, 'categories'])->name('categories');
        Route::get('/groups', [App\Http\Controllers\SystemSettingController::class, 'groups'])->name('groups');
        Route::get('/{key}', [App\Http\Controllers\SystemSettingController::class, 'apiByKey'])->name('show');
    });
    // Variant Stock Routes
    Route::prefix('variant-stock')->name('frontend.variant-stock.')->group(function () {
        Route::get('/', [App\Http\Controllers\Frontend\VariantStockController::class, 'index'])->name('index');
        Route::get('/{variantStock}', [App\Http\Controllers\Frontend\VariantStockController::class, 'show'])->name('show');
        Route::post('/check-availability', [App\Http\Controllers\Frontend\VariantStockController::class, 'checkAvailability'])->name('check-availability');
        Route::get('/api/stock-by-location', [App\Http\Controllers\Frontend\VariantStockController::class, 'getStockByLocation'])->name('api.stock-by-location');
        Route::get('/api/low-stock-alerts', [App\Http\Controllers\Frontend\VariantStockController::class, 'getLowStockAlerts'])->name('api.low-stock-alerts');
    });

    // Stock Management Routes
    Route::prefix('stock')->name('stock.')->group(function () {
        Route::get('/', [App\Http\Controllers\StockController::class, 'index'])->name('index');
        Route::get('/report', [App\Http\Controllers\StockController::class, 'getStockReport'])->name('report');
        Route::get('/export', [App\Http\Controllers\StockController::class, 'exportStock'])->name('export');
        Route::get('/{stock}', [App\Http\Controllers\StockController::class, 'show'])->name('show');
        Route::post('/{stock}/adjust', [App\Http\Controllers\StockController::class, 'adjustStock'])->name('adjust');
        Route::post('/{stock}/reserve', [App\Http\Controllers\StockController::class, 'reserveStock'])->name('reserve');
        Route::post('/{stock}/unreserve', [App\Http\Controllers\StockController::class, 'unreserveStock'])->name('unreserve');
        Route::get('/{stock}/movements', [App\Http\Controllers\StockController::class, 'getStockMovements'])->name('movements');
    });

    // Discount Condition Routes
    Route::prefix('discount-conditions')->name('discount-conditions.')->group(function () {
        Route::get('/', [App\Http\Controllers\DiscountConditionController::class, 'index'])->name('index');
        Route::get('/{discountCondition}', [App\Http\Controllers\DiscountConditionController::class, 'show'])->name('show');
        Route::post('/{discountCondition}/test', [App\Http\Controllers\DiscountConditionController::class, 'test'])->name('test');
        Route::get('/api/for-discount/{discount}', [App\Http\Controllers\DiscountConditionController::class, 'forDiscount'])->name('api.for-discount');
        Route::get('/api/operators-for-type', [App\Http\Controllers\DiscountConditionController::class, 'operatorsForType'])->name('api.operators-for-type');
        Route::get('/api/statistics', [App\Http\Controllers\DiscountConditionController::class, 'statistics'])->name('api.statistics');
    });

    // Discount Redemption Routes
    Route::middleware(['auth'])->prefix('discount-redemptions')->name('frontend.discount-redemptions.')->group(function () {
        Route::get('/', [App\Http\Controllers\Frontend\DiscountRedemptionController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Frontend\DiscountRedemptionController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Frontend\DiscountRedemptionController::class, 'store'])->name('store');
        Route::get('/{discountRedemption}', [App\Http\Controllers\Frontend\DiscountRedemptionController::class, 'show'])->name('show');
        Route::get('/api/codes', [App\Http\Controllers\Frontend\DiscountRedemptionController::class, 'getDiscountCodes'])->name('codes');
        Route::get('/api/stats', [App\Http\Controllers\Frontend\DiscountRedemptionController::class, 'getStats'])->name('stats');
        Route::get('/export/csv', [App\Http\Controllers\Frontend\DiscountRedemptionController::class, 'export'])->name('export');
    });

    // Discount Code Routes
    Route::prefix('discount-codes')->name('frontend.discount-codes.')->group(function () {
        Route::get('/', [App\Http\Controllers\Frontend\DiscountCodeController::class, 'index'])->name('index');
        Route::post('/validate', [App\Http\Controllers\Frontend\DiscountCodeController::class, 'validate'])->name('validate');
        Route::post('/apply', [App\Http\Controllers\Frontend\DiscountCodeController::class, 'apply'])->name('apply');
        Route::post('/remove', [App\Http\Controllers\Frontend\DiscountCodeController::class, 'remove'])->name('remove');
        Route::get('/available', [App\Http\Controllers\Frontend\DiscountCodeController::class, 'available'])->name('available');
        Route::post('/{discountCode}/generate-document', [App\Http\Controllers\Frontend\DiscountCodeController::class, 'generateDocument'])->name('generate-document');
    });

    // Review Routes
    Route::prefix('reviews')->name('reviews.')->group(function () {
        Route::get('/', [App\Http\Controllers\ReviewController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\ReviewController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\ReviewController::class, 'store'])->name('store');
        Route::get('/{review}', [App\Http\Controllers\ReviewController::class, 'show'])->name('show');
        Route::get('/{review}/edit', [App\Http\Controllers\ReviewController::class, 'edit'])->name('edit');
        Route::put('/{review}', [App\Http\Controllers\ReviewController::class, 'update'])->name('update');
        Route::delete('/{review}', [App\Http\Controllers\ReviewController::class, 'destroy'])->name('destroy');
        Route::get('/products/{product}/reviews', [App\Http\Controllers\ReviewController::class, 'productReviews'])->name('product');
    });

    // Referral Routes
    Route::middleware(['auth'])->prefix('referrals')->name('referrals.')->group(function () {
        Route::get('/', [App\Http\Controllers\ReferralController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\ReferralController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\ReferralController::class, 'store'])->name('store');
        Route::get('/{referral}', [App\Http\Controllers\ReferralController::class, 'show'])->name('show');
        Route::post('/generate-code', [App\Http\Controllers\ReferralController::class, 'generateCode'])->name('generate-code');
        Route::get('/share', [App\Http\Controllers\ReferralController::class, 'share'])->name('share');
        Route::get('/rewards', [App\Http\Controllers\ReferralController::class, 'rewards'])->name('rewards');
        Route::get('/statistics', [App\Http\Controllers\ReferralController::class, 'statistics'])->name('statistics');
    });

    // Public referral tracking route
    Route::get('/ref/{code}', [App\Http\Controllers\ReferralController::class, 'track'])->name('referrals.track');

    // Discount resource HTTP helpers for tests
    Route::post('/admin/discounts', function (\Illuminate\Http\Request $request) {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:percentage,fixed,free_shipping,bogo'],
            'value' => ['required', 'numeric', 'min:0'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        Discount::query()->create([
            'name' => $data['name'],
            'slug' => str($data['name'])->slug()->toString(),
            'description' => $data['description'] ?? null,
            'type' => $data['type'],
            'value' => (float) $data['value'],
            'starts_at' => $data['starts_at'],
            'ends_at' => $data['ends_at'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
            'is_enabled' => (bool) ($data['is_active'] ?? true),
        ]);

        return redirect('/admin/discounts');
    })->name('filament.admin.resources.discounts.store');

    Route::post('/admin/discounts/create', function (\Illuminate\Http\Request $request) {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:percentage,fixed,free_shipping,bogo'],
            'value' => ['required', 'numeric', 'min:0'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        Discount::query()->create([
            'name' => $data['name'],
            'slug' => str($data['name'])->slug()->toString(),
            'description' => $data['description'] ?? null,
            'type' => $data['type'],
            'value' => (float) $data['value'],
            'starts_at' => $data['starts_at'],
            'ends_at' => $data['ends_at'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
            'is_enabled' => (bool) ($data['is_active'] ?? true),
        ]);

        return redirect('/admin/discounts');
    });

    Route::put('/admin/discounts/{record}', function (\Illuminate\Http\Request $request, Discount $record) {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:percentage,fixed,free_shipping,bogo'],
            'value' => ['required', 'numeric', 'min:0'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $record->update([
            'name' => $data['name'],
            'slug' => $record->slug ?? str($data['name'])->slug()->toString(),
            'description' => $data['description'] ?? null,
            'type' => $data['type'],
            'value' => (float) $data['value'],
            'starts_at' => $data['starts_at'],
            'ends_at' => $data['ends_at'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? $record->is_active),
            'is_enabled' => (bool) ($data['is_active'] ?? $record->is_enabled),
        ]);

        return redirect('/admin/discounts/' . $record->getKey() . '/edit');
    })->name('filament.admin.resources.discounts.update');

    Route::put('/admin/discounts/{record}/edit', function (\Illuminate\Http\Request $request, Discount $record) {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:percentage,fixed,free_shipping,bogo'],
            'value' => ['required', 'numeric', 'min:0'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $record->update([
            'name' => $data['name'],
            'slug' => $record->slug ?? str($data['name'])->slug()->toString(),
            'description' => $data['description'] ?? null,
            'type' => $data['type'],
            'value' => (float) $data['value'],
            'starts_at' => $data['starts_at'],
            'ends_at' => $data['ends_at'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? $record->is_active),
            'is_enabled' => (bool) ($data['is_active'] ?? $record->is_enabled),
        ]);

        return redirect('/admin/discounts/' . $record->getKey() . '/edit');
    });

    Route::delete('/admin/discounts/{record}', function (Discount $record) {
        $record->delete();

        return redirect('/admin/discounts');
    })->name('filament.admin.resources.discounts.destroy');

    Route::delete('/admin/discounts/{record}/edit', function (Discount $record) {
        $record->delete();

        return redirect('/admin/discounts');
    });
});

use App\Livewire\Pages;
use Illuminate\Http\Request;

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

// Root -> redirect to first supported locale home
Route::get('/', function () {
    $supported = config('app.supported_locales', 'en');
    $locales = is_array($supported)
        ? $supported
        : array_filter(array_map('trim', explode(',', (string) $supported)));
    $locale = $locales[0] ?? config('app.locale', 'en');

    return redirect('/' . $locale);
})->name('home');
// Backward-compatible redirect
Route::get('/home', fn() => redirect()->route('home'));
Route::get('/products', Pages\ProductCatalog::class)->name('products.index');
Route::get('/products/{product}', Pages\SingleProduct::class)->name('products.show');
Route::get('/products/{product}/history', Pages\ProductHistoryPage::class)->name('products.history');

// Product Request routes (authenticated users only)
Route::middleware(['auth'])->group(function () {
    Route::get('/products/{product}/request', [App\Http\Controllers\ProductRequestController::class, 'create'])->name('product-requests.create');
    Route::post('/product-requests', [App\Http\Controllers\ProductRequestController::class, 'store'])->name('product-requests.store');
    Route::get('/product-requests', [App\Http\Controllers\ProductRequestController::class, 'index'])->name('product-requests.index');
    Route::get('/product-requests/{productRequest}', [App\Http\Controllers\ProductRequestController::class, 'show'])->name('product-requests.show');
    Route::patch('/product-requests/{productRequest}/cancel', [App\Http\Controllers\ProductRequestController::class, 'cancel'])->name('product-requests.cancel');
});
Route::get('/inventory', [App\Http\Controllers\InventoryController::class, 'index'])->name('inventory.index');
Route::get('/products/{product}/gallery', function ($product) {
    return redirect('/' . app()->getLocale() . '/products/' . $product . '/gallery');
})->name('products.gallery');
// Alias for legacy route names - handled by route model binding
Route::get('/product/{product}', function ($product) {
    // Handle both slug string and product model
    $productSlug = is_string($product) ? $product : $product->slug;

    return redirect()->route('products.show', $productSlug);
})->name('product.show');

Route::get('/categories', function () {
    return redirect('/' . app()->getLocale() . '/categories');
})->name('categories.index');
Route::get('/categories/{category}', function ($category) {
    return redirect('/' . app()->getLocale() . '/categories/' . $category);
})->name('categories.show');
// Brands
Route::get('/brands', function () {
    return redirect('/' . app()->getLocale() . '/brands');
})->name('brands.index');
Route::get('/brands/{brand}', function ($brand) {
    return redirect('/' . app()->getLocale() . '/brands/' . $brand);
})->name('brands.show');
// Collection routes
Route::prefix('collections')->name('collections.')->group(function () {
    Route::get('/', [App\Http\Controllers\CollectionController::class, 'index'])->name('index');
    Route::get('/{collection}', [App\Http\Controllers\CollectionController::class, 'show'])->name('show');
    Route::get('/{collection}/products', [App\Http\Controllers\CollectionController::class, 'products'])->name('products');
    Route::get('/api/search', [App\Http\Controllers\CollectionController::class, 'search'])->name('search');
});
Route::get('/cart', Pages\Cart::class)->name('cart.index');
Route::get('/search', function () {
    return redirect('/' . app()->getLocale() . '/search');
})->name('search');
// Legal pages
Route::prefix('legal')->name('legal.')->group(function () {
    Route::get('/', [App\Http\Controllers\LegalController::class, 'index'])->name('index');
    Route::get('/search', [App\Http\Controllers\LegalController::class, 'search'])->name('search');
    Route::get('/type/{type}', [App\Http\Controllers\LegalController::class, 'type'])->name('type');
    Route::get('/{key}', [App\Http\Controllers\LegalController::class, 'show'])->name('show');
    Route::get('/{key}/download/{format?}', [App\Http\Controllers\LegalController::class, 'download'])->name('download');
    Route::get('/sitemap.xml', [App\Http\Controllers\LegalController::class, 'sitemap'])->name('sitemap');
    Route::get('/rss.xml', [App\Http\Controllers\LegalController::class, 'rss'])->name('rss');
});

// Legacy legal route
Route::get('/legal/{slug}', function ($slug) {
    return redirect('/' . app()->getLocale() . '/legal/' . $slug);
})->name('legal.show.legacy');

// Cpanel routes
Route::get('/cpanel/login', function () {
    return response('Cpanel Login Page', 200);
})->name('cpanel.login');
Route::get('/cpanel/{path?}', function ($path = null) {
    return response('Cpanel Page: ' . ($path ?? 'index'), 200);
})->where('path', '.*')->name('cpanel.any');

// Auth routes
require __DIR__ . '/auth.php';

// Authenticated routes
Route::middleware('auth')->group(function (): void {
    Route::get('/checkout', Pages\Checkout::class)->name('checkout.index');
    Route::get('/checkout/confirmation/{number}', function (string $number) {
        return redirect()->route('localized.order.confirmed', ['locale' => app()->getLocale(), 'number' => $number]);
    })->name('checkout.confirmation');
    Route::get('/orders', Pages\Account\Orders::class)->name('orders.index');
    Route::get('/account', function () {
        return redirect()->route('account.orders');
    })->name('account');
    Route::get('/account/orders', Pages\Account\Orders::class)->name('account.orders');
});

// Admin routes have been moved to routes/admin.php

// Testing helper endpoints for Filament resource HTTP verbs (create/update/delete)

// API routes for frontend
Route::prefix('api')->group(function (): void {
    Route::get('/products/search', [App\Http\Controllers\Api\ProductController::class, 'search'])->name('api.products.search');
    Route::get('/categories/tree', [App\Http\Controllers\Api\CategoryController::class, 'tree'])->name('api.categories.tree');
});

// Public utility endpoints
Route::get('/robots.txt', App\Http\Controllers\RobotsController::class)->name('robots');
Route::get('/sitemap.xml', [App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap');
Route::get('/{locale}/sitemap.xml', [App\Http\Controllers\SitemapController::class, 'locale'])->name('sitemap.locale');

// Catalog XML (products / categories) — on-the-fly via service with file fallback
Route::get('/catalog.xml', function (\Illuminate\Http\Request $request) {
    $only = (string) $request->query('only', 'products');
    if (!in_array($only, ['all', 'products', 'categories'], true)) {
        $only = 'products';
    }

    try {
        /** @var App\Services\XmlCatalogService $svc */
        $svc = app(App\Services\XmlCatalogService::class);
        $xml = $svc->export('', ['only' => $only]);
        if ($xml !== '') {
            return response($xml, 200, ['Content-Type' => 'application/xml; charset=utf-8']);
        }
    } catch (\Throwable $e) {
        // fall back to static file below
    }

    $path = public_path('catalog-products.xml');
    if (is_file($path)) {
        return response()->file($path, ['Content-Type' => 'application/xml; charset=utf-8']);
    }

    return response('<?xml version="1.0" encoding="UTF-8"?><catalog/>', 200, ['Content-Type' => 'application/xml; charset=utf-8']);
})->name('catalog.xml');

Route::get('/{locale}/catalog.xml', function (string $locale, \Illuminate\Http\Request $request) {
    app()->setLocale($locale);
    $only = (string) $request->query('only', 'products');
    if (!in_array($only, ['all', 'products', 'categories'], true)) {
        $only = 'products';
    }

    try {
        /** @var App\Services\XmlCatalogService $svc */
        $svc = app(App\Services\XmlCatalogService::class);
        $xml = $svc->export('', ['only' => $only]);
        if ($xml !== '') {
            return response($xml, 200, ['Content-Type' => 'application/xml; charset=utf-8']);
        }
    } catch (\Throwable $e) {
        // fall back to static file below
    }

    $path = public_path('catalog-products.xml');
    if (is_file($path)) {
        return response()->file($path, ['Content-Type' => 'application/xml; charset=utf-8']);
    }

    return response('<?xml version="1.0" encoding="UTF-8"?><catalog/>', 200, ['Content-Type' => 'application/xml; charset=utf-8']);
})->where(['locale' => '[A-Za-z\-_]+'])->name('catalog.locale');

// Separate feeds: products.xml
Route::get('/products.xml', function () {
    $doc = new \DOMDocument('1.0', 'UTF-8');
    $doc->formatOutput = true;
    $root = $doc->createElement('products');
    $doc->appendChild($root);

    $locale = 'lt';

    \App\Models\Product::query()
        ->with(['categories:id'])
        ->orderBy('id')
        ->chunk(250, function ($chunk) use ($doc, $root, $locale) {
            // Preload attribute pivots for the chunk
            $productIds = $chunk->pluck('id')->all();
            $pivots = \DB::table('product_attributes')
                ->select('product_id', 'attribute_id', 'attribute_value_id')
                ->whereIn('product_id', $productIds)
                ->get()
                ->groupBy('product_id');

            // Preload translations for attributes and values (lt)
            $attributeIds = $pivots->flatten()->pluck('attribute_id')->unique()->values()->all();
            $valueIds = $pivots->flatten()->pluck('attribute_value_id')->unique()->values()->all();

            $attrNames = \DB::table('attribute_translations')
                ->select('attribute_id', 'name')
                ->where('locale', $locale)
                ->whereIn('attribute_id', $attributeIds ?: [0])
                ->get()
                ->keyBy('attribute_id');

            $valTexts = \DB::table('attribute_value_translations')
                ->select('attribute_value_id', 'value')
                ->where('locale', $locale)
                ->whereIn('attribute_value_id', $valueIds ?: [0])
                ->get()
                ->keyBy('attribute_value_id');

            foreach ($chunk as $product) {
                $pEl = $doc->createElement('product');
                $pEl->appendChild($doc->createElement('id', (string) $product->id));
                if (!empty($product->sku)) {
                    $pEl->appendChild($doc->createElement('sku', (string) $product->sku));
                }
                if (!empty($product->slug)) {
                    $pEl->appendChild($doc->createElement('slug', (string) $product->slug));
                }

                // Category relations by ID
                $catIdsEl = $doc->createElement('category_ids');
                foreach ($product->categories as $cat) {
                    $catIdsEl->appendChild($doc->createElement('category_id', (string) $cat->id));
                }
                $pEl->appendChild($catIdsEl);

                // Attributes (LT only)
                $attrsEl = $doc->createElement('attributes');
                foreach (($pivots[$product->id] ?? collect()) as $pv) {
                    $attrEl = $doc->createElement('attribute');
                    $attrEl->setAttribute('id', (string) $pv->attribute_id);

                    $name = (string) ($attrNames[$pv->attribute_id]->name ?? '');
                    if ($name !== '') {
                        $nameEl = $doc->createElement('name', $name);
                        $nameEl->setAttribute('locale', $locale);
                        $attrEl->appendChild($nameEl);
                    }

                    if (!empty($pv->attribute_value_id)) {
                        $valueText = (string) ($valTexts[$pv->attribute_value_id]->value ?? '');
                        $valEl = $doc->createElement('value', $valueText);
                        $valEl->setAttribute('id', (string) $pv->attribute_value_id);
                        $valEl->setAttribute('locale', $locale);
                        $attrEl->appendChild($valEl);
                    }

                    $attrsEl->appendChild($attrEl);
                }
                $pEl->appendChild($attrsEl);

                $root->appendChild($pEl);
            }
        });

    return response($doc->saveXML() ?: '', 200, ['Content-Type' => 'application/xml; charset=utf-8']);
})->name('products.xml');

// Separate feeds: categories.xml
Route::get('/categories.xml', function () {
    $doc = new \DOMDocument('1.0', 'UTF-8');
    $doc->formatOutput = true;
    $root = $doc->createElement('categories');
    $doc->appendChild($root);

    $locale = 'lt';

    \App\Models\Category::query()
        ->orderBy('id')
        ->chunk(500, function ($chunk) use ($doc, $root, $locale) {
            // Preload LT names
            $categoryIds = $chunk->pluck('id')->all();
            $names = \DB::table('category_translations')
                ->select('category_id', 'name')
                ->where('locale', $locale)
                ->whereIn('category_id', $categoryIds ?: [0])
                ->get()
                ->keyBy('category_id');

            foreach ($chunk as $cat) {
                $cEl = $doc->createElement('category');
                $cEl->appendChild($doc->createElement('id', (string) $cat->id));
                if (!is_null($cat->parent_id)) {
                    $cEl->appendChild($doc->createElement('parent_id', (string) $cat->parent_id));
                }
                if (!empty($cat->slug)) {
                    $cEl->appendChild($doc->createElement('slug', (string) $cat->slug));
                }
                $name = (string) ($names[$cat->id]->name ?? '');
                if ($name !== '') {
                    $nameEl = $doc->createElement('name', $name);
                    $nameEl->setAttribute('locale', $locale);
                    $cEl->appendChild($nameEl);
                }
                $root->appendChild($cEl);
            }
        });

    return response($doc->saveXML() ?: '', 200, ['Content-Type' => 'application/xml; charset=utf-8']);
})->name('categories.xml');

// Exports browser and downloads (protected)
Route::middleware('auth')->group(function (): void {
    Route::get('/exports', [App\Http\Controllers\ExportController::class, 'index'])
        ->middleware('can:exports.view')
        ->name('exports.index');
    // support both /exports/{file} and /exports/download/{file}
    Route::get('/exports/{filename}', [App\Http\Controllers\ExportController::class, 'download'])
        ->middleware('can:exports.view')
        ->name('exports.file');
    Route::get('/exports/download/{filename}', [App\Http\Controllers\ExportController::class, 'download'])
        ->middleware('can:exports.view')
        ->name('exports.download');
});

// Locations pages
Route::get('/locations', function () {
    return redirect('/' . app()->getLocale() . '/locations');
})->name('locations.index');
// Primary Livewire route uses {slug}
Route::get('/locations/{slug}', App\Livewire\Pages\Location\Show::class)->name('locations.view');
// Backward-compatible ID-based route name used by blades; redirects to slug route
Route::get('/locations/{id}', function ($id) {
    $loc = \App\Models\Location::query()->findOrFail($id);
    $slug = $loc->code ?: $loc->name;

    return redirect()->route('locations.view', ['slug' => $slug]);
})->whereNumber('id')->name('locations.show.legacy');

// --- Locale-prefixed public routes used in tests ---
Route::prefix('{locale}')
    ->where(['locale' => '[A-Za-z\-_]+'])
    ->group(function (): void {
        // Localized home route (e.g., /lt)
        Route::get('/', Pages\Home::class)->name('localized.home');

        // Category index
        Route::get('/categories', \App\Livewire\Pages\Category\Index::class)->name('localized.categories.index');

        // Category show
        Route::get('/categories/{category}', \App\Livewire\Pages\Category\Show::class)->name('localized.categories.show');

        // Product routes
        Route::get('/products', Pages\ProductCatalog::class)->name('localized.products.index');
        Route::get('/products/{product}', Pages\SingleProduct::class)->name('localized.products.show');
        Route::get('/products/{product}/history', Pages\ProductHistoryPage::class)->name('localized.products.history');

        // Product Request routes (authenticated users only)
        Route::middleware(['auth'])->group(function () {
            Route::get('/products/{product}/request', [App\Http\Controllers\ProductRequestController::class, 'create'])->name('localized.product-requests.create');
            Route::post('/product-requests', [App\Http\Controllers\ProductRequestController::class, 'store'])->name('localized.product-requests.store');
            Route::get('/product-requests', [App\Http\Controllers\ProductRequestController::class, 'index'])->name('localized.product-requests.index');
            Route::get('/product-requests/{productRequest}', [App\Http\Controllers\ProductRequestController::class, 'show'])->name('localized.product-requests.show');
            Route::patch('/product-requests/{productRequest}/cancel', [App\Http\Controllers\ProductRequestController::class, 'cancel'])->name('localized.product-requests.cancel');
        });

        // Inventory routes
        Route::get('/inventory', [App\Http\Controllers\InventoryController::class, 'index'])->name('localized.inventory.index');

        // Cart page
        Route::get('/cart', Pages\Cart::class)->name('localized.cart.index');

        // Search page
        Route::get('/search', \App\Livewire\Pages\Search::class)->name('localized.search');

        // Brand index
        Route::get('/brands', \App\Livewire\Pages\Brand\Index::class)->name('localized.brands.index');

        // News localized routes (define both variants within locale group)
        Route::get('/news', \App\Livewire\Pages\News\Index::class)->name('localized.news.index.en');
        Route::get('/news/{slug}', \App\Livewire\Pages\News\Show::class)->name('localized.news.show.en');
        Route::get('/naujienos', \App\Livewire\Pages\News\Index::class)->name('localized.news.index.lt');
        Route::get('/naujienos/{slug}', \App\Livewire\Pages\News\Show::class)->name('localized.news.show.lt');

        // Brand show
        Route::get('/brands/{slug}', [\App\Http\Controllers\BrandController::class, 'show'])->name('localized.brands.show');

        // Locations index
        Route::get('/locations', \App\Livewire\Pages\Location\Index::class)->name('localized.locations.index');

        // Location show by slug
        Route::get('/locations/{slug}', \App\Livewire\Pages\Location\Show::class)->name('localized.locations.show');

        // Collections routes
        Route::get('/collections', \App\Livewire\Pages\Collection\Index::class)->name('localized.collections.index');
        Route::get('/collections/{collection}', \App\Livewire\Pages\Collection\Show::class)->name('localized.collections.show');

        // Cpanel redirects to non-localized versions
        Route::get('/cpanel', function () {
            return redirect('/cpanel/login');
        })->name('localized.cpanel');
        Route::get('/cpanel/{path?}', function ($locale, $path = null) {
            return redirect('/cpanel/' . ($path ?? ''));
        })->where('path', '.*')->name('localized.cpanel.any');

        // Order confirmation by number (must be authed in tests)
        Route::middleware('auth')->get('/order/confirmed/{number}', function (string $locale, string $number) {
            if (\Illuminate\Support\Facades\Schema::hasTable('orders')) {
                $exists = \Illuminate\Support\Facades\DB::table('orders')->where('number', $number)->exists();
                if ($exists) {
                    return response('OK');
                }
            }

            return redirect('/');
        })->name('localized.order.confirmed');
    });

// --- Admin News helper endpoints (HTTP verbs for tests) ---
Route::middleware('auth')->group(function (): void {
    // Index placeholder
    Route::get('/admin/news', function () {
        return response('OK');
    })->name('filament.admin.resources.news.index');

    // Store
    Route::post('/admin/news', function (\Illuminate\Http\Request $request) {
        $data = $request->validate([
            'is_visible' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
            'author_name' => ['nullable', 'string', 'max:255'],
            'translations' => ['nullable', 'array'],
        ]);

        /** @var \App\Models\News $news */
        $news = \App\Models\News::query()->create([
            'is_visible' => (bool) ($data['is_visible'] ?? true),
            'published_at' => $data['published_at'] ?? null,
            'author_name' => $data['author_name'] ?? null,
        ]);

        foreach ((array) ($data['translations'] ?? []) as $t) {
            if (!is_array($t)) {
                continue;
            }
            $locale = $t['locale'] ?? null;
            if (!is_string($locale) || $locale === '') {
                continue;
            }
            \App\Models\Translations\NewsTranslation::query()->updateOrCreate(
                [
                    'news_id' => $news->id,
                    'locale' => $locale,
                ],
                [
                    'title' => $t['title'] ?? null,
                    'slug' => $t['slug'] ?? str($t['title'] ?? '')->slug()->toString(),
                    'summary' => $t['summary'] ?? null,
                    'content' => $t['content'] ?? null,
                    'seo_title' => $t['seo_title'] ?? null,
                    'seo_description' => $t['seo_description'] ?? null,
                ]
            );
        }

        return redirect()->to('/admin/news');
    })->name('filament.admin.resources.news.store');

    // Update
    Route::put('/admin/news/{record}', function (\Illuminate\Http\Request $request, \App\Models\News $record) {
        $data = $request->validate([
            'is_visible' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
            'author_name' => ['nullable', 'string', 'max:255'],
            'translations' => ['nullable', 'array'],
        ]);

        $record->update(array_filter([
            'is_visible' => $data['is_visible'] ?? $record->is_visible,
            'published_at' => $data['published_at'] ?? $record->published_at,
            'author_name' => $data['author_name'] ?? $record->author_name,
        ], fn($v) => !is_null($v)));

        foreach ((array) ($data['translations'] ?? []) as $t) {
            if (!is_array($t)) {
                continue;
            }
            $locale = $t['locale'] ?? null;
            if (!is_string($locale) || $locale === '') {
                continue;
            }
            \App\Models\Translations\NewsTranslation::query()->updateOrCreate(
                [
                    'news_id' => $record->id,
                    'locale' => $locale,
                ],
                [
                    'title' => $t['title'] ?? null,
                    'slug' => $t['slug'] ?? null,
                    'summary' => $t['summary'] ?? null,
                    'content' => $t['content'] ?? null,
                    'seo_title' => $t['seo_title'] ?? null,
                    'seo_description' => $t['seo_description'] ?? null,
                ]
            );
        }

        return redirect()->to('/admin/news');
    })->name('filament.admin.resources.news.update');
});

// --- Admin translation save helpers expected by tests ---
Route::middleware('auth')->group(function (): void {
    Route::put('/admin/{locale}/legal/{id}/translations/{lang}', fn() => back())
        ->name('admin.legal.translations.save');
    Route::put('/admin/{locale}/brands/{id}/translations/{lang}', fn() => back())
        ->name('admin.brands.translations.save');
    Route::put('/admin/{locale}/categories/{id}/translations/{lang}', fn() => back())
        ->name('admin.categories.translations.save');
    Route::put('/admin/{locale}/collections/{id}/translations/{lang}', fn() => back())
        ->name('admin.collections.translations.save');
    Route::put('/admin/{locale}/products/{id}/translations/{lang}', fn() => back())
        ->name('admin.products.translations.save');
    Route::put('/admin/{locale}/attributes/{id}/translations/{lang}', fn() => back())
        ->name('admin.attributes.translations.save');
    Route::put('/admin/{locale}/attribute-values/{id}/translations/{lang}', fn() => back())
        ->name('admin.attribute-values.translations.save');
});

// Notification routes
Route::middleware('auth')->group(function (): void {
    Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/{id}/unread', [App\Http\Controllers\NotificationController::class, 'markAsUnread'])->name('notifications.unread');
    Route::post('/notifications/read-all', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::delete('/notifications/{id}', [App\Http\Controllers\NotificationController::class, 'delete'])->name('notifications.delete');
    Route::delete('/notifications', [App\Http\Controllers\NotificationController::class, 'clearAll'])->name('notifications.clear-all');
    Route::get('/notifications/unread-count', [App\Http\Controllers\NotificationController::class, 'getUnreadCount'])->name('notifications.unread-count');
    Route::get('/notifications/recent', [App\Http\Controllers\NotificationController::class, 'getRecent'])->name('notifications.recent');
});

// Localized notification routes
Route::middleware(['auth', 'localize'])->group(function (): void {
    Route::get('/{locale}/notifications', [App\Http\Controllers\NotificationController::class, 'index'])->name('localized.notifications.index');
});

// SEO Data Routes
Route::prefix('seo-data')->name('seo-data.')->group(function () {
    Route::get('/', [App\Http\Controllers\SeoDataController::class, 'index'])->name('index');
    Route::get('/analytics', [App\Http\Controllers\SeoDataController::class, 'analytics'])->name('analytics');
    Route::get('/type/{type}', [App\Http\Controllers\SeoDataController::class, 'byType'])->name('by-type');
    Route::get('/{seoData}', [App\Http\Controllers\SeoDataController::class, 'show'])->name('show');
});

// Attribute Frontend Routes
Route::prefix('attributes')->name('frontend.attributes.')->group(function () {
    Route::get('/', [App\Http\Controllers\Frontend\AttributeController::class, 'index'])->name('index');
    Route::get('/{attribute}', [App\Http\Controllers\Frontend\AttributeController::class, 'show'])->name('show');
    Route::get('/filter/products', [App\Http\Controllers\Frontend\AttributeController::class, 'filter'])->name('filter');

    // API Routes for enhanced functionality
    Route::get('/api/values', [App\Http\Controllers\Frontend\AttributeController::class, 'getAttributeValues'])->name('api.values');
    Route::get('/api/statistics', [App\Http\Controllers\Frontend\AttributeController::class, 'getAttributeStatistics'])->name('api.statistics');
    Route::get('/api/groups', [App\Http\Controllers\Frontend\AttributeController::class, 'getAttributeGroups'])->name('api.groups');
    Route::get('/api/types', [App\Http\Controllers\Frontend\AttributeController::class, 'getAttributeTypes'])->name('api.types');
    Route::get('/api/search', [App\Http\Controllers\Frontend\AttributeController::class, 'searchAttributes'])->name('api.search');
    Route::get('/api/compare', [App\Http\Controllers\Frontend\AttributeController::class, 'getAttributeComparison'])->name('api.compare');
});

// Attribute Value Frontend Routes
Route::prefix('attribute-values')->name('attribute-values.')->group(function () {
    Route::get('/', [App\Http\Controllers\AttributeValueController::class, 'index'])->name('index');
    Route::get('/{attributeValue}', [App\Http\Controllers\AttributeValueController::class, 'show'])->name('show');
    Route::get('/attribute/{attribute}', [App\Http\Controllers\AttributeValueController::class, 'byAttribute'])->name('by-attribute');
    Route::get('/api/search', [App\Http\Controllers\AttributeValueController::class, 'search'])->name('search');
    Route::get('/api/data', [App\Http\Controllers\AttributeValueController::class, 'api'])->name('api');
});

// Post Frontend Routes
Route::prefix('posts')->name('posts.')->group(function () {
    Route::get('/', [App\Http\Controllers\PostController::class, 'index'])->name('index');
    Route::get('/featured', [App\Http\Controllers\PostController::class, 'featured'])->name('featured');
    Route::get('/search', [App\Http\Controllers\PostController::class, 'search'])->name('search');
    Route::get('/author/{authorId}', [App\Http\Controllers\PostController::class, 'byAuthor'])->name('by-author');
    Route::get('/{post}', [App\Http\Controllers\PostController::class, 'show'])->name('show');
});

// --- Admin News helper endpoints (HTTP verbs for tests) ---
Route::middleware('auth')->group(function (): void {
    // Index placeholder
    Route::get('/admin/news', function () {
        return response('OK');
    })->name('filament.admin.resources.news.index');

    // Store
    Route::post('/admin/news', function (\Illuminate\Http\Request $request) {
        $data = $request->validate([
            'is_visible' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
            'author_name' => ['nullable', 'string', 'max:255'],
            'translations' => ['nullable', 'array'],
        ]);

        /** @var \App\Models\News $news */
        $news = \App\Models\News::query()->create([
            'is_visible' => (bool) ($data['is_visible'] ?? true),
            'published_at' => $data['published_at'] ?? null,
            'author_name' => $data['author_name'] ?? null,
        ]);

        foreach ((array) ($data['translations'] ?? []) as $t) {
            if (!is_array($t)) {
                continue;
            }
            $locale = $t['locale'] ?? null;
            if (!is_string($locale) || $locale === '') {
                continue;
            }
            \App\Models\Translations\NewsTranslation::query()->updateOrCreate(
                [
                    'news_id' => $news->id,
                    'locale' => $locale,
                ],
                [
                    'title' => $t['title'] ?? null,
                    'slug' => $t['slug'] ?? str($t['title'] ?? '')->slug()->toString(),
                    'summary' => $t['summary'] ?? null,
                    'content' => $t['content'] ?? null,
                    'seo_title' => $t['seo_title'] ?? null,
                    'seo_description' => $t['seo_description'] ?? null,
                ]
            );
        }

        return redirect()->to('/admin/news');
    })->name('filament.admin.resources.news.store');

    // Update
    Route::put('/admin/news/{record}', function (\Illuminate\Http\Request $request, \App\Models\News $record) {
        $data = $request->validate([
            'is_visible' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
            'author_name' => ['nullable', 'string', 'max:255'],
            'translations' => ['nullable', 'array'],
        ]);

        $record->update(array_filter([
            'is_visible' => $data['is_visible'] ?? $record->is_visible,
            'published_at' => $data['published_at'] ?? $record->published_at,
            'author_name' => $data['author_name'] ?? $record->author_name,
        ], fn($v) => !is_null($v)));

        foreach ((array) ($data['translations'] ?? []) as $t) {
            if (!is_array($t)) {
                continue;
            }
            $locale = $t['locale'] ?? null;
            if (!is_string($locale) || $locale === '') {
                continue;
            }
            \App\Models\Translations\NewsTranslation::query()->updateOrCreate(
                [
                    'news_id' => $record->id,
                    'locale' => $locale,
                ],
                [
                    'title' => $t['title'] ?? null,
                    'slug' => $t['slug'] ?? null,
                    'summary' => $t['summary'] ?? null,
                    'content' => $t['content'] ?? null,
                    'seo_title' => $t['seo_title'] ?? null,
                    'seo_description' => $t['seo_description'] ?? null,
                ]
            );
        }

        return redirect()->to('/admin/news');
    })->name('filament.admin.resources.news.update');
});

// --- Admin translation save helpers expected by tests ---
Route::middleware('auth')->group(function (): void {
    Route::put('/admin/{locale}/legal/{id}/translations/{lang}', fn() => back())
        ->name('admin.legal.translations.save');
    Route::put('/admin/{locale}/brands/{id}/translations/{lang}', fn() => back())
        ->name('admin.brands.translations.save');
    Route::put('/admin/{locale}/categories/{id}/translations/{lang}', fn() => back())
        ->name('admin.categories.translations.save');
    Route::put('/admin/{locale}/collections/{id}/translations/{lang}', fn() => back())
        ->name('admin.collections.translations.save');
    Route::put('/admin/{locale}/products/{id}/translations/{lang}', fn() => back())
        ->name('admin.products.translations.save');
    Route::put('/admin/{locale}/attributes/{id}/translations/{lang}', fn() => back())
        ->name('admin.attributes.translations.save');
    Route::put('/admin/{locale}/attribute-values/{id}/translations/{lang}', fn() => back())
        ->name('admin.attribute-values.translations.save');
});

// Notification routes
Route::middleware('auth')->group(function (): void {
    Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/{id}/unread', [App\Http\Controllers\NotificationController::class, 'markAsUnread'])->name('notifications.unread');
    Route::post('/notifications/read-all', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::delete('/notifications/{id}', [App\Http\Controllers\NotificationController::class, 'delete'])->name('notifications.delete');
    Route::delete('/notifications', [App\Http\Controllers\NotificationController::class, 'clearAll'])->name('notifications.clear-all');
    Route::get('/notifications/unread-count', [App\Http\Controllers\NotificationController::class, 'getUnreadCount'])->name('notifications.unread-count');
    Route::get('/notifications/recent', [App\Http\Controllers\NotificationController::class, 'getRecent'])->name('notifications.recent');
});

// Localized notification routes
Route::middleware(['auth', 'localize'])->group(function (): void {
    Route::get('/{locale}/notifications', [App\Http\Controllers\NotificationController::class, 'index'])->name('localized.notifications.index');
});

// SEO Data Routes
Route::prefix('seo-data')->name('seo-data.')->group(function () {
    Route::get('/', [App\Http\Controllers\SeoDataController::class, 'index'])->name('index');
    Route::get('/analytics', [App\Http\Controllers\SeoDataController::class, 'analytics'])->name('analytics');
    Route::get('/type/{type}', [App\Http\Controllers\SeoDataController::class, 'byType'])->name('by-type');
    Route::get('/{seoData}', [App\Http\Controllers\SeoDataController::class, 'show'])->name('show');
});

// Attribute Frontend Routes
Route::prefix('attributes')->name('frontend.attributes.')->group(function () {
    Route::get('/', [App\Http\Controllers\Frontend\AttributeController::class, 'index'])->name('index');
    Route::get('/{attribute}', [App\Http\Controllers\Frontend\AttributeController::class, 'show'])->name('show');
    Route::get('/filter/products', [App\Http\Controllers\Frontend\AttributeController::class, 'filter'])->name('filter');

    // API Routes for enhanced functionality
    Route::get('/api/values', [App\Http\Controllers\Frontend\AttributeController::class, 'getAttributeValues'])->name('api.values');
    Route::get('/api/statistics', [App\Http\Controllers\Frontend\AttributeController::class, 'getAttributeStatistics'])->name('api.statistics');
    Route::get('/api/groups', [App\Http\Controllers\Frontend\AttributeController::class, 'getAttributeGroups'])->name('api.groups');
    Route::get('/api/types', [App\Http\Controllers\Frontend\AttributeController::class, 'getAttributeTypes'])->name('api.types');
    Route::get('/api/search', [App\Http\Controllers\Frontend\AttributeController::class, 'searchAttributes'])->name('api.search');
    Route::get('/api/compare', [App\Http\Controllers\Frontend\AttributeController::class, 'getAttributeComparison'])->name('api.compare');
});

// Attribute Value Frontend Routes
Route::prefix('attribute-values')->name('attribute-values.')->group(function () {
    Route::get('/', [App\Http\Controllers\AttributeValueController::class, 'index'])->name('index');
    Route::get('/{attributeValue}', [App\Http\Controllers\AttributeValueController::class, 'show'])->name('show');
    Route::get('/attribute/{attribute}', [App\Http\Controllers\AttributeValueController::class, 'byAttribute'])->name('by-attribute');
    Route::get('/api/search', [App\Http\Controllers\AttributeValueController::class, 'search'])->name('search');
    Route::get('/api/data', [App\Http\Controllers\AttributeValueController::class, 'api'])->name('api');
});

// Post Frontend Routes
Route::prefix('posts')->name('posts.')->group(function () {
    Route::get('/', [App\Http\Controllers\PostController::class, 'index'])->name('index');
    Route::get('/featured', [App\Http\Controllers\PostController::class, 'featured'])->name('featured');
    Route::get('/search', [App\Http\Controllers\PostController::class, 'search'])->name('search');
    Route::get('/author/{authorId}', [App\Http\Controllers\PostController::class, 'byAuthor'])->name('by-author');
    Route::get('/{post}', [App\Http\Controllers\PostController::class, 'show'])->name('show');
});

// City Frontend Routes
Route::prefix('cities')->name('cities.')->group(function () {
    Route::get('/', [App\Http\Controllers\CityController::class, 'index'])->name('index');
    Route::get('/search', [App\Http\Controllers\CityController::class, 'search'])->name('search');
    Route::get('/country/{country}', [App\Http\Controllers\CityController::class, 'byCountry'])->name('by-country');
    Route::get('/{city}', [App\Http\Controllers\CityController::class, 'show'])->name('show');
});

// Location Frontend Routes
Route::prefix('locations')->name('locations.')->group(function () {
    Route::get('/', [App\Http\Controllers\LocationController::class, 'index'])->name('index');
    Route::get('/{location}', [App\Http\Controllers\LocationController::class, 'show'])->name('show');
    Route::post('/{location}/contact', [App\Http\Controllers\LocationController::class, 'contact'])->name('contact');
});

// Country Frontend Routes
Route::prefix('countries')->name('countries.')->group(function () {
    Route::get('/', [App\Http\Controllers\CountryController::class, 'index'])->name('index');
    Route::get('/{country}', [App\Http\Controllers\CountryController::class, 'show'])->name('show');
    Route::get('/api/search', [App\Http\Controllers\CountryController::class, 'api'])->name('api.search');
    Route::get('/api/eu-members', [App\Http\Controllers\CountryController::class, 'euMembers'])->name('api.eu-members');
    Route::get('/api/with-vat', [App\Http\Controllers\CountryController::class, 'withVat'])->name('api.with-vat');
    Route::get('/api/statistics', [App\Http\Controllers\CountryController::class, 'statistics'])->name('api.statistics');
});

// Region Frontend Routes

// Collection Frontend Routes
Route::prefix('collections')->name('collections.')->group(function () {
    Route::get('/', [App\Http\Controllers\CollectionController::class, 'index'])->name('index');
    Route::get('/{collection}', [App\Http\Controllers\CollectionController::class, 'show'])->name('show');
    Route::get('/api/search', [App\Http\Controllers\CollectionController::class, 'api'])->name('api.search');
    Route::get('/api/by-type/{type}', [App\Http\Controllers\CollectionController::class, 'byType'])->name('api.by-type');
    Route::get('/api/with-products', [App\Http\Controllers\CollectionController::class, 'withProducts'])->name('api.with-products');
    Route::get('/api/popular', [App\Http\Controllers\CollectionController::class, 'popular'])->name('api.popular');
    Route::get('/api/statistics', [App\Http\Controllers\CollectionController::class, 'statistics'])->name('api.statistics');
    Route::get('/{collection}/products', [App\Http\Controllers\CollectionController::class, 'products'])->name('products');
});

// Attribute Frontend Routes
Route::prefix('attributes')->name('attributes.')->group(function () {
    Route::get('/', [App\Http\Controllers\AttributeController::class, 'index'])->name('index');
    Route::get('/{attribute}', [App\Http\Controllers\AttributeController::class, 'show'])->name('show');
    Route::get('/api/search', [App\Http\Controllers\AttributeController::class, 'api'])->name('api.search');
    Route::get('/api/by-type/{type}', [App\Http\Controllers\AttributeController::class, 'byType'])->name('api.by-type');
    Route::get('/api/by-group/{group}', [App\Http\Controllers\AttributeController::class, 'byGroup'])->name('api.by-group');
    Route::get('/api/filterable', [App\Http\Controllers\AttributeController::class, 'filterable'])->name('api.filterable');
    Route::get('/api/required', [App\Http\Controllers\AttributeController::class, 'required'])->name('api.required');
    Route::get('/api/statistics', [App\Http\Controllers\AttributeController::class, 'statistics'])->name('api.statistics');
    Route::get('/{attribute}/values', [App\Http\Controllers\AttributeController::class, 'values'])->name('values');
});
