<?php

declare(strict_types=1);

use Illuminate\Support\Collection;

uses()->group('localization');

test('key storefront pages render without missing navigation translations for every supported locale', function (): void {
    $this->withoutVite();

    $supportedConfig = config('app.supported_locales', []);
    $sharedConfig = config('shared.localization.supported_locales', []);

    $locales = Collection::wrap(
        is_array($supportedConfig)
            ? $supportedConfig
            : array_map('trim', explode(',', (string) $supportedConfig))
    )
        ->merge(Collection::wrap($sharedConfig))
        ->map(fn ($locale) => trim((string) $locale))
        ->filter()
        ->unique()
        ->values();

    expect($locales)->not->toBeEmpty();

    $pages = [
        'home'        => '/',
        'brands'      => '/brands',
        'categories'  => '/categories',
        'collections' => '/collections',
    ];

    $navigationKeys = [
        'nav_home',
        'nav_categories',
        'nav_collections',
        'nav_brands',
        'nav_search',
        'nav_cart',
        'nav_account',
        'nav_locations',
        'nav_toggle',
        'support_centre',
        'company_phone',
        'company_email',
    ];

    $translator = app('translator');

    foreach ($locales as $locale) {
        app()->setLocale($locale);
        if (method_exists($translator, 'setLocale')) {
            $translator->setLocale($locale);
        }

        if (method_exists($translator, 'setLoaded')) {
            $translator->setLoaded([]);
        }

        if (method_exists($translator, 'load')) {
            $translator->load('*', 'messages', $locale);
            $translator->load('*', 'json', $locale);
        }

        foreach ($pages as $page => $uri) {
            $response = get($uri);
            $redirects = 0;
            while (in_array($response->getStatusCode(), [301, 302, 303, 307, 308], true) && $redirects < 5) {
                $location = $response->headers->get('Location');
                $target = $location !== null ? (parse_url($location, PHP_URL_PATH) ?: $location) : $uri;
                $response = get($target);
                $redirects++;
            }

            $response->assertOk();

            $html = $response->getContent();

            foreach ($navigationKeys as $key) {
                test()->assertStringNotContainsString(
                    $key,
                    $html,
                    sprintf('Missing translation for [%s] on [%s] page when locale is [%s].', $key, $page, $locale)
                );
            }
        }
    }
});
