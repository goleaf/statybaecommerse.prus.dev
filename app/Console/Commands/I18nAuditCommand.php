<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use JsonException;

final class I18nAuditCommand extends Command
{
    protected $signature = 'i18n:audit';

    protected $description = 'Audit translation keys across locales.';

    public function handle(Filesystem $filesystem): int
    {
        $langPath = lang_path();

        if (! $filesystem->isDirectory($langPath)) {
            $this->error("Language path [{$langPath}] does not exist.");

            return self::FAILURE;
        }

        $locales = $this->discoverLocales($filesystem, $langPath);

        if ($locales->isEmpty()) {
            $this->error('No translation locales were discovered.');

            return self::FAILURE;
        }

        $fallbackLocale = config('app.fallback_locale');

        if (empty($fallbackLocale)) {
            $this->error('The fallback locale is not configured.');

            return self::FAILURE;
        }

        if (! $locales->contains($fallbackLocale)) {
            $this->warn("Fallback locale [{$fallbackLocale}] not found in lang directory. Using discovered locales to build master set.");
        }

        $masterKeys = $this->collectLocaleKeys($filesystem, $langPath, $fallbackLocale);

        if ($masterKeys->isEmpty()) {
            $this->error("No translation keys found for fallback locale [{$fallbackLocale}].");

            return self::FAILURE;
        }

        $issues = [];

        foreach ($locales->sort() as $locale) {
            $keys = $this->collectLocaleKeys($filesystem, $langPath, $locale);

            $missing = $masterKeys->diff($keys)->values();
            $extra = $keys->diff($masterKeys)->values();

            if ($missing->isNotEmpty() || $extra->isNotEmpty()) {
                $issues[$locale] = [
                    'missing' => $missing->all(),
                    'extra' => $extra->all(),
                ];
            }
        }

        if (empty($issues)) {
            $this->components->info('All locales are consistent with the master translation key set.');

            return self::SUCCESS;
        }

        $this->displayIssues($issues);

        return self::FAILURE;
    }

    /**
     * @return Collection<int, string>
     */
    private function discoverLocales(Filesystem $filesystem, string $langPath): Collection
    {
        $directories = collect($filesystem->directories($langPath))
            ->map(static fn (string $path) => basename($path));

        $files = collect($filesystem->files($langPath))
            ->filter(static fn ($file) => in_array($file->getExtension(), ['php', 'json'], true))
            ->map(static fn ($file) => $file->getBasename('.'.$file->getExtension()));

        return $directories->merge($files)->unique()->values();
    }

    /**
     * @return Collection<int, string>
     */
    private function collectLocaleKeys(Filesystem $filesystem, string $langPath, string $locale): Collection
    {
        $keys = collect();

        $directory = $langPath.DIRECTORY_SEPARATOR.$locale;

        if ($filesystem->isDirectory($directory)) {
            foreach ($filesystem->allFiles($directory) as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }

                $prefix = str_replace(DIRECTORY_SEPARATOR, '.', $file->getRelativePathname());
                $prefix = preg_replace('/\.php$/', '', $prefix) ?? '';

                $data = $this->loadPhpTranslation($file->getPathname());

                $keys = $keys->merge($this->flattenTranslationArray($data, $prefix));
            }
        }

        $rootPhp = $langPath.DIRECTORY_SEPARATOR.$locale.'.php';

        if ($filesystem->exists($rootPhp)) {
            $data = $this->loadPhpTranslation($rootPhp);
            $keys = $keys->merge($this->flattenTranslationArray($data));
        }

        $rootJson = $langPath.DIRECTORY_SEPARATOR.$locale.'.json';

        if ($filesystem->exists($rootJson)) {
            try {
                $data = json_decode((string) $filesystem->get($rootJson), true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $exception) {
                throw new InvalidArgumentException("Invalid JSON in translation file [{$rootJson}]: {$exception->getMessage()}");
            }

            $keys = $keys->merge(array_keys($data));
        }

        return $keys->unique()->sort()->values();
    }

    /**
     * @return array<int, string>
     */
    private function flattenTranslationArray(array $translations, string $prefix = ''): array
    {
        $flattened = [];

        foreach ($translations as $key => $value) {
            $fullKey = $prefix !== '' ? $prefix.'.'.$key : (string) $key;

            if (is_array($value)) {
                $flattened = array_merge($flattened, $this->flattenTranslationArray($value, $fullKey));

                continue;
            }

            $flattened[] = $fullKey;
        }

        return $flattened;
    }

    /**
     * @return array<mixed>
     */
    private function loadPhpTranslation(string $path): array
    {
        $translations = require $path;

        if (! is_array($translations)) {
            throw new InvalidArgumentException("Translation file [{$path}] must return an array.");
        }

        return $translations;
    }

    /**
     * @param array<string, array<int, string>> $issues
     */
    private function displayIssues(array $issues): void
    {
        foreach ($issues as $locale => $diffs) {
            $this->components->error("Locale [{$locale}] has inconsistencies");

            if (! empty($diffs['missing'])) {
                $this->line('  Missing keys:');
                foreach ($diffs['missing'] as $key) {
                    $this->line("    - {$key}");
                }
            }

            if (! empty($diffs['extra'])) {
                $this->line('  Extra keys:');
                foreach ($diffs['extra'] as $key) {
                    $this->line("    + {$key}");
                }
            }

            $this->newLine();
        }
    }
}
