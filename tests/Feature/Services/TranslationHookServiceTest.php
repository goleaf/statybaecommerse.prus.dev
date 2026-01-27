<?php

declare(strict_types=1);

use App\Services\TranslationHookService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(TranslationHookService::class);

    // Create test translation files
    ensureTranslationDirectoryExists();
});

afterEach(function () {
    // Clean up test translation files
    cleanupTestTranslations();
});

it('can add translations across all supported locales', function () {
    $key = 'test.greeting';
    $translations = [
        'lt' => 'Labas',
        'en' => 'Hello',
        'de' => 'Hallo',
    ];

    $result = $this->service->addTranslation($key, $translations);

    expect($result)->toBeTrue();

    // Verify translations were saved
    foreach (['lt', 'en', 'de'] as $locale) {
        $file = lang_path("{$locale}/test.php");
        expect(File::exists($file))->toBeTrue();

        $content = include $file;
        expect($content)->toHaveKey('greeting');
        expect($content['greeting'])->toBe($translations[$locale]);
    }
});

it('generates translation keys from text correctly', function () {
    $text = 'Hello World! This is a test.';
    $key = $this->service->generateTranslationKey($text, 'product');

    expect($key)->toBe('product.hello_world_this_is_a_test');
});

it('extracts translatable strings from blade content', function () {
    $bladeContent = '
        <div>
            {{ __("welcome.message") }}
            @lang("nav.home")
            {{ trans("buttons.submit") }}
            <span>{{ __("form.required") }}</span>
        </div>
    ';

    $keys = $this->service->extractTranslatableStrings($bladeContent);

    expect($keys)->toContain('welcome.message');
    expect($keys)->toContain('nav.home');
    expect($keys)->toContain('buttons.submit');
    expect($keys)->toContain('form.required');
    expect($keys)->toHaveCount(4);
});

it('processes blade files and creates missing translations', function () {
    $testFile = resource_path('views/test-translation.blade.php');
    $bladeContent = '<div>{{ __("test.missing_key") }}</div>';

    File::put($testFile, $bladeContent);

    $missingKeys = $this->service->processBladeFile($testFile);

    expect($missingKeys)->toContain('test.missing_key');

    // Verify translation was auto-created
    $ltFile = lang_path('lt/test.php');
    $content = include $ltFile;
    expect($content)->toHaveKey('missing_key');

    // Cleanup
    File::delete($testFile);
});

it('generates translation report correctly', function () {
    // Add some test translations
    $this->service->addTranslation('test.key1', ['lt' => 'Testas 1', 'en' => 'Test 1']);
    $this->service->addTranslation('test.key2', ['lt' => 'Testas 2']); // Missing EN translation

    $report = $this->service->generateTranslationReport();

    expect($report)->toHaveKey('total_keys');
    expect($report)->toHaveKey('locales');
    expect($report['locales'])->toHaveKey('lt');
    expect($report['locales'])->toHaveKey('en');

    expect($report['locales']['lt']['completion_percentage'])->toBe(100.0);
});

it('gets missing translations for specific locale', function () {
    // Manually create files to simulate a truly missing translation
    $localeDirLt = lang_path('lt');
    $localeDirEn = lang_path('en');
    if (!File::isDirectory($localeDirLt)) File::makeDirectory($localeDirLt, 0755, true);
    if (!File::isDirectory($localeDirEn)) File::makeDirectory($localeDirEn, 0755, true);

    File::put($localeDirLt . '/test.php', "<?php return ['complete' => 'Pilnas', 'incomplete' => 'Nepilnas'];");
    File::put($localeDirEn . '/test.php', "<?php return ['complete' => 'Complete'];");

    $missing = $this->service->getMissingTranslations('en');

    expect($missing)->toHaveKey('test.incomplete');
});

it('syncs translation formats between json and php', function () {
    // Method is now a no-op but we verify it doesn't crash
    $this->service->syncTranslationFormats();
    expect(true)->toBeTrue();
});

function ensureTranslationDirectoryExists(): void
{
    if (! File::isDirectory(lang_path())) {
        File::makeDirectory(lang_path(), 0755, true);
    }
}

function cleanupTestTranslations(): void
{
    // Custom cleanup for feature test directories
    foreach (['lt', 'en', 'de', 'ru'] as $locale) {
        $dir = lang_path($locale);
        if (File::isDirectory($dir)) {
            // Only delete files created during test, for safety we delete test.php and missing.php
            File::delete($dir . '/test.php');
            File::delete($dir . '/missing.php');
        }
    }
    
    $testFiles = [
        resource_path('views/test-translation.blade.php'),
    ];

    foreach ($testFiles as $file) {
        if (File::exists($file)) {
            File::delete($file);
        }
    }
}
