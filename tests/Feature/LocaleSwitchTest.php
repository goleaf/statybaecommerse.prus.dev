<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

beforeEach(function (): void {
    config()->set('app.supported_locales', ['lt', 'en']);
    config()->set('app.locale', 'lt');
    config()->set('app.fallback_locale', 'en');

    Session::flush();
    App::setLocale('lt');
});

it('feature: persists the selected locale and updates the user preference', function (): void {
    $user = User::factory()->create(['preferred_locale' => 'lt']);

    $response = $this->actingAs($user)->post(route('locale.switch'), [
        'locale'      => 'en',
        'redirect_to' => url('/en/products'),
    ]);

    $response->assertRedirect(url('/en/products'));
    $response->assertCookie('app_locale', 'en');

    expect(Session::get('locale'))->toBe('en')
        ->and(Session::get('app.locale'))->toBe('en')
        ->and(App::getLocale())->toBe('en');

    $user->refresh();
    expect($user->preferred_locale)->toBe('en');
});

it('feature: falls back to the configured fallback locale when an unsupported locale is provided', function (): void {
    Session::put('locale', 'zz');
    Session::put('app.locale', 'zz');

    $response = $this->get('/health?locale=xx');

    $response->assertOk();
    $response->assertHeader('Content-Language', 'en');

    expect(App::getLocale())->toBe('en')
        ->and(Session::get('locale'))->toBe('en');
});

it('feature: rejects unsafe redirect targets during locale switching', function (): void {
    $response = $this->post(route('locale.switch'), [
        'locale'      => 'en',
        'redirect_to' => 'https://example.com/attack',
    ]);

    $response->assertRedirect(route('localized.home', ['locale' => 'en']));
    $response->assertCookie('app_locale', 'en');

    expect(Session::get('locale'))->toBe('en');
});
