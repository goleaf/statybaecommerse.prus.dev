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
    collect(['lt.json', 'en.json'])->each(fn ($file) => File::delete(lang_path($file)));
});

it('scans blade files and reports missing translations', function () {
    // Create test blade file with missing translations
    $testFile = resource_path('views/test/sample.blade.php');
    File::put($testFile, '<div>{{ __("missing.key") }}</div>');

    $this->artisan('translation:hook scan --path=' . resource_path('views/test'))
        ->expectsOutput('Scanning Blade files in: ' . resource_path('views/test'))
        ->assertExitCode(0);
});

it('fixes missing translations when using fix flag', function () {
    $testFile = resource_path('views/test/sample.blade.php');
    File::put($testFile, '<div>{{ __("test.auto_fix") }}</div>');

    $this->artisan('translation:hook scan --path=' . resource_path('views/test') . ' --fix')
        ->assertExitCode(0);

    // Verify translation was created
    $ltFile = lang_path('lt.json');
    expect(File::exists($ltFile))->toBeTrue();

    $translations = json_decode(File::get($ltFile), true);
    expect($translations)->toHaveKey('test.auto_fix');
});

it('generates translation report', function () {
    // Create test translations
    File::put(lang_path('lt.json'), json_encode(['test.key1' => 'Testas 1']));
    File::put(lang_path('en.json'), json_encode(['test.key2' => 'Test 2']));

    $this->artisan('translation:hook report')
        ->expectsOutput('Translation Report')
        ->assertExitCode(0);
});

it('syncs translations between locales', function () {
    // Create test translations with missing keys
    File::put(lang_path('lt.json'), json_encode([
        'test.key1' => 'Testas 1',
        'test.key2' => 'Testas 2',
    ]));
    File::put(lang_path('en.json'), json_encode([
        'test.key1' => 'Test 1',
        // Missing key2
    ]));

    $this->artisan('translation:hook sync')
        ->assertExitCode(0);

    // Verify missing key was added
    $enTranslations = json_decode(File::get(lang_path('en.json')), true);
    expect($enTranslations)->toHaveKey('test.key2');
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

    $this->artisan('translation:hook process --path=' . resource_path('views/test'))
        ->assertExitCode(0);

    // Verify all translations were created
    $ltFile = lang_path('lt.json');
    expect(File::exists($ltFile))->toBeTrue();

    $translations = json_decode(File::get($ltFile), true);
    expect($translations)->toHaveKey('page.title');
    expect($translations)->toHaveKey('page.description');
    expect($translations)->toHaveKey('buttons.save');
});

it('handles invalid blade files gracefully', function () {
    $testFile = resource_path('views/test/invalid.blade.php');
    File::put($testFile, '<div>{{ __("invalid.syntax" }}</div>'); // Missing closing parenthesis

    $this->artisan('translation:hook scan --path=' . resource_path('views/test'))
        ->assertExitCode(0); // Should not fail, just skip invalid files
});

it('respects exclude patterns', function () {
    // Create test files
    File::put(resource_path('views/test/include.blade.php'), '<div>{{ __("include.key") }}</div>');
    File::put(resource_path('views/test/exclude.blade.php'), '<div>{{ __("exclude.key") }}</div>');

    $this->artisan('translation:hook scan --path=' . resource_path('views/test') . ' --exclude=exclude.blade.php --fix')
        ->assertExitCode(0);

    // Verify only included file was processed
    $ltFile = lang_path('lt.json');
    $translations = json_decode(File::get($ltFile), true);
    expect($translations)->toHaveKey('include.key');
    expect($translations)->not->toHaveKey('exclude.key');
});
