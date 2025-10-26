<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\from;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    // Provide deterministic localisation defaults for each scenario.
    config()->set('app.supported_locales', ['en', 'lt']);
    config()->set('app.locale', 'en');
    config()->set('app.fallback_locale', 'en');
    config()->set('app.locale_mapping', [
        'en' => ['currency' => 'EUR'],
        'lt' => ['currency' => 'EUR'],
    ]);
});

it('updates the locale, persists the preference, and redirects back safely', function (): void {
    // Create a user with an initial locale to verify persistence updates.
    $user = User::factory()->create(['preferred_locale' => 'en']);

    actingAs($user);

    // Simulate the request originating from a dashboard page to test the back redirect.
    $response = from('/dashboard')->post(route('locale.switch'), ['locale' => 'lt']);

    $response->assertRedirect('/dashboard');
    $response->assertSessionHas('locale', 'lt');
    $response->assertSessionHas('app.locale', 'lt');
    $response->assertSessionHas('forced_currency', 'EUR');
    $response->assertCookie('app_locale', 'lt');

    // Confirm the application locale and stored preference were updated.
    expect(App::getLocale())->toBe('lt');
    expect($user->fresh()->preferred_locale)->toBe('lt');
});

it('maps regional locale variants to supported locales when switching', function (): void {
    // Only expose English as the supported locale for the alias test.
    config()->set('app.supported_locales', ['en']);

    $response = post(route('locale.switch'), ['locale' => 'en-GB']);

    $response->assertRedirect(route('localized.home', ['locale' => 'en']));
    $response->assertSessionHas('locale', 'en');

    // The resolved locale should align with the supported one.
    expect(App::getLocale())->toBe('en');
});

it('falls back to the configured locale when the request cannot be matched', function (): void {
    // Configure Lithuanian as both the default and fallback locale for the scenario.
    config()->set('app.supported_locales', ['lt']);
    config()->set('app.locale', 'lt');
    config()->set('app.fallback_locale', 'lt');

    $response = post(route('locale.switch'), ['locale' => 'es']);

    $response->assertRedirect(route('localized.home', ['locale' => 'lt']));
    $response->assertSessionHas('locale', 'lt');

    expect(App::getLocale())->toBe('lt');
});

it('allows absolute redirects that stay within the same host, scheme, and port', function (): void {
    // Authenticate a user so preference persistence can be exercised.
    actingAs(User::factory()->create());

    $response = post(route('locale.switch'), [
        'locale'      => 'lt',
        'redirect_to' => 'http://localhost/profile',
    ]);

    $response->assertRedirect('http://localhost/profile');
});

it('rejects redirects that attempt to escape the current application origin', function (): void {
    // Use a crafted absolute URL pointing to another domain and port to test the guard.
    $response = post(route('locale.switch'), [
        'locale'      => 'lt',
        'redirect_to' => 'https://malicious.example:8443/phish',
    ]);

    $response->assertRedirect(route('localized.home', ['locale' => 'en']));
});
