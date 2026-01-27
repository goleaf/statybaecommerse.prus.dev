<?php

declare(strict_types=1);

use App\Services\TranslationHookService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(TranslationHookService::class);

    // Ensure directories exist
    if (! File::isDirectory(lang_path())) {
        File::makeDirectory(lang_path(), 0755, true);
    }
    if (! File::isDirectory(resource_path('views/test'))) {
        File::makeDirectory(resource_path('views/test'), 0755, true);
    }
});

afterEach(function () {
    // Clean up test files
    File::deleteDirectory(resource_path('views/test'));
    foreach (['lt', 'en', 'de', 'ru'] as $locale) {
        $dir = lang_path($locale);
        if (File::isDirectory($dir)) {
            File::delete($dir . '/test.php');
            File::delete($dir . '/missing.php');
            File::delete($dir . '/page.php');
            File::delete($dir . '/buttons.php');
            File::delete($dir . '/include.php');
        }
    }
});

it('scans blade files and reports missing translations', function () {
    // Create test blade file with missing translations
    $testFile = resource_path('views/test/sample.blade.php');
    File::put($testFile, '<div>{{ __("missing.key") }}</div>');

    $this->artisan('translation:hook scan --path=' . str_replace('\\', '/', resource_path('views/test')))
        ->expectsOutput('Scanning Blade files in: ' . str_replace('\\', '/', resource_path('views/test')))
        ->assertExitCode(0);
});

it('fixes missing translations when using fix flag', function () {
    $testFile = resource_path('views/test/sample.blade.php');
    File::put($testFile, '<div>{{ __("test.auto_fix") }}</div>');

    $this->artisan('translation:hook scan --path=' . str_replace('\\', '/', resource_path('views/test')) . ' --fix')
        ->assertExitCode(0);

    // Verify translation was created
    $ltFile = lang_path('lt/test.php');
    expect(File::exists($ltFile))->toBeTrue();

    $translations = include $ltFile;
    expect($translations)->toHaveKey('auto_fix');
});

it('generates translation report', function () {
    // Create test translations
    $localeDirLt = lang_path('lt');
    $localeDirEn = lang_path('en');
    if (!File::isDirectory($localeDirLt)) File::makeDirectory($localeDirLt, 0755, true);
    if (!File::isDirectory($localeDirEn)) File::makeDirectory($localeDirEn, 0755, true);
    
    File::put($localeDirLt . '/test.php', "<?php return ['key1' => 'Testas 1'];");
    File::put($localeDirEn . '/test.php', "<?php return ['key2' => 'Test 2'];");

    $this->artisan('translation:hook report')
        ->expectsOutput('Translation Report')
        ->assertExitCode(0);
});

it('syncs translations between locales', function () {
    // Create test translations with missing keys
    $localeDirLt = lang_path('lt');
    $localeDirEn = lang_path('en');
    if (!File::isDirectory($localeDirLt)) File::makeDirectory($localeDirLt, 0755, true);
    if (!File::isDirectory($localeDirEn)) File::makeDirectory($localeDirEn, 0755, true);

    File::put($localeDirLt . '/test.php', "<?php return ['key1' => 'Testas 1', 'key2' => 'Testas 2'];");
    File::put($localeDirEn . '/test.php', "<?php return ['key1' => 'Test 1'];");

    $this->artisan('translation:hook sync')
        ->assertExitCode(0);

    // Verify missing key was added
    $enTranslations = include $localeDirEn . '/test.php';
    expect($enTranslations)->toHaveKey('key2');
});

it('processes blade files and creates translations', function () {
    $testFile = resource_path('views/test/complex.blade.php');
    File::put($testFile, '
        <div>
            <h1>{{ __("page.title") }}</h1>
            <p>{{ __("page.description") }}</p>
            <button>{{ __("buttons.save") }}</button>
        </div>
    ');

    $this->artisan('translation:hook process --path=' . str_replace('\\', '/', resource_path('views/test')))
        ->assertExitCode(0);

    // Verify all translations were created
    expect(File::exists(lang_path('lt/page.php')))->toBeTrue();
    expect(File::exists(lang_path('lt/buttons.php')))->toBeTrue();

    $pageTranslations = include lang_path('lt/page.php');
    $buttonTranslations = include lang_path('lt/buttons.php');
    
    expect($pageTranslations)->toHaveKey('title');
    expect($pageTranslations)->toHaveKey('description');
    expect($buttonTranslations)->toHaveKey('save');
});

it('handles invalid blade files gracefully', function () {
    $testFile = resource_path('views/test/invalid.blade.php');
    File::put($testFile, '<div>{{ __("invalid.syntax" }}</div>'); // Missing closing parenthesis

    $this->artisan('translation:hook scan --path=' . str_replace('\\', '/', resource_path('views/test')))
        ->assertExitCode(0); // Should not fail, just skip invalid files
});

it('respects exclude patterns', function () {
    // Create test files
    File::put(resource_path('views/test/include.blade.php'), '<div>{{ __("include.key") }}</div>');
    File::put(resource_path('views/test/exclude.blade.php'), '<div>{{ __("exclude.key") }}</div>');

    $this->artisan('translation:hook scan --path=' . str_replace('\\', '/', resource_path('views/test')) . ' --exclude=exclude.blade.php --fix')
        ->assertExitCode(0);

    // Verify only included file was processed
    $ltFile = lang_path('lt/include.php');
    $translations = include $ltFile;
    expect($translations)->toHaveKey('key');
    expect(File::exists(lang_path('lt/exclude.php')))->toBeFalse();
});
