<?php

declare(strict_types=1);

test('messages navigation labels exist across locales', function () {
    $locales = ['en', 'lt', 'de', 'ru'];
    $keys = [
        'home',
        'nav_categories',
        'nav_collections',
        'nav_brands',
        'nav_downloads',
        'nav_vendor_catalogs',
        'nav_support_center',
        'home_loyalty_title',
        'home_loyalty_subtitle',
        'home_loyalty_join',
        'home_loyalty_invite_friend',
        'home_loyalty_catalog_title',
        'home_loyalty_catalog_subtitle',
        'home_featured',
        'home_products_featured_title',
        'home_products_featured_subtitle',
        'home_slider_autoplay_start',
    ];

    foreach ($locales as $locale) {
        $translations = include lang_path($locale . '/messages.php');

        expect($translations)->toBeArray();

        foreach ($keys as $key) {
            expect($translations)->toHaveKey($key);
            expect($translations[$key])->toBeString()->not->toBeEmpty();
        }
    }
});
