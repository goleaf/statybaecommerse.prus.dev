<?php declare(strict_types=1);

use App\Http\Controllers\BrandController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

it('health route responds', function (): void {
    $this->get('/health')->assertOk()->assertJson(['ok' => true]);
});

it('localized brand routes resolve when feature enabled', function (): void {
    config()->set('shopper.features.brand', 'enabled');
    $locale = 'en';
    Route::shouldReceive('has');
    $this->get("/$locale/brands")->assertStatus(200);
})->skip(true, 'Requires seeded data/views; kept as route smoke test placeholder.');

it('locations index loads', function (): void {
    $locale = 'en';
    $this->get("/$locale/locations")->assertStatus(200);
})->skip(true, 'Views and data dependencies required; placeholder.');

it('checkout confirmation requires auth', function (): void {
    $locale = 'en';
    $this
        ->get("/$locale/order/confirmed/ORD-123")
        ->assertRedirect();
});
