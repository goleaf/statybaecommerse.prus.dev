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
    // Currently implemented routes only
    'health',
    'home',
    'products.index',
    'products.gallery',
    'categories.index',
    'categories.show',
    'brands.index',
    'brands.show',
    'collections.index',
    'collections.show',
    'products.show',
    'search',
    'cart.index',
    'locations.index',
    'locations.show',
    'legal.show',
    'checkout.index',
    'account.orders',
    'account.addresses',
    'admin.impersonate',
    'admin.stop-impersonating',
])->skip(function () {
    // Skip entirely if permission middleware is not available in this environment
    return !class_exists(\Spatie\Permission\Middleware\PermissionMiddleware::class);
}, 'Spatie Permission middleware not installed; route registration may differ.');

it('guest is redirected from protected routes', function (string $method, string $routeName, array $params = []): void {
    $url = route($routeName, $params);
    $response = $this->call($method, $url);
    $response->assertRedirect();
})->with([
    // Test currently implemented protected routes
    ['GET', 'checkout.index'],
    ['GET', 'account.orders'],
    ['GET', 'account.addresses'],
])->skip(function () {
    return !class_exists(\Spatie\Permission\Middleware\PermissionMiddleware::class);
}, 'Spatie Permission middleware not installed; protected routes may not boot.');
