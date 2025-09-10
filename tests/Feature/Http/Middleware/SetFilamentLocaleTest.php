<?php declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

it('sets locale from request parameter', function (): void {
    config()->set('app.locale', 'lt');
    config()->set('app.supported_locales', ['lt', 'en']);

    Route::middleware([\App\Http\Middleware\SetFilamentLocale::class])->get('/test-locale', fn() => response('ok'));

    $this
        ->get('/test-locale?locale=en')
        ->assertOk();

    expect(App::getLocale())->toBe('en');
    expect(Session::get('locale'))->toBe('en');
});

it('sets locale from session when request missing', function (): void {
    config()->set('app.locale', 'lt');
    config()->set('app.supported_locales', ['lt', 'en']);

    Session::put('locale', 'en');

    Route::middleware([\App\Http\Middleware\SetFilamentLocale::class])->get('/test-locale-2', fn() => response('ok'));

    $this
        ->get('/test-locale-2')
        ->assertOk();

    expect(App::getLocale())->toBe('en');
});

it('falls back to default when invalid locale provided', function (): void {
    config()->set('app.locale', 'lt');
    config()->set('app.supported_locales', ['lt', 'en']);

    Route::middleware([\App\Http\Middleware\SetFilamentLocale::class])->get('/test-locale-3', fn() => response('ok'));

    $this
        ->get('/test-locale-3?locale=xx')
        ->assertOk();

    expect(App::getLocale())->toBe('lt');
    expect(Session::get('locale'))->toBe('lt');
});
