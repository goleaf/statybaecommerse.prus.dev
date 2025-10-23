<?php

declare(strict_types=1);

it('unit: default locales are a unique list of strings', function (): void {
    $locales = config('filament-language-tabs.default_locales');

    expect($locales)->toBeArray();
    foreach ($locales as $locale) {
        expect($locale)->toBeString()->not->toBe('');
    }
    // Ensure uniqueness
    expect(array_values(array_unique($locales)))->toEqual($locales);
});

it('unit: required locales is a non-empty list', function (): void {
    $required = config('filament-language-tabs.required_locales');

    expect($required)->toBeArray();
    expect($required)->not->toBeEmpty();
});
