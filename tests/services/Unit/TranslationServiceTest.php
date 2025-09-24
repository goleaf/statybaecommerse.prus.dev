<?php

declare(strict_types=1);

use App\Services\TranslationService;
use Tests\TestCase;

uses(TestCase::class);

it('normalizes dotted keys to underscores', function () {
    expect(TranslationService::normalizeKey('a.b.c'))->toBe('a_b_c');
});

it('returns available locales from csv or array config', function () {
    config()->set('app.supported_locales', 'lt,en');
    expect(TranslationService::getAvailableLocales())->toEqual(['lt', 'en']);

    config()->set('app.supported_locales', ['lt', 'en']);
    expect(TranslationService::getAvailableLocales())->toEqual(['lt', 'en']);
});

it('checks supported locale and default/fallback locales', function () {
    config()->set('app.supported_locales', 'lt,en');
    config()->set('app.locale', 'lt');
    config()->set('app.fallback_locale', 'en');

    expect(TranslationService::isLocaleSupported('lt'))->toBeTrue()
        ->and(TranslationService::isLocaleSupported('ru'))->toBeFalse()
        ->and(TranslationService::getDefaultLocale())->toBe('lt')
        ->and(TranslationService::getFallbackLocale())->toBe('en');
});

it('get/choice resolve using normalized keys when translation missing', function () {
    $key = 'frontend.title';
    $normalized = TranslationService::normalizeKey($key);

    expect(TranslationService::get($key))->toBe($normalized)
        ->and(TranslationService::choice('messages.item', 2))->toBe('messages_item');
});
