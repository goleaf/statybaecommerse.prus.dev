<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use RuntimeException;
use Throwable;

final class I18nAuditCommand extends Command
{
    private Filesystem $filesystem;

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
        try {
            return $this->runAudit();
        } catch (Throwable $exception) {
            $message = 'I18n audit failed: '.$exception->getMessage();

            $this->error($message);
            fwrite(STDERR, $message.PHP_EOL);

            if ($this->output->isVerbose()) {
                $this->line($exception->getTraceAsString());
            }

            return self::FAILURE;
        }
    }

    private function runAudit(): int
    {
        $baseOption = $this->option('base');
        $defaultLocale = config('app.locale', 'en');

        if (! is_string($defaultLocale) || $defaultLocale === '') {
            throw new RuntimeException('The configured app.locale value must be a non-empty string.');
        }

        $baseLocale = is_string($baseOption) && $baseOption !== ''
            ? $baseOption
            : $defaultLocale;

        $formatOption = $this->option('format');
        $format = is_string($formatOption) && $formatOption !== ''
            ? strtolower($formatOption)
            : 'table';

        if (! in_array($format, ['table', 'json'], true)) {
            throw new RuntimeException("Unsupported format [{$format}]. Use 'table' or 'json'.");
        }

        $locales = $this->discoverLocales();

        if ($locales->isEmpty()) {
            throw new RuntimeException('No locales were discovered in the lang directory.');
        }

        if (! $locales->contains($baseLocale)) {
            throw new RuntimeException("The base locale [{$baseLocale}] does not exist in resources/lang.");
        }

        $translations = $locales
            ->mapWithKeys(fn (string $locale) => [$locale => $this->loadTranslations($locale)])
            ->all();

        $baseTranslations = $translations[$baseLocale];
        $report = [];
        $hasErrors = false;
        $hasWarnings = false;

        foreach ($translations as $locale => $localeTranslations) {
            if ($locale === $baseLocale) {
                continue;
            }

            $missingKeys = array_keys(array_diff_key($baseTranslations, $localeTranslations));
            $extraKeys = array_keys(array_diff_key($localeTranslations, $baseTranslations));
            $untranslatedKeys = $this->detectUntranslatedKeys($baseTranslations, $localeTranslations);

            if ($missingKeys !== []) {
                $hasErrors = true;
            }

            if ($extraKeys !== [] || $untranslatedKeys !== []) {
                $hasWarnings = true;
            }

            sort($missingKeys);
            sort($extraKeys);
            sort($untranslatedKeys);

            $report[$locale] = [
                'missing' => $missingKeys,
                'extra' => $extraKeys,
                'untranslated' => $untranslatedKeys,
            ];
        }

        $this->outputReport($report, $baseLocale, $format);

        if ($hasErrors || ($hasWarnings && $this->option('strict'))) {
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * @return Collection<int, string>
     */
    private function discoverLocales(): Collection
    {
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

                $translations = array_merge(
                    $translations,
                    $this->loadPhpTranslations($file->getPathname(), $group)
                );
            }
        }

        ksort($translations);

        return $translations;
    }

    /**
     * @return array<string, string>
     */
    private function loadJsonTranslations(string $path): array
    {
        $contents = $this->filesystem->get($path);
        $decoded = json_decode($contents, true);

        if (! is_array($decoded)) {
            $message = json_last_error() === JSON_ERROR_NONE
                ? 'Unexpected JSON structure.'
                : json_last_error_msg();

            throw new RuntimeException("Failed to decode JSON translations from [{$path}]: {$message}");
        }

        $translations = [];

        foreach ($decoded as $key => $value) {
            $translations['__json__.'.$key] = $this->normalizeValue($value);
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
