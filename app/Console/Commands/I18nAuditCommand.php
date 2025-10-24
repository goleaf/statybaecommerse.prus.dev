<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use JsonException;

final class I18nAuditCommand extends Command
{
    protected $signature = <<<'SIGNATURE'
        i18n:audit
            {fallback? : Override the configured fallback locale}
            {--format=text : Output format (text or json)}
            {--strict : Fail when untranslated strings are found}
    SIGNATURE;

    protected $description = 'Audit language files to detect missing, extra, or untranslated strings across locales.';

    public function __construct(private readonly Filesystem $filesystem)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $fallback = (string) ($this->argument('fallback') ?: config('app.fallback_locale', 'en'));
        $format = strtolower((string) $this->option('format') ?: 'text');
        $strict = (bool) $this->option('strict');

        if (! in_array($format, ['text', 'json'], true)) {
            $this->error("Invalid format [{$format}]. Supported formats: text, json.");

            return self::FAILURE;
        }

        $locales = $this->discoverLocales();

        if ($locales->isEmpty()) {
            $this->error('No locales were found in the resources/lang directory.');

            return self::FAILURE;
        }

        if (! $locales->contains($fallback)) {
            $this->error("The fallback locale [{$fallback}] does not exist.");

            return self::FAILURE;
        }

        $fallbackCatalogue = $this->collectCatalogue($fallback);

        $hasErrors = false;
        $hasWarnings = false;
        $results = [];

        foreach ($locales as $locale) {
            $catalogue = $this->collectCatalogue($locale);

            $missing = array_values(array_diff($fallbackCatalogue['keys'], $catalogue['keys']));
            $extra = array_values(array_diff($catalogue['keys'], $fallbackCatalogue['keys']));
            $untranslated = [];

            if ($locale !== $fallback) {
                foreach ($fallbackCatalogue['translations'] as $key => $fallbackValue) {
                    if (! array_key_exists($key, $catalogue['translations'])) {
                        continue;
                    }

                    $localeValue = $catalogue['translations'][$key];

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

                sort($untranslated);
            }

            sort($missing);
            sort($extra);

            if ($missing !== [] || $extra !== []) {
                $hasErrors = true;
            }

            if ($untranslated !== [] && $locale !== $fallback) {
                $hasWarnings = true;
            }

            $results[$locale] = [
                'missing'      => $missing,
                'extra'        => $extra,
                'untranslated' => $locale === $fallback ? [] : $untranslated,
            ];
        }

        $status = $this->renderReport($format, $fallback, $results, $hasErrors, $hasWarnings, $strict);

        return $status;
    }

    /**
     * @return array{keys: list<string>, translations: array<string, mixed>}
     */
    private function collectCatalogue(string $locale): array
    {
        $translations = [];
        $keys = [];

        foreach ($this->loadTranslations($locale) as $key => $value) {
            $keys[] = $key;
            $translations[$key] = $value;
        }

        sort($keys);

        return ['keys' => $keys, 'translations' => $translations];
    }

    /**
     * @return array<string, mixed>
     */
    private function loadTranslations(string $locale): array
    {
        $translations = [];
        $langPath = lang_path();
        $jsonFile = $langPath . DIRECTORY_SEPARATOR . "{$locale}.json";
        $rootPhpFile = $langPath . DIRECTORY_SEPARATOR . "{$locale}.php";
        $localeDirectory = $langPath . DIRECTORY_SEPARATOR . $locale;

        if ($this->filesystem->exists($jsonFile)) {
            $translations = array_merge($translations, $this->loadJson($jsonFile));
        }

        if ($this->filesystem->exists($rootPhpFile)) {
            $translations = array_merge($translations, $this->loadPhp($rootPhpFile));
        }

        if ($this->filesystem->isDirectory($localeDirectory)) {
            foreach ($this->filesystem->allFiles($localeDirectory) as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }

                $relative = trim(str_replace($localeDirectory, '', $file->getPathname()), DIRECTORY_SEPARATOR);
                $group = Str::replaceLast('.php', '', str_replace(DIRECTORY_SEPARATOR, '.', $relative));

                $translations = array_merge(
                    $translations,
                    $this->prependKeys($this->loadPhp($file->getPathname()), $group)
                );
            }
        }

        return $translations;
    }

    /**
     * @return array<string, mixed>
     */
    private function loadJson(string $path): array
    {
        try {
            $contents = $this->filesystem->get($path);
            $decoded = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return [];
        }

        if (! is_array($decoded)) {
            return [];
        }

        return array_filter(
            $decoded,
            static fn ($value, $key): bool => is_string($key),
            ARRAY_FILTER_USE_BOTH
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function loadPhp(string $path): array
    {
        $data = require $path;

        if (! is_array($data)) {
            return [];
        }

        return $this->flatten($data);
    }

    /**
     * @param  array<string, mixed>  $translations
     * @return array<string, mixed>
     */
    private function prependKeys(array $translations, string $prefix): array
    {
        if ($prefix === '') {
            return $translations;
        }

        $prefixed = [];

        foreach ($translations as $key => $value) {
            $prefixed[$prefix . '.' . $key] = $value;
        }

        return $prefixed;
    }

    /**
     * @param  array<mixed>  $values
     * @return array<string, mixed>
     */
    private function flatten(array $values, string $prefix = ''): array
    {
        $flattened = [];

        foreach ($values as $key => $value) {
            if (! is_string($key)) {
                continue;
            }

            $fullKey = $prefix !== '' ? "{$prefix}.{$key}" : $key;

            if (is_array($value)) {
                $flattened += $this->flatten($value, $fullKey);
            } else {
                $flattened[$fullKey] = $value;
            }
        }

        return $flattened;
    }

    private function renderReport(string $format, string $fallback, array $results, bool $hasErrors, bool $hasWarnings, bool $strict): int
    {
        if ($format === 'json') {
            $payload = [
                'fallback'     => $fallback,
                'results'      => $results,
                'has_errors'   => $hasErrors,
                'has_warnings' => $hasWarnings,
                'strict'       => $strict,
            ];

            $this->line(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            return ($hasErrors || ($strict && $hasWarnings)) ? self::FAILURE : self::SUCCESS;
        }

        $this->info(sprintf('Auditing translations (fallback: %s)', $fallback));

        foreach ($results as $locale => $report) {
            $missing = $report['missing'];
            $extra = $report['extra'];
            $untranslated = $report['untranslated'];

            if ($missing === [] && $extra === [] && $untranslated === []) {
                $this->line(" - {$locale}: OK");

                continue;
            }

            $this->line(" - {$locale}:");

            if ($missing !== []) {
                $this->error('   Missing ('.count($missing).'):');

                foreach ($missing as $key) {
                    $this->line("     • {$key}");
                }
            }

            if ($extra !== []) {
                $this->error('   Extra ('.count($extra).'):');

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

        if ($hasErrors) {
            $this->error('Translation audit found discrepancies.');
        } elseif ($hasWarnings) {
            if ($strict) {
                $this->error('Translation audit found untranslated strings (strict mode).');
            } else {
                $this->warn('Translation audit found untranslated strings.');
            }
        } else {
            $this->info('All locales are consistent');
        }

        return ($hasErrors || ($strict && $hasWarnings)) ? self::FAILURE : self::SUCCESS;
    }

    /**
     * @return Collection<int, string>
     */
    private function discoverLocales(): Collection
    {
        $langPath = lang_path();

        $locales = collect($this->filesystem->directories($langPath))
            ->map(static fn (string $directory): string => basename($directory));

        foreach ($this->filesystem->files($langPath) as $file) {
            $name = $file->getFilename();

            if (! is_string($name)) {
                continue;
            }

            if (preg_match('/^([\w-]+)\.(php|json)$/', $name, $matches) === 1) {
                $locales->push($matches[1]);
            }
        }

        $supportedConfig = config('app.supported_locales', []);
        $supportedLocales = collect();

        if (is_array($supportedConfig)) {
            $supportedLocales = collect($supportedConfig)->filter(static fn ($locale): bool => is_string($locale) && $locale !== '')->map(static fn (string $locale): string => trim($locale));
        } elseif (is_string($supportedConfig)) {
            $supportedLocales = collect(explode(',', $supportedConfig))
                ->map(static fn (string $locale): string => trim($locale))
                ->filter(static fn (string $locale): bool => $locale !== '');
        }

        $fallbackLocale = config('app.fallback_locale');
        if (is_string($fallbackLocale) && $fallbackLocale !== '') {
            $supportedLocales = $supportedLocales->push($fallbackLocale);
        }

        if ($supportedLocales->isNotEmpty()) {
            $supportedLocales = $supportedLocales->unique()->values();
            $locales = $locales
                ->filter(static fn (string $locale): bool => $supportedLocales->contains($locale))
                ->values();
        }

        return $locales->unique()->sort()->values();
    }
}
