<?php

declare(strict_types=1);

use App\Services\TranslationHookService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(TranslationHookService::class);

    // Create test translation files
    $this->ensureTranslationDirectoryExists();
});

afterEach(function () {
    // Clean up test translation files
    $this->cleanupTestTranslations();
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
        $file = lang_path("{$locale}.json");
        expect(File::exists($file))->toBeTrue();

        $content = json_decode(File::get($file), true);
        expect($content)->toHaveKey($key);
        expect($content[$key])->toBe($translations[$locale]);
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
    $ltFile = lang_path('lt.json');
    $content = json_decode(File::get($ltFile), true);
    expect($content)->toHaveKey('test.missing_key');

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
    expect($report['locales']['en']['completion_percentage'])->toBeLessThan(100.0);
});

it('gets missing translations for specific locale', function () {
    $this->service->addTranslation('test.complete', ['lt' => 'Pilnas', 'en' => 'Complete']);
    $this->service->addTranslation('test.incomplete', ['lt' => 'Nepilnas']); // Missing EN

    $missing = $this->service->getMissingTranslations('en');

    expect($missing)->toHaveKey('test.incomplete');
    expect($missing)->not->toHaveKey('test.complete');
});

it('syncs translation formats between json and php', function () {
    // Create a PHP translation file
    $phpContent = "<?php\nreturn ['php.key' => 'PHP Value'];";
    File::put(lang_path('lt.php'), $phpContent);

    // Add JSON translation
    $this->service->addTranslation('json.key', ['lt' => 'JSON Value']);

    $this->service->syncTranslationFormats();

    // Verify PHP file was updated with JSON content
    $phpTranslations = include lang_path('lt.php');
    expect($phpTranslations)->toHaveKey('json.key');
    expect($phpTranslations['json.key'])->toBe('JSON Value');
});

function ensureTranslationDirectoryExists(): void
{
    if (! File::isDirectory(lang_path())) {
        File::makeDirectory(lang_path(), 0755, true);
    }
}

function cleanupTestTranslations(): void
{
    $testFiles = [
        lang_path('lt.json'),
        lang_path('en.json'),
        lang_path('de.json'),
        lang_path('lt.php'),
        resource_path('views/test-translation.blade.php'),
    ];

    foreach ($testFiles as $file) {
        if (File::exists($file)) {
            File::delete($file);
        }
    }
}
