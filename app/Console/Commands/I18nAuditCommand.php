<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Finder\SplFileInfo;

final class I18nAuditCommand extends Command
{
    protected $signature = 'i18n:audit {fallback? : Override the configured fallback locale}';

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
        $locales = $this->discoverLocales();

        if ($locales === []) {
            $this->error('No locales found in the lang directory.');

            return self::FAILURE;
        }

        if (! in_array($fallback, $locales, true)) {
            $this->error("Fallback locale [{$fallback}] does not have any translation files.");

            return self::FAILURE;
        }

        $masterKeys = $this->collectLocaleKeys($fallback);
        sort($masterKeys);

        $this->info("Auditing translations (fallback: {$fallback})");

        $hasIssues = false;

        foreach ($locales as $locale) {
            $localeKeys = $this->collectLocaleKeys($locale);
            sort($localeKeys);

            $missing = array_values(array_diff($masterKeys, $localeKeys));
            $extra = array_values(array_diff($localeKeys, $masterKeys));

            if ($missing === [] && $extra === []) {
                $this->line(" - {$locale}: OK");

                continue;
            }

            $hasIssues = true;

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
        }

        if ($hasIssues) {
            $this->error('Translation audit found discrepancies.');

            return self::FAILURE;
        }

        $this->info('All locales match the fallback key set.');

        return self::SUCCESS;
    }

    /**
     * @return list<string>
     */
    private function collectLocaleKeys(string $locale): array
    {
        $keys = [];
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

                foreach ($this->flattenArrayKeys($data) as $key) {
                    $keys[] = $relative !== '' ? $relative.'.'.$key : $key;
                }
            }
        }

        $rootPhp = $langPath.DIRECTORY_SEPARATOR.$locale.'.php';
        if ($this->files->exists($rootPhp)) {
            $data = require $rootPhp;

            if (is_array($data)) {
                foreach ($this->flattenArrayKeys($data) as $key) {
                    $keys[] = $key;
                }
            }
        }

        $jsonPath = $langPath.DIRECTORY_SEPARATOR.$locale.'.json';
        if ($this->files->exists($jsonPath)) {
            $json = json_decode((string) $this->files->get($jsonPath), true, 512, JSON_THROW_ON_ERROR);

            foreach (array_keys($json) as $key) {
                $keys[] = (string) $key;
            }
        }

        $keys = array_values(array_unique($keys));
        sort($keys);

        return $keys;
    }

    /**
     * @return list<string>
     */
    private function flattenArrayKeys(array $data, string $prefix = ''): array
    {
        $keys = [];

        foreach ($data as $key => $value) {
            $key = (string) $key;
            $fullKey = $prefix !== '' ? $prefix.'.'.$key : $key;

            if (is_array($value) && $value !== []) {
                $keys = array_merge($keys, $this->flattenArrayKeys($value, $fullKey));

                continue;
            }

            $keys[] = $fullKey;
        }

        return $keys;
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
