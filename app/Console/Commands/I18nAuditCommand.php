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

    protected $signature = 'i18n:audit
                            {--base= : Base locale used as the reference point}
                            {--format=table : Output format (table or json)}
                            {--strict : Fail when warnings (extra or untranslated strings) are found}';

    protected $description = 'Audit language files to detect missing, extra, or untranslated strings across locales.';

    public function __construct()
    {
        parent::__construct();

        $this->filesystem = new Filesystem;
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

        if (! $locales->contains($baseLocale)) {
            throw new RuntimeException("The base locale [{$baseLocale}] does not exist in resources/lang.");
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

        foreach ($translations as $locale => $localeTranslations) {
            if ($locale === $baseLocale) {
                continue;
            }

            if ($missing === [] && $extra === [] && $untranslated === []) {
                $this->line(" - {$locale}: OK");

                continue;
            }

            if ($missingKeys !== []) {
                $hasErrors = true;
            }

            if ($extraKeys !== [] || $untranslatedKeys !== []) {
                $hasWarnings = true;
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
        $locales = collect();

        foreach ($this->filesystem->directories($langPath) as $directory) {
            $locales->push(basename($directory));
        }

        foreach ($this->filesystem->files($langPath) as $file) {
            $matches = [];

            if (preg_match('/^([\w-]+)\.(php|json)$/i', $file->getFilename(), $matches) === 1) {
                $locales->push($matches[1]);
            }
        }

        return $locales->unique()->sort()->values();
    }

    /**
     * @return array<string, string>
     */
    private function loadTranslations(string $locale): array
    {
        $translations = [];
        $langPath = lang_path();
        $jsonFile = $langPath.'/'.$locale.'.json';
        $rootPhpFile = $langPath.'/'.$locale.'.php';
        $localeDirectory = $langPath.'/'.$locale;

        if ($this->filesystem->exists($jsonFile)) {
            $translations = array_merge($translations, $this->loadJsonTranslations($jsonFile));
        }

        if ($this->filesystem->exists($rootPhpFile)) {
            $translations = array_merge($translations, $this->loadPhpTranslations($rootPhpFile));
        }

        if ($this->filesystem->isDirectory($localeDirectory)) {
            foreach ($this->filesystem->allFiles($localeDirectory) as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }

                $relativePath = trim(str_replace($localeDirectory, '', $file->getPathname()), DIRECTORY_SEPARATOR);
                $group = str_replace(DIRECTORY_SEPARATOR, '/', substr($relativePath, 0, -4));

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

        ksort($translations);

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

        if (! is_array($decoded)) {
            $message = json_last_error() === JSON_ERROR_NONE
                ? 'Unexpected JSON structure.'
                : json_last_error_msg();

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
     * @return array<string, string>
     */
    private function loadPhpTranslations(string $path, ?string $group = null): array
    {
        /** @var array<mixed>|mixed $data */
        $data = require $path;

        if (! is_array($data)) {
            return [];
        }

        $flattened = Arr::dot($data);
        $translations = [];

        foreach ($flattened as $key => $value) {
            $translationKey = $group === null || $group === ''
                ? (string) $key
                : $group.'.'.$key;

            $translations[$translationKey] = $this->normalizeValue($value);
        }

        return $translations;
    }

    /**
     * @param  array<string, string>  $base
     * @param  array<string, string>  $locale
     * @return array<int, string>
     */
    private function detectUntranslatedKeys(array $base, array $locale): array
    {
        $keys = [];

        foreach (array_intersect_key($locale, $base) as $key => $value) {
            if ($this->normalizeValue($base[$key]) === $this->normalizeValue($value)) {
                $keys[] = $key;
            }
        }

        return $keys;
    }

    private function normalizeValue(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_scalar($value) || $value === null) {
            return trim((string) $value);
        }

        return trim(var_export($value, true));
    }

    /**
     * @param  array<string, array<string, array<int, string>>>  $report
     */
    private function outputReport(array $report, string $baseLocale, string $format): void
    {
        if ($format === 'json') {
            $payload = json_encode(
                [
                    'base_locale' => $baseLocale,
                    'locales' => $report,
                ],
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
            );

            if ($payload === false) {
                throw new RuntimeException('Failed to encode report as JSON: '.json_last_error_msg());
            }

            $this->line($payload);

            return;
        }

        if ($report === []) {
            $this->info("All locales match the base locale [{$baseLocale}].");

            return;
        }

        $this->line(sprintf('%-12s %-10s %-10s %-15s', 'Locale', 'Missing', 'Extra', 'Untranslated'));
        $this->line(str_repeat('-', 50));

        foreach ($report as $locale => $data) {
            $this->line(sprintf(
                '%-12s %-10d %-10d %-15d',
                $locale,
                count($data['missing']),
                count($data['extra']),
                count($data['untranslated'])
            ));
        }

        foreach ($report as $locale => $data) {
            if ($data['missing'] === [] && $data['extra'] === [] && $data['untranslated'] === []) {
                continue;
            }

            $this->newLine();
            $this->info("Locale: {$locale}");

            if ($data['missing'] !== []) {
                $this->line('  Missing keys:');
                foreach ($data['missing'] as $key) {
                    $this->line("    • {$key}");
                }
            }

            if ($data['extra'] !== []) {
                $this->line('  Extra keys:');
                foreach ($data['extra'] as $key) {
                    $this->line("    • {$key}");
                }
            }

            if ($data['untranslated'] !== []) {
                $this->line('  Untranslated keys (identical to base):');
                foreach ($data['untranslated'] as $key) {
                    $this->line("    • {$key}");
                }
            }
        }
    }
}
