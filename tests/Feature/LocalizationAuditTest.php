<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Tests\TestCase;

final class LocalizationAuditTest extends TestCase
{
    private Filesystem $filesystem;

    protected function setUp(): void
    {
        parent::setUp();

        $this->filesystem = new Filesystem;
    }

    protected function tearDown(): void
    {
        $this->cleanupLocale('test-fallback');
        $this->cleanupLocale('test-locale');
        config()->set('app.fallback_locale', 'en');
        app()->setLocale('en');

        parent::tearDown();
    }

    public function test_audit_command_reports_missing_and_extra_keys(): void
    {
        config()->set('app.fallback_locale', 'test-fallback');

        $this->writeLocale('test-fallback', [
            'audit' => [
                'general' => [
                    'greeting' => 'Hello',
                    'farewell' => 'Goodbye',
                ],
            ],
        ], [
            'json-greeting' => 'Hello from JSON',
        ]);

        $this->writeLocale('test-locale', [
            'audit' => [
                'general' => [
                    'greeting' => 'Sveiki',
                    'extra'    => 'Papildomas',
                ],
            ],
        ], [
            'json-extra' => 'Papildomas',
        ]);

        $exitCode = Artisan::call('i18n:audit');
        $output = Artisan::output();

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Auditing translations (fallback: test-fallback)', $output);
        $this->assertStringContainsString(' - test-fallback: OK', $output);
        $this->assertStringContainsString(' - test-locale:', $output);
        $this->assertStringContainsString('Missing (2):', $output);
        $this->assertStringContainsString('• audit.general.farewell', $output);
        $this->assertStringContainsString('• json-greeting', $output);
        $this->assertStringContainsString('Extra (2):', $output);
        $this->assertStringContainsString('• audit.general.extra', $output);
        $this->assertStringContainsString('• json-extra', $output);
        $this->assertStringContainsString('Translation audit found discrepancies.', $output);
    }

    public function test_translations_fall_back_to_configured_locale(): void
    {
        config()->set('app.fallback_locale', 'test-fallback');
        config()->set('app.locale', 'test-locale');

        $this->writeLocale('test-fallback', [
            'audit' => [
                'general' => [
                    'greeting' => 'Hello',
                    'farewell' => 'Goodbye',
                ],
            ],
        ]);

        $this->writeLocale('test-locale', [
            'audit' => [
                'general' => [
                    'greeting' => 'Sveiki',
                ],
            ],
        ]);

        app()->setLocale('test-locale');

        $this->assertSame('Sveiki', __('audit.general.greeting'));
        $this->assertSame('Goodbye', __('audit.general.farewell'));
    }

    public function test_notification_translation_keys_are_defined(): void
    {
        config()->set('app.fallback_locale', 'en');
        app()->setLocale('en');

        $keys = [
            'legal.actions.create',
            'legal.actions.preview',
            'legal.actions.publish',
            'legal.actions.unpublish',
            'legal.actions.duplicate',
            'legal.notifications.published',
            'legal.notifications.unpublished',
            'legal.notifications.duplicated',
            'admin.inventory.actions.create',
        ];

        foreach ($keys as $key) {
            $translation = __($key);
            $this->assertFalse(Str::startsWith($translation, ['_', $key]), "Missing translation for {$key}");
            $this->assertNotSame($key, $translation, "Missing translation for {$key}");
        }
    }

    /**
     * @param array<mixed> $php
     * @param array<mixed> $json
     */
    private function writeLocale(string $locale, array $php, array $json = []): void
    {
        $directory = lang_path($locale);

        if (! $this->filesystem->isDirectory($directory)) {
            $this->filesystem->makeDirectory($directory, 0755, true);
        }

        $phpPath = $directory . '/audit.php';
        $phpContent = "<?php\n\nreturn " . var_export($php, true) . ";\n";
        $this->filesystem->put($phpPath, $phpContent);

        if ($json !== []) {
            $jsonPath = lang_path("{$locale}.json");
            $this->filesystem->put($jsonPath, json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
    }

    private function cleanupLocale(string $locale): void
    {
        $directory = lang_path($locale);

        if ($this->filesystem->isDirectory($directory)) {
            $this->filesystem->deleteDirectory($directory);
        }

        $jsonPath = lang_path("{$locale}.json");

        if ($this->filesystem->exists($jsonPath)) {
            $this->filesystem->delete($jsonPath);
        }
    }
}
