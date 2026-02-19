<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Lang;

it('feature: falls back to the configured locale when translation is missing', function (): void {
    $translator = Lang::getFacadeRoot();
    $originalLocale = $translator->getLocale();
    $fallbackLocale = config('app.fallback_locale');

    $translator->setLocale('zz');

    $key = 'validation.accepted';

    $value = $translator->get($key);
    $expected = $translator->get($key, [], $fallbackLocale);

    $translator->setLocale($originalLocale);

    expect($value)->toBe($expected);
    expect($value)->not->toBe($key);
});

it('feature: resolves mixed-case admin translation group keys', function (): void {
    $translator = Lang::getFacadeRoot();
    $originalLocale = $translator->getLocale();

    $translator->setLocale('en');

    $lower = $translator->get('admin.system_setting_categories.plural_model_label');
    $mixedCase = $translator->get('Admin.system_setting_categories.plural_model_label');

    $translator->setLocale($originalLocale);

    expect($mixedCase)->toBe($lower);
    expect($mixedCase)->not->toBe('Admin.system_setting_categories.plural_model_label');
});
