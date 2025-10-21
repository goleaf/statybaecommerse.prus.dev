<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use JsonException;
use RuntimeException;
use Symfony\Component\Finder\SplFileInfo;

final class I18nAuditCommand extends Command
{
    protected $signature = 'i18n:audit
        {fallback? : Override the configured fallback locale}
        {--format=text : Output format (text or json)}
        {--strict : Fail when warnings are present}';

    protected $description = 'Audit translation keys across locales using the fallback locale as the source of truth.';

    private Filesystem $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    public function handle(): int
    {
        $fallback = (string) ($this->argument('fallback') ?: config('app.fallback_locale'));
        $format = (string) ($this->option('format') ?: 'text');
        $strict = (bool) $this->option('strict');
        $locales = $this->discoverLocales();

        if (! in_array($format, ['text', 'json'], true)) {
            $this->error("Invalid format [{$format}]. Supported formats: text, json.");

            return self::FAILURE;
        }

        if ($locales === []) {
            $this->error('No locales found in the lang directory.');

            return self::FAILURE;
        }

        if (! in_array($fallback, $locales, true)) {
            $this->error("Fallback locale [{$fallback}] does not have any translation files.");

            return self::FAILURE;
        }

        try {
            $fallbackCatalogue = $this->collectLocaleCatalogue($fallback);
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        if ($format === 'text') {
            $this->info("Auditing translations (fallback: {$fallback})");
        }

        $hasErrors = false;
        $hasWarnings = false;

        $report = [
            'fallback' => $fallback,
            'results' => [],
        ];

        foreach ($locales as $locale) {
            try {
                $localeCatalogue = $this->collectLocaleCatalogue($locale);
            } catch (RuntimeException $exception) {
                $this->error($exception->getMessage());

                return self::FAILURE;
            }

            $missing = array_values(array_diff($fallbackCatalogue['keys'], $localeCatalogue['keys']));
            $extra = array_values(array_diff($localeCatalogue['keys'], $fallbackCatalogue['keys']));
            $untranslated = [];

            if ($locale !== $fallback) {
                foreach ($fallbackCatalogue['translations'] as $key => $fallbackValue) {
                    if (! array_key_exists($key, $localeCatalogue['translations'])) {
                        continue;
                    }

                    $localeValue = $localeCatalogue['translations'][$key];

                    if (! is_string($fallbackValue) || ! is_string($localeValue)) {
                        continue;
                    }

                    if (trim($fallbackValue) === '' || trim($localeValue) === '') {
                        continue;
                    }

                    if ($fallbackValue === $localeValue) {
                        $untranslated[] = $key;
                    }
                }
            }

            sort($untranslated);

            $hasErrors = $hasErrors || $missing !== [] || $extra !== [];
            $hasWarnings = $hasWarnings || ($locale !== $fallback && $untranslated !== []);

            $report['results'][$locale] = [
                'missing' => $missing,
                'extra' => $extra,
                'untranslated' => $locale !== $fallback ? $untranslated : [],
            ];

            if ($format !== 'text') {
                continue;
            }

            if ($locale === $fallback) {
                $this->line(" - {$locale}: fallback locale");

                continue;
            }

            if ($missing === [] && $extra === [] && $untranslated === []) {
                $this->line(" - {$locale}: OK");

                continue;
            }

            $this->line(" - {$locale}:");

            if ($missing !== []) {
                $this->warn('   Missing ('.count($missing).'):');

                foreach ($missing as $key) {
                    $this->line("     • {$key}");
                }
            }

            if ($extra !== []) {
                $this->warn('   Extra ('.count($extra).'):');

                foreach ($extra as $key) {
                    $this->line("     • {$key}");
                }
            }

            if ($untranslated !== []) {
                $this->warn('   Untranslated ('.count($untranslated).'):');

                foreach ($untranslated as $key) {
                    $this->line("     • {$key}");
                }
            }
        }

        $status = 'ok';

        if ($hasErrors) {
            $status = 'error';
        } elseif ($hasWarnings) {
            $status = 'warning';
        }

        if ($format === 'json') {
            $report['status'] = $status;
            $report['hasErrors'] = $hasErrors;
            $report['hasWarnings'] = $hasWarnings;
            $report['strict'] = $strict;

            $json = json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            if ($json === false) {
                $this->error('Failed to encode audit report as JSON.');

                return self::FAILURE;
            }

            $this->line($json);
        } elseif ($hasErrors) {
            $this->error('Translation audit found discrepancies.');
        } elseif ($hasWarnings) {
            if ($strict) {
                $this->error('Translation audit found untranslated strings (strict mode).');
            } else {
                $this->warn('Translation audit found untranslated strings.');
            }
        } else {
            $this->info('All locales match the fallback key set.');
        }

        if ($hasErrors || ($strict && $hasWarnings)) {
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * @return array{keys: list<string>, translations: array<string, mixed>}
     */
    private function collectLocaleCatalogue(string $locale): array
    {
        $translations = [];
        $langPath = lang_path();

        $directory = $langPath.DIRECTORY_SEPARATOR.$locale;
        if ($this->files->isDirectory($directory)) {
            /** @var SplFileInfo $file */
            foreach ($this->files->allFiles($directory) as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }

                $relative = str_replace(['\\', '/'], '.', $file->getRelativePathname());
                $relative = (string) preg_replace('/\.php$/', '', $relative);

                $data = require $file->getRealPath();

                if (! is_array($data)) {
                    continue;
                }

                foreach ($this->flattenTranslations($data) as $key => $value) {
                    $fullKey = $relative !== '' ? $relative.'.'.$key : $key;
                    $translations[$fullKey] = $value;
                }
            }
        }

        $rootPhp = $langPath.DIRECTORY_SEPARATOR.$locale.'.php';
        if ($this->files->exists($rootPhp)) {
            $data = require $rootPhp;

            if (is_array($data)) {
                foreach ($this->flattenTranslations($data) as $key => $value) {
                    $translations[$key] = $value;
                }
            }
        }

        $jsonPath = $langPath.DIRECTORY_SEPARATOR.$locale.'.json';
        if ($this->files->exists($jsonPath)) {
            try {
                $json = json_decode((string) $this->files->get($jsonPath), true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $exception) {
                throw new RuntimeException("Failed to decode JSON translation file [{$jsonPath}]: {$exception->getMessage()}", 0, $exception);
            }

            if (! is_array($json)) {
                throw new RuntimeException("JSON translation file [{$jsonPath}] must decode to an object of key/value pairs.");
            }

            foreach ($json as $key => $value) {
                if (is_array($value)) {
                    continue;
                }

                $translations[(string) $key] = is_string($value) ? $value : (string) $value;
            }
        }

        $keys = array_keys($translations);
        sort($keys);

        return [
            'keys' => $keys,
            'translations' => $translations,
        ];
    }

    /**
     * @param  array<mixed>  $data
     * @return array<string, mixed>
     */
    private function flattenTranslations(array $data, string $prefix = ''): array
    {
        $translations = [];

        foreach ($data as $key => $value) {
            $key = (string) $key;
            $fullKey = $prefix !== '' ? $prefix.'.'.$key : $key;

            if (is_array($value) && $value !== []) {
                foreach ($this->flattenTranslations($value, $fullKey) as $nestedKey => $nestedValue) {
                    $translations[$nestedKey] = $nestedValue;
                }

                continue;
            }

            $translations[$fullKey] = $value;
        }

        return $translations;
    }

    /**
     * @return list<string>
     */
    private function discoverLocales(): array
    {
        $langPath = lang_path();

        if (! $this->files->isDirectory($langPath)) {
            return [];
        }

        $locales = [];

        foreach ($this->files->directories($langPath) as $directory) {
            $locales[] = basename($directory);
        }

        foreach ($this->files->files($langPath) as $file) {
            $extension = $file->getExtension();

            if (! in_array($extension, ['php', 'json'], true)) {
                continue;
            }

            $locales[] = $file->getBasename('.'.$extension);
        }

        $locales = array_values(array_unique($locales));
        sort($locales);

        return $locales;
    }
}
