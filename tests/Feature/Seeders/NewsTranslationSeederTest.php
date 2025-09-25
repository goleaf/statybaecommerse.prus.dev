<?php

declare(strict_types=1);

use App\Models\UiTranslation;
use Database\Seeders\NewsTranslationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates UI translations using database models instead of filesystem', function () {
    $seeder = new NewsTranslationSeeder;
    $seeder->run();

    // Verify translations were created in database
    expect(UiTranslation::count())->toBeGreaterThan(0);

    // Verify both locales are represented
    $ltTranslations = UiTranslation::where('locale', 'lt')->count();
    $enTranslations = UiTranslation::where('locale', 'en')->count();

    expect($ltTranslations)->toBeGreaterThan(0);
    expect($enTranslations)->toBeGreaterThan(0);
    expect($ltTranslations)->toBe($enTranslations);  // Should have same number of keys
});

it('creates translations with proper group assignment', function () {
    $seeder = new NewsTranslationSeeder;
    $seeder->run();

    // Verify all translations are assigned to 'news' group
    $newsTranslations = UiTranslation::where('group', 'news')->count();
    $totalTranslations = UiTranslation::count();

    expect($newsTranslations)->toBe($totalTranslations);
});

it('creates translations with proper key structure', function () {
    $seeder = new NewsTranslationSeeder;
    $seeder->run();

    // Verify key patterns
    $fieldsTranslations = UiTranslation::where('key', 'like', 'news.fields.%')->count();
    $filtersTranslations = UiTranslation::where('key', 'like', 'news.filters.%')->count();

    expect($fieldsTranslations)->toBeGreaterThan(0);
    expect($filtersTranslations)->toBeGreaterThan(0);

    // Verify specific keys exist
    expect(UiTranslation::where('key', 'news.fields.title')->exists())->toBeTrue();
    expect(UiTranslation::where('key', 'news.fields.content')->exists())->toBeTrue();
    expect(UiTranslation::where('key', 'news.filters.published_from')->exists())->toBeTrue();
});

it('creates translations with proper metadata', function () {
    $seeder = new NewsTranslationSeeder;
    $seeder->run();

    $translation = UiTranslation::first();
    expect($translation->metadata)->toBeArray();
    expect($translation->metadata)->toHaveKey('context');
    expect($translation->metadata)->toHaveKey('description');
    expect($translation->metadata)->toHaveKey('seeded_at');
    expect($translation->metadata['context'])->toBe('news_admin_interface');
});

it('creates Lithuanian translations with proper values', function () {
    $seeder = new NewsTranslationSeeder;
    $seeder->run();

    // Verify Lithuanian translations
    $titleLt = UiTranslation::where('key', 'news.fields.title')
        ->where('locale', 'lt')
        ->first();

    expect($titleLt)->not->toBeNull();
    expect($titleLt->value)->toBe('Pavadinimas');

    $contentLt = UiTranslation::where('key', 'news.fields.content')
        ->where('locale', 'lt')
        ->first();

    expect($contentLt)->not->toBeNull();
    expect($contentLt->value)->toBe('Turinys');
});

it('creates English translations with proper values', function () {
    $seeder = new NewsTranslationSeeder;
    $seeder->run();

    // Verify English translations
    $titleEn = UiTranslation::where('key', 'news.fields.title')
        ->where('locale', 'en')
        ->first();

    expect($titleEn)->not->toBeNull();
    expect($titleEn->value)->toBe('Title');

    $contentEn = UiTranslation::where('key', 'news.fields.content')
        ->where('locale', 'en')
        ->first();

    expect($contentEn)->not->toBeNull();
    expect($contentEn->value)->toBe('Content');
});

it('ensures unique key-locale combinations', function () {
    $seeder = new NewsTranslationSeeder;
    $seeder->run();

    // Verify no duplicate key-locale combinations
    $duplicates = UiTranslation::select('key', 'locale')
        ->groupBy('key', 'locale')
        ->havingRaw('COUNT(*) > 1')
        ->count();

    expect($duplicates)->toBe(0);
});

it('can retrieve translations using model methods', function () {
    $seeder = new NewsTranslationSeeder;
    $seeder->run();

    // Test getTranslation method
    $title = UiTranslation::getTranslation('news.fields.title', 'lt');
    expect($title)->toBe('Pavadinimas');

    $titleEn = UiTranslation::getTranslation('news.fields.title', 'en');
    expect($titleEn)->toBe('Title');

    // Test fallback functionality
    $nonExistent = UiTranslation::getTranslation('news.fields.nonexistent', 'lt', 'en');
    expect($nonExistent)->toBeNull();
});

it('can retrieve group translations', function () {
    $seeder = new NewsTranslationSeeder;
    $seeder->run();

    // Test getGroupTranslations method
    $newsTranslationsLt = UiTranslation::getGroupTranslations('news', 'lt');
    $newsTranslationsEn = UiTranslation::getGroupTranslations('news', 'en');

    expect($newsTranslationsLt)->toBeArray();
    expect($newsTranslationsEn)->toBeArray();
    expect(count($newsTranslationsLt))->toBe(count($newsTranslationsEn));

    expect($newsTranslationsLt)->toHaveKey('news.fields.title');
    expect($newsTranslationsLt['news.fields.title'])->toBe('Pavadinimas');

    expect($newsTranslationsEn)->toHaveKey('news.fields.title');
    expect($newsTranslationsEn['news.fields.title'])->toBe('Title');
});

it('handles running seeder multiple times without duplicates', function () {
    $seeder = new NewsTranslationSeeder;

    // Run seeder first time
    $seeder->run();
    $firstCount = UiTranslation::count();

    // Run seeder second time
    $seeder->run();
    $secondCount = UiTranslation::count();

    // Should not create duplicates due to factory's updateOrCreate behavior
    expect($secondCount)->toBe($firstCount);
});
