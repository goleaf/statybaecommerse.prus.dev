<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\TranslationHookService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Test;
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
        if (!File::exists($this->tempLangPath)) {
            File::makeDirectory($this->tempLangPath, 0755, true);
        }
        
        // Override the lang_path helper function
        $this->app->useLanguagePath($this->tempLangPath);
        
        // Set up test configuration
        Config::set('app.locale', 'lt');
        Config::set('app.supported_locales', ['lt', 'en']);
        
        $this->service = new TranslationHookService();
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
        $reflection = new \ReflectionClass($this->service);
        
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
            'en' => 'Hello'
        ];

        $result = $this->service->addTranslation($key, $translations);

        expect($result)->toBeTrue();
        
        // Verify files were created
        expect(File::exists($this->tempLangPath . '/lt.json'))->toBeTrue();
        expect(File::exists($this->tempLangPath . '/en.json'))->toBeTrue();
        
        // Verify content
        $ltContent = json_decode(File::get($this->tempLangPath . '/lt.json'), true);
        $enContent = json_decode(File::get($this->tempLangPath . '/en.json'), true);
        
        expect($ltContent[$key])->toBe('Labas');
        expect($enContent[$key])->toBe('Hello');
    }

    #[Test]
    public function it_falls_back_to_default_locale_when_translation_missing(): void
    {
        $key = 'test.fallback';
        $translations = [
            'lt' => 'Lietuviškai'
        ];

        $result = $this->service->addTranslation($key, $translations);

        expect($result)->toBeTrue();
        
        $enContent = json_decode(File::get($this->tempLangPath . '/en.json'), true);
        expect($enContent[$key])->toBe('Lietuviškai'); // Should fallback to Lithuanian
    }

    #[Test]
    public function it_falls_back_to_key_when_no_translations_provided(): void
    {
        $key = 'test.no_translation';
        $translations = [];

        $result = $this->service->addTranslation($key, $translations);

        expect($result)->toBeTrue();
        
        $ltContent = json_decode(File::get($this->tempLangPath . '/lt.json'), true);
        expect($ltContent[$key])->toBe($key);
    }

    #[Test]
    public function it_logs_error_and_returns_false_on_exception(): void
    {
        Log::shouldReceive('error')
            ->once()
            ->with('Translation hook failed', \Mockery::type('array'));

        // Mock File::put to throw exception
        File::shouldReceive('put')
            ->andThrow(new \Exception('File write failed'));

        $result = $this->service->addTranslation('test.key', ['lt' => 'value']);

        expect($result)->toBeFalse();
    }

    #[Test]
    public function it_generates_translation_key_correctly(): void
    {
        $testCases = [
            ['Hello World', '', 'hello_world'],
            ['User Name', 'form', 'form.user_name'],
            ['Special!@#$%Characters', '', 'special_characters'],
            ['Multiple   Spaces', '', 'multiple_spaces'],
            ['_Leading_Trailing_', '', 'leading_trailing'],
        ];

        foreach ($testCases as [$text, $prefix, $expected]) {
            $result = $this->service->generateTranslationKey($text, $prefix);
            expect($result)->toBe($expected);
        }
    }

    #[Test]
    public function it_extracts_translatable_strings_from_blade_content(): void
    {
        $bladeContent = '
            <h1>{{ __("page.title") }}</h1>
            <p>@lang("page.description")</p>
            <span>{{ trans("page.subtitle") }}</span>
            <div><?php echo __("page.content"); ?></div>
        ';

        $keys = $this->service->extractTranslatableStrings($bladeContent);

        expect($keys)->toContain('page.title');
        expect($keys)->toContain('page.description');
        expect($keys)->toContain('page.subtitle');
        expect($keys)->toContain('page.content');
        expect(count($keys))->toBe(4);
    }

    #[Test]
    public function it_processes_blade_file_and_creates_missing_translations(): void
    {
        $bladeFile = $this->tempLangPath . '/test.blade.php';
        $bladeContent = '
            <h1>{{ __("missing.title") }}</h1>
            <p>{{ __("missing.description") }}</p>
        ';
        
        File::put($bladeFile, $bladeContent);

        $missingKeys = $this->service->processBladeFile($bladeFile);

        expect($missingKeys)->toContain('missing.title');
        expect($missingKeys)->toContain('missing.description');
        
        // Verify translations were created
        $ltContent = json_decode(File::get($this->tempLangPath . '/lt.json'), true);
        expect($ltContent['missing.title'])->toBe('Missing Title');
        expect($ltContent['missing.description'])->toBe('Missing Description');
    }

    #[Test]
    public function it_returns_empty_array_for_non_existent_blade_file(): void
    {
        $result = $this->service->processBladeFile('/non/existent/file.blade.php');
        expect($result)->toBe([]);
    }

    #[Test]
    public function it_syncs_translation_formats_correctly(): void
    {
        // Create JSON file
        $jsonTranslations = ['json.key' => 'JSON Value'];
        File::put($this->tempLangPath . '/lt.json', json_encode($jsonTranslations));
        
        // Create PHP file
        $phpTranslations = ['php.key' => 'PHP Value'];
        File::put($this->tempLangPath . '/lt.php', "<?php\n\nreturn " . var_export($phpTranslations, true) . ";\n");

        $this->service->syncTranslationFormats();

        // Verify PHP file contains both JSON and PHP translations
        $mergedTranslations = include $this->tempLangPath . '/lt.php';
        expect($mergedTranslations['json.key'])->toBe('JSON Value');
        expect($mergedTranslations['php.key'])->toBe('PHP Value');
    }

    #[Test]
    public function it_gets_missing_translations_for_locale(): void
    {
        // Set up translations
        $this->service->addTranslation('common.hello', ['lt' => 'Labas', 'en' => 'Hello']);
        $this->service->addTranslation('common.goodbye', ['lt' => 'Viso gero']); // Missing English

        $missingEn = $this->service->getMissingTranslations('en');

        expect($missingEn)->toHaveKey('common.goodbye');
        expect($missingEn)->not->toHaveKey('common.hello');
    }

    #[Test]
    public function it_generates_comprehensive_translation_report(): void
    {
        // Set up test data
        $this->service->addTranslation('test.key1', ['lt' => 'Value 1', 'en' => 'Value 1']);
        $this->service->addTranslation('test.key2', ['lt' => 'Value 2']); // Missing English

        $report = $this->service->generateTranslationReport();

        expect($report['total_keys'])->toBe(2);
        expect($report['locales']['lt']['translated'])->toBe(2);
        expect($report['locales']['lt']['missing'])->toBe(0);
        expect($report['locales']['lt']['completion_percentage'])->toBe(100.0);
        
        expect($report['locales']['en']['translated'])->toBe(1);
        expect($report['locales']['en']['missing'])->toBe(1);
        expect($report['locales']['en']['completion_percentage'])->toBe(50.0);
        expect($report['locales']['en']['missing_keys'])->toContain('test.key2');
    }

    #[Test]
    public function it_handles_string_supported_locales_configuration(): void
    {
        Config::set('app.supported_locales', 'lt,en,de');
        
        $service = new TranslationHookService();
        
        $reflection = new \ReflectionClass($service);
        $supportedLocalesProperty = $reflection->getProperty('supportedLocales');
        $supportedLocalesProperty->setAccessible(true);
        $supportedLocales = $supportedLocalesProperty->getValue($service);
        
        expect($supportedLocales)->toBe(['lt', 'en', 'de']);
    }

    #[Test]
    public function it_handles_array_supported_locales_configuration(): void
    {
        Config::set('app.supported_locales', ['lt', 'en', 'fr']);
        
        $service = new TranslationHookService();
        
        $reflection = new \ReflectionClass($service);
        $supportedLocalesProperty = $reflection->getProperty('supportedLocales');
        $supportedLocalesProperty->setAccessible(true);
        $supportedLocales = $supportedLocalesProperty->getValue($service);
        
        expect($supportedLocales)->toBe(['lt', 'en', 'fr']);
    }

    #[Test]
    public function it_falls_back_to_default_locales_when_config_invalid(): void
    {
        Config::set('app.supported_locales', null);
        
        $service = new TranslationHookService();
        
        $reflection = new \ReflectionClass($service);
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

        $content = File::get($this->tempLangPath . '/lt.json');
        $translations = json_decode($content, true);
        $keys = array_keys($translations);

        expect($keys)->toBe(['a.first', 'm.middle', 'z.last']);
    }

    #[Test]
    public function it_loads_existing_translation_files_on_initialization(): void
    {
        // Create existing translation file
        $existingTranslations = ['existing.key' => 'Existing Value'];
        File::put($this->tempLangPath . '/lt.json', json_encode($existingTranslations));

        $service = new TranslationHookService();

        // Add new translation
        $service->addTranslation('new.key', ['lt' => 'New Value']);

        // Verify both existing and new translations are present
        $content = json_decode(File::get($this->tempLangPath . '/lt.json'), true);
        expect($content['existing.key'])->toBe('Existing Value');
        expect($content['new.key'])->toBe('New Value');
    }

    #[Test]
    public function it_handles_malformed_json_files_gracefully(): void
    {
        // Create malformed JSON file
        File::put($this->tempLangPath . '/lt.json', '{"invalid": json}');

        $service = new TranslationHookService();
        $result = $service->addTranslation('test.key', ['lt' => 'Test Value']);

        expect($result)->toBeTrue();
        
        // Should overwrite malformed file with valid JSON
        $content = json_decode(File::get($this->tempLangPath . '/lt.json'), true);
        expect($content['test.key'])->toBe('Test Value');
    }
}