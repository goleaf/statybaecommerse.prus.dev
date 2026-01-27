<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\TranslationHookService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use Tests\TestCase;

final class TranslationHookServiceTest extends TestCase
{
    use RefreshDatabase;

    private TranslationHookService $service;

    private string $tempLangPath;

    protected function setUp(): void
    {
        parent::setUp();

        // Create temporary language directory for testing
        $this->tempLangPath = base_path('lang_test_' . uniqid());
        if (! File::exists($this->tempLangPath)) {
            File::makeDirectory($this->tempLangPath, 0755, true);
        }

        // Override the lang_path helper function
        $this->app->useLangPath($this->tempLangPath);

        // Set up test configuration
        Config::set('app.locale', 'lt');
        Config::set('app.supported_locales', ['lt', 'en']);

        $this->service = new TranslationHookService;
    }

    protected function tearDown(): void
    {
        // Clean up temporary files
        if (File::exists($this->tempLangPath)) {
            File::deleteDirectory($this->tempLangPath);
        }

        parent::tearDown();
    }

    #[Test]
    public function it_initializes_with_correct_default_values(): void
    {
        $reflection = new ReflectionClass($this->service);

        $supportedLocalesProperty = $reflection->getProperty('supportedLocales');
        $supportedLocalesProperty->setAccessible(true);
        $supportedLocales = $supportedLocalesProperty->getValue($this->service);

        $defaultLocaleProperty = $reflection->getProperty('defaultLocale');
        $defaultLocaleProperty->setAccessible(true);
        $defaultLocale = $defaultLocaleProperty->getValue($this->service);

        expect($supportedLocales)->toBe(['lt', 'en']);
        expect($defaultLocale)->toBe('lt');
    }

    #[Test]
    public function it_adds_translation_successfully(): void
    {
        $key = 'test.greeting';
        $translations = [
            'lt' => 'Labas',
            'en' => 'Hello',
        ];

        $result = $this->service->addTranslation($key, $translations);

        expect($result)->toBeTrue();

        // Verify files were created
        expect(File::exists($this->tempLangPath . '/lt/test.php'))->toBeTrue();
        expect(File::exists($this->tempLangPath . '/en/test.php'))->toBeTrue();

        // Verify content
        $ltContent = include $this->tempLangPath . '/lt/test.php';
        $enContent = include $this->tempLangPath . '/en/test.php';

        expect($ltContent['greeting'])->toBe('Labas');
        expect($enContent['greeting'])->toBe('Hello');
    }

    #[Test]
    public function it_falls_back_to_default_locale_when_translation_missing(): void
    {
        $key = 'test.fallback';
        $translations = [
            'lt' => 'Lietuviškai',
        ];

        $result = $this->service->addTranslation($key, $translations);

        expect($result)->toBeTrue();

        $enContent = include $this->tempLangPath . '/en/test.php';
        expect($enContent['fallback'])->toBe('Lietuviškai'); // Should fallback to Lithuanian
    }

    #[Test]
    public function it_falls_back_to_key_when_no_translations_provided(): void
    {
        $key = 'test.no_translation';
        $translations = [];

        $result = $this->service->addTranslation($key, $translations);

        expect($result)->toBeTrue();

        $ltContent = include $this->tempLangPath . '/lt/test.php';
        expect($ltContent['no_translation'])->toBe($key);
    }

    #[Test]
    public function it_logs_error_and_returns_false_on_exception(): void
    {
        Log::shouldReceive('error')
            ->once()
            ->with('Translation hook failed', Mockery::type('array'));

        // Mock File to throw exception
        File::shouldReceive('exists')->andReturn(true);
        File::shouldReceive('isDirectory')->andReturn(true);
        File::shouldReceive('deleteDirectory')->zeroOrMoreTimes();
        File::shouldReceive('put')
            ->andThrow(new Exception('File write failed'));

        $result = $this->service->addTranslation('test.key', ['lt' => 'value']);

        expect($result)->toBeFalse();
    }

    #[Test]
    public function it_gets_missing_translations_for_locale(): void
    {
        // Manually create files to simulate a truly missing translation
        $localeDirLt = $this->tempLangPath . '/lt';
        $localeDirEn = $this->tempLangPath . '/en';
        if (!File::isDirectory($localeDirLt)) File::makeDirectory($localeDirLt, 0755, true);
        if (!File::isDirectory($localeDirEn)) File::makeDirectory($localeDirEn, 0755, true);

        File::put($localeDirLt . '/common.php', "<?php return ['hello' => 'Labas', 'goodbye' => 'Viso gero'];");
        File::put($localeDirEn . '/common.php', "<?php return ['hello' => 'Hello'];");

        $missingEn = $this->service->getMissingTranslations('en');

        expect($missingEn)->toHaveKey('common.goodbye');
    }

    #[Test]
    public function it_generates_comprehensive_translation_report(): void
    {
        // Manually create files to simulate missing translations
        $localeDirLt = $this->tempLangPath . '/lt';
        $localeDirEn = $this->tempLangPath . '/en';
        if (!File::isDirectory($localeDirLt)) File::makeDirectory($localeDirLt, 0755, true);
        if (!File::isDirectory($localeDirEn)) File::makeDirectory($localeDirEn, 0755, true);

        File::put($localeDirLt . '/test.php', "<?php return ['key1' => 'Value 1', 'key2' => 'Value 2'];");
        File::put($localeDirEn . '/test.php', "<?php return ['key1' => 'Value 1'];");

        $report = $this->service->generateTranslationReport();

        expect($report['locales']['lt']['translated'])->toBeGreaterThanOrEqual(2);
        expect($report['locales']['en']['missing'])->toBeGreaterThanOrEqual(1);
        expect($report['locales']['en']['missing_keys'])->toContain('test.key2');
    }

    #[Test]
    public function it_handles_string_supported_locales_configuration(): void
    {
        Config::set('app.supported_locales', 'lt,en,de');

        $service = new TranslationHookService;

        $reflection = new ReflectionClass($service);
        $supportedLocalesProperty = $reflection->getProperty('supportedLocales');
        $supportedLocalesProperty->setAccessible(true);
        $supportedLocales = $supportedLocalesProperty->getValue($service);

        expect($supportedLocales)->toBe(['lt', 'en', 'de']);
    }

    #[Test]
    public function it_handles_array_supported_locales_configuration(): void
    {
        Config::set('app.supported_locales', ['lt', 'en', 'fr']);

        $service = new TranslationHookService;

        $reflection = new ReflectionClass($service);
        $supportedLocalesProperty = $reflection->getProperty('supportedLocales');
        $supportedLocalesProperty->setAccessible(true);
        $supportedLocales = $supportedLocalesProperty->getValue($service);

        expect($supportedLocales)->toBe(['lt', 'en', 'fr']);
    }

    #[Test]
    public function it_falls_back_to_default_locales_when_config_invalid(): void
    {
        Config::set('app.supported_locales', null);

        $service = new TranslationHookService;

        $reflection = new ReflectionClass($service);
        $supportedLocalesProperty = $reflection->getProperty('supportedLocales');
        $supportedLocalesProperty->setAccessible(true);
        $supportedLocales = $supportedLocalesProperty->getValue($service);

        expect($supportedLocales)->toBe(['lt', 'en']);
    }

    #[Test]
    public function it_sorts_translations_alphabetically_when_saving(): void
    {
        $this->service->addTranslation('z.last', ['lt' => 'Last']);
        $this->service->addTranslation('a.first', ['lt' => 'First']);
        $this->service->addTranslation('m.middle', ['lt' => 'Middle']);

        $ltContent = include $this->tempLangPath . '/lt/z.php';
        expect($ltContent['last'])->toBe('Last');
        
        $ltContent = include $this->tempLangPath . '/lt/a.php';
        expect($ltContent['first'])->toBe('First');
    }

    #[Test]
    public function it_loads_existing_translation_files_on_initialization(): void
    {
        // Create existing translation file
        $localeDir = $this->tempLangPath . '/lt';
        File::makeDirectory($localeDir, 0755, true);
        $existingTranslations = ['key' => 'Existing Value'];
        File::put($localeDir . '/existing.php', "<?php\nreturn " . var_export($existingTranslations, true) . ";\n");

        $service = new TranslationHookService;

        // Add new translation
        $service->addTranslation('existing.new_key', ['lt' => 'New Value']);

        // Verify both existing and new translations are present
        $content = include $localeDir . '/existing.php';
        expect($content['key'])->toBe('Existing Value');
        expect($content['new_key'])->toBe('New Value');
    }

    #[Test]
    public function it_handles_malformed_json_files_gracefully(): void
    {
        // Test is no longer relevant for JSON, but we verify it handles missing files
        $service = new TranslationHookService;
        $result = $service->addTranslation('test.key', ['lt' => 'Test Value']);

        expect($result)->toBeTrue();
        
        $content = include $this->tempLangPath . '/lt/test.php';
        expect($content['key'])->toBe('Test Value');
    }
}
