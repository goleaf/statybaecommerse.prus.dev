<?php

declare(strict_types=1);

use App\Http\Middleware\SetFilamentLocale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Livewire\Features\SupportRedirects\Redirector as LivewireRedirector;
use Symfony\Component\HttpFoundation\Response;

beforeEach(function (): void {
    Session::flush();

    config()->set('app.supported_locales', ['lt', 'en']);
    config()->set('app.locale', 'lt');
});

it('accepts livewire redirector responses without type errors', function (): void {
    $middleware = app(SetFilamentLocale::class);
    $request = Request::create('/admin/news/1/edit', 'GET', ['locale' => 'lt']);

    $response = $middleware->handle($request, static fn () => app(LivewireRedirector::class));

    expect($response)->toBeInstanceOf(LivewireRedirector::class);
});

it('stores admin locale when admin request returns a livewire redirector', function (): void {
    $middleware = app(SetFilamentLocale::class);
    $request = Request::create('/admin/news/1/edit?locale=lt', 'GET');

    $response = $middleware->handle($request, static fn () => app(LivewireRedirector::class));

    expect($response)->toBeInstanceOf(LivewireRedirector::class)
        ->and(app()->getLocale())->toBe('lt')
        ->and(session('admin_locale'))->toBe('lt')
        ->and(session('app.locale'))->toBe('lt');
});

it('passes through standard responses unchanged', function (): void {
    $middleware = app(SetFilamentLocale::class);
    $request = Request::create('/admin/news', 'GET');

    $response = $middleware->handle($request, static fn () => response('ok', 200));

    expect($response)->toBeInstanceOf(Response::class)
        ->and($response->getStatusCode())->toBe(200)
        ->and($response->getContent())->toBe('ok');
});
