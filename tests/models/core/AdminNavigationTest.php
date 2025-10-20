<?php

declare(strict_types=1);

it('has consolidated admin navigation labels configured', function (): void {
    app()->setLocale('lt');

    expect(__('admin.navigation.dashboard'))->not()->toBe('admin.navigation.dashboard');
    expect(__('admin.navigation.marketing'))->not()->toBe('admin.navigation.marketing');
    expect(__('admin.navigation.content'))->not()->toBe('admin.navigation.content');
    expect(__('admin.navigation.analytics'))->not()->toBe('admin.navigation.analytics');
    expect(__('admin.navigation.system'))->not()->toBe('admin.navigation.system');
    expect(__('admin.navigation.commerce'))->toBe('Prekyba');
});

it('loads the admin commerce label from JSON translations for locales with definitions', function (string $locale, string $expected): void {
    app()->setLocale($locale);

    expect(__('admin.navigation.commerce', [], $locale))->toBe($expected);
})->with([
    'Lithuanian' => ['lt', 'Prekyba'],
    'English' => ['en', 'Commerce'],
]);

it('never falls back to the raw commerce translation key across configured locales', function (string $locale): void {
    expect(__('admin.navigation.commerce', [], $locale))->not()->toBe('admin.navigation.commerce');
})->with(function (): array {
    $configured = config('shared.localization.supported_locales', []);
    $appConfigured = array_filter(array_map('trim', explode(',', (string) config('app.supported_locales', ''))));
    $fallback = (string) config('app.fallback_locale', '');

    $locales = array_filter(array_merge($configured, $appConfigured, [$fallback]));

    return array_map(static fn (string $locale): array => [$locale], array_values(array_unique($locales)));
});
