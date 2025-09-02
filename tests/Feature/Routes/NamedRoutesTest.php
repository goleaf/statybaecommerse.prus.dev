<?php declare(strict_types=1);

use Illuminate\Support\Facades\Route;

beforeEach(function (): void {
    // Some routes depend on config flags or packages; ensure minimal config
    config()->set('shopper.features', [
        'brand' => 'enabled',
        'category' => 'enabled',
        'collection' => 'enabled',
    ]);
});

it('has expected named routes registered', function (string $routeName): void {
    expect(Route::has($routeName))->toBeTrue();
})->with([
    // Public
    'health',
    'sitemap.locale',
    'root.redirect',
    'brands.redirect',
    'locations.redirect',
    'categories.redirect',
    'collections.redirect',
    'search.redirect',
    'legal.redirect',
    'login.redirect',
    'register.redirect',
    'password.request.redirect',
    'password.reset.redirect',
    'verification.notice.redirect',
    'verification.verify.redirect',
    'password.confirm.redirect',
    'account.redirect',
    'cpanel.redirect',
    'admin.redirect',
    'cpanel.localized.fallback',
    // Localized
    'home',
    'product.show',
    'brand.index',
    'brand.show',
    'locations.index',
    'locations.show',
    'legal.show',
    'category.index',
    'category.show',
    'collection.index',
    'collection.show',
    'search.index',
    // Localized Auth
    'register',
    'login',
    'password.request',
    'password.reset',
    'verification.notice',
    'verification.verify',
    'password.confirm',
    'account',
    'account.profile',
    'account.addresses',
    'account.orders',
    'account.orders.detail',
    'account.order.show',
    'account.reviews',
    // Exports (auth)
    'exports.index',
    'exports.download',
    // Admin localized redirects
    'cpanel.redirect.localized',
    'admin.redirect.localized',
    // Order checkout confirmation (auth)
    'checkout.confirmation',
    // Admin area (auth + permission)
    // Admin area (auth + permission) — middleware may be missing in CI, but name should exist
    'admin.orders.packing-slip',
    'admin.discounts.codes',
    'admin.discounts.codes.store',
    'admin.discounts.codes.download',
    'admin.discounts.preview',
    'admin.discounts.preview.compute',
    'admin.discounts.presets',
    'admin.discounts.presets.store',
    'admin.campaigns.index',
    'admin.campaigns.create',
    'admin.campaigns.store',
    'admin.campaigns.edit',
    'admin.campaigns.update',
    'admin.redemptions.index',
    'admin.redemptions.export',
    'admin.orders.status.edit',
    'admin.orders.status.update',
    'admin.orders.tracking.update',
    // Admin translation update routes (auth)
    'admin.legal.translations.save',
    'admin.brands.translations.save',
    'admin.categories.translations.save',
    'admin.collections.translations.save',
    'admin.products.translations.save',
    'admin.attributes.translations.save',
    'admin.attribute-values.translations.save',
])->skip(function () {
    // Skip entirely if permission middleware is not available in this environment
    return !class_exists(\Spatie\Permission\Middleware\PermissionMiddleware::class);
}, 'Spatie Permission middleware not installed; route registration may differ.');

it('guest is redirected from protected routes', function (string $method, string $routeName, array $params = []): void {
    $url = route($routeName, $params + ['locale' => 'en']);
    $response = $this->call($method, $url);
    $response->assertRedirect();
})->with([
    ['GET', 'exports.index'],
    ['GET', 'exports.download', ['filename' => 'export.csv']],
    ['GET', 'checkout.confirmation', ['number' => 'ORD-123']],
    ['GET', 'admin.discounts.presets'],
    ['POST', 'admin.discounts.presets.store'],
    ['GET', 'admin.campaigns.index'],
    ['POST', 'admin.campaigns.store'],
    ['PUT', 'admin.campaigns.update', ['id' => 1]],
    ['GET', 'admin.redemptions.index'],
    ['GET', 'admin.orders.status.edit', ['number' => 'ORD-1']],
    ['PUT', 'admin.orders.status.update', ['number' => 'ORD-1']],
])->skip(function () {
    return !class_exists(\Spatie\Permission\Middleware\PermissionMiddleware::class);
}, 'Spatie Permission middleware not installed; protected routes may not boot.');
