<?php declare(strict_types=1);

use App\Services\MultiLanguageTabService;

it('returns available languages with codes and flags', function (): void {
    config()->set('app-features.supported_locales', ['lt', 'en']);

    $langs = MultiLanguageTabService::getAvailableLanguages();

    expect($langs)
        ->toBeArray()
        ->and($langs)
        ->toHaveCount(2)
        ->and($langs[0])
        ->toHaveKeys(['code', 'name', 'flag'])
        ->and(collect($langs)->pluck('code')->all())
        ->toEqual(['lt', 'en']);
});

it('prefers lithuanian as default active tab', function (): void {
    config()->set('app-features.supported_locales', ['en', 'lt', 'de']);

    $index = MultiLanguageTabService::getDefaultActiveTab();

    expect($index)->toBeInt()->toBe(1);
});

it('prepares and splits translation data correctly', function (): void {
    config()->set('app-features.supported_locales', ['lt', 'en']);

    $formData = [
        'title_lt' => 'Pavadinimas',
        'title_en' => 'Title',
        'slug_lt' => 'pavadinimas',
        'slug_en' => 'title',
        'visible' => true,
    ];

    $result = MultiLanguageTabService::prepareTranslationData($formData, ['title', 'slug']);

    expect($result)
        ->toHaveKeys(['main_data', 'translations'])
        ->and($result['main_data'])
        ->toHaveKey('visible', true)
        ->and($result['translations']['lt']['title'])
        ->toBe('Pavadinimas')
        ->and($result['translations']['en']['slug'])
        ->toBe('title');
});
