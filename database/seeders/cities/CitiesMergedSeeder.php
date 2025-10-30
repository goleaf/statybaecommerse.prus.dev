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

        $locales = Locales::supported();

        $availableIso = Country::query()
            ->withoutGlobalScopes()
            ->pluck('cca2')
            ->map(static fn (string $iso): string => Str::upper($iso))
            ->all();

        $isoLookup = array_flip($availableIso);
        $discovery = $this->discoverSeeders();
        $selectedSeeders = $discovery['selected'];

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
}
