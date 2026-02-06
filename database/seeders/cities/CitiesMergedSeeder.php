<?php

declare(strict_types=1);

namespace Database\Seeders\Cities;

use App\Models\Country;
use App\Support\Locales;
use Illuminate\Database\Seeder;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Orchestrator that consolidates all city seeders into a single transaction-aware entry point.
 */
final class CitiesMergedSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            throw new RuntimeException('City seeders cannot run in production environments.');
        }

        $locales = $this->resolveLocales();

        $availableIso = Country::query()
            ->withoutGlobalScopes()
            ->pluck('cca2')
            ->map(static fn (string $iso): string => Str::upper($iso))
            ->all();

        $isoLookup = array_flip($availableIso);
        $discovery = $this->discoverSeeders();
        $selectedSeeders = $discovery['selected'];

        if ($this->isFastModeEnabled()) {
            $selectedSeeders = $this->trimSeedersForFastMode($selectedSeeders);
        }

        foreach ($discovery['duplicates'] as $duplicate) {
            $this->command?->warn(sprintf('Skipping duplicate city seeder %s in favour of the larger dataset.', $duplicate));
        }

        foreach ($selectedSeeders as $iso2 => $meta) {
            if (! array_key_exists($iso2, $isoLookup)) {
                $this->command?->warn(sprintf('Skipping %s because country %s is not present in the database.', $meta['class'], $iso2));

                continue;
            }

            $results = CitySeederToolkit::upsertForCountry($iso2, $meta['rows'], $locales);
            $this->command?->info(sprintf(
                'Seeded cities for %s (created: %d, updated: %d, skipped: %d).',
                $iso2,
                $results['created'],
                $results['updated'],
                $results['skipped'],
            ));
        }
    }

    /**
     * @return array{
     *     selected: array<string, array{class: class-string, rows: array<int, array<string, mixed>>}>,
     *     duplicates: array<int, string>,
     * }
     */
    private function discoverSeeders(): array
    {
        $filesystem = app(Filesystem::class);
        $seedersPath = database_path('seeders/cities');
        $files = $filesystem->exists($seedersPath) ? $filesystem->files($seedersPath) : [];
        $namespace = __NAMESPACE__ . '\\';
        $selected = [];
        $duplicates = [];

        foreach ($files as $file) {

            if ($file->getExtension() !== 'php') {
                continue;
            }

            $class = $namespace . $file->getFilenameWithoutExtension();

            if (! class_exists($class)) {
                continue;
            }

            if (! method_exists($class, 'iso2') || ! method_exists($class, 'data')) {
                continue;
            }

            if (in_array($class, [self::class, CitySeederToolkit::class], true)) {
                continue;
            }

            $iso2 = Str::upper((string) $class::iso2());
            $rows = Collection::make($class::data())->map(static function ($row): array {
                return $row instanceof Collection ? $row->all() : (array) $row;
            })->all();
            $rowCount = count($rows);

            if (isset($selected[$iso2])) {
                if ($rowCount > count($selected[$iso2]['rows'])) {
                    $duplicates[] = $selected[$iso2]['class'];
                    $selected[$iso2] = ['class' => $class, 'rows' => $rows];
                } else {
                    $duplicates[] = $class;
                }

                continue;
            }

            $selected[$iso2] = ['class' => $class, 'rows' => $rows];
        }

        ksort($selected);

        return ['selected' => $selected, 'duplicates' => $duplicates];
    }

    private function isFastModeEnabled(): bool
    {
        return (bool) config('seeds.fast_mode', false);
    }

    /**
     * @return array<int, string>
     */
    private function resolveLocales(): array
    {
        $supportedLocales = Locales::supported();

        if (! $this->isFastModeEnabled()) {
            return $supportedLocales;
        }

        $configuredLocales = config('seeds.fast.locales', ['lt', 'en']);
        $allowedLocales = collect(is_array($configuredLocales) ? $configuredLocales : [])
            ->map(static fn (mixed $locale): string => Str::lower(trim((string) $locale)))
            ->filter()
            ->values();

        $locales = collect($supportedLocales)
            ->map(static fn (string $locale): string => Str::lower($locale))
            ->intersect($allowedLocales)
            ->values()
            ->all();

        if ($locales !== []) {
            return $locales;
        }

        return collect($supportedLocales)
            ->map(static fn (string $locale): string => Str::lower($locale))
            ->take(2)
            ->values()
            ->all();
    }

    /**
     * @param  array<string, array{class: class-string, rows: array<int, array<string, mixed>>}>  $selectedSeeders
     * @return array<string, array{class: class-string, rows: array<int, array<string, mixed>>}>
     */
    private function trimSeedersForFastMode(array $selectedSeeders): array
    {
        $isoFilter = $this->fastIso2List();
        $maxRowsPerCountry = max(1, (int) config('seeds.fast.max_cities_per_country', 80));

        if ($isoFilter !== []) {
            $selectedSeeders = array_intersect_key($selectedSeeders, array_flip($isoFilter));
        }

        return collect($selectedSeeders)
            ->map(static fn (array $meta): array => [
                'class' => $meta['class'],
                'rows'  => array_slice($meta['rows'], 0, $maxRowsPerCountry),
            ])
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function fastIso2List(): array
    {
        $configured = config('seeds.fast.city_iso2', ['LT', 'DE']);

        return collect(is_array($configured) ? $configured : [])
            ->map(static fn (mixed $iso): string => Str::upper(trim((string) $iso)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
