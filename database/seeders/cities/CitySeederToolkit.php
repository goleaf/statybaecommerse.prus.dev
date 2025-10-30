<?php

declare(strict_types=1);

namespace Database\Seeders\Cities;

use App\Models\City;
use App\Models\Country;
use App\Models\Translations\CityTranslation;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Shared helper responsible for normalising and seeding city records across the curated country seeders.
 */
final class CitySeederToolkit
{
    /**
     * Upsert the provided city rows for a specific country while keeping translations synchronised.
     *
     * @param  iterable<int, array<string, mixed>>          $rows
     * @param  array<int, string>                           $locales
     * @return array{created:int, updated:int, skipped:int}
     */
    public static function upsertForCountry(string $countryIso2, iterable $rows, array $locales): array
    {
        if (app()->environment('production')) {
            throw new RuntimeException('City seeders are disabled in production environments to protect live data.');
        }

        $iso2 = Str::upper($countryIso2);

        /** @var Country|null $country */
        $country = Country::query()
            ->withoutGlobalScopes()
            ->where('cca2', $iso2)
            ->first();

        if ($country === null) {
            throw new RuntimeException(sprintf('Unable to locate a country with ISO2 code %s while seeding cities.', $iso2));
        }

        $localeList = self::normaliseLocales($locales);
        $baseLocale = config('app.locale', 'en');
        $fallbackLocale = config('app.fallback_locale', $baseLocale);
        $now = Carbon::now();

        $existingCityIdsBySlug = City::query()
            ->withoutGlobalScopes()
            ->where('country_id', $country->getKey())
            ->pluck('id', 'slug')
            ->all();

        $existingCodes = City::query()
            ->withoutGlobalScopes()
            ->pluck('code')
            ->filter()
            ->all();

        $slugAssignments = $existingCityIdsBySlug;
        $codeAssignments = array_fill_keys(array_map(static fn ($code) => (string) $code, $existingCodes), true);
        $preparedRows = [];
        $translationMap = [];
        $created = 0;
        $updated = 0;
        $skipped = 0;
        $rowIndex = 0;

        foreach ($rows as $row) {
            $rowIndex++;
            $payload = self::toArray($row);

            if ($payload === []) {
                $skipped++;

                continue;
            }

            $nameContext = self::extractNames($payload);

            if ($nameContext['fallback'] === null) {
                // Without a deterministic name we cannot create a slug or translation entry, so skip the row.
                $skipped++;

                continue;
            }

            $slug = self::determineSlug($payload, $nameContext['fallback'], $slugAssignments, $rowIndex);
            $code = array_key_exists('code', $payload) ? (string) $payload['code'] : null;
            $isExisting = array_key_exists($slug, $existingCityIdsBySlug);
            $isDuplicateRow = array_key_exists($slug, $preparedRows);

            if ($isDuplicateRow) {
                // Later occurrences of the same slug in the dataset should be ignored to avoid overriding curated records.
                $skipped++;

                continue;
            }

            if ($code !== null && $code !== '' && array_key_exists($code, $codeAssignments)) {
                // Skip entries that reuse an existing code to honour the unique constraint introduced by earlier migrations.
                $skipped++;

                continue;
            }

            $cityRow = self::buildCityRow(
                $country->getKey(),
                $slug,
                $payload,
                $nameContext['fallback'],
                $nameContext['descriptionFallback'],
                $now,
            );

            $preparedRows[$slug] = $cityRow;
            $translationMap[$slug] = self::buildTranslations(
                $nameContext['translations'],
                $nameContext['descriptions'],
                $localeList,
                $nameContext['fallback'],
                $nameContext['descriptionFallback'],
                $baseLocale,
                $fallbackLocale,
            );

            if ($code !== null && $code !== '') {
                $codeAssignments[$code] = true;
            }

            if ($isExisting) {
                $updated++;
            } else {
                $created++;
            }
        }

        if ($preparedRows === []) {
            return ['created' => 0, 'updated' => 0, 'skipped' => $skipped];
        }

        $chunks = array_chunk(array_values($preparedRows), 500);
        $slugs = array_keys($preparedRows);
        $driver = City::query()->getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            DB::transaction(static function () use ($preparedRows): void {
                foreach ($preparedRows as $row) {
                    $attributes = Arr::only($row, ['country_id', 'slug']);
                    $values = Arr::except($row, ['country_id', 'slug']);

                    City::query()
                        ->withoutGlobalScopes()
                        ->updateOrCreate($attributes, $values);
                }
            });
        } else {
            DB::transaction(static function () use ($chunks): void {
                foreach ($chunks as $chunk) {
                    City::query()
                        ->withoutGlobalScopes()
                        ->upsert(
                            $chunk,
                            ['country_id', 'slug'],
                            self::updatableColumns(),
                        );
                }
            });
        }

        // Fetch the final city identifiers to link translations correctly after the upsert completes.
        $cityIdsBySlug = City::query()
            ->withoutGlobalScopes()
            ->where('country_id', $country->getKey())
            ->whereIn('slug', $slugs)
            ->pluck('id', 'slug')
            ->all();

        DB::transaction(static function () use ($translationMap, $cityIdsBySlug, $now): void {
            foreach ($translationMap as $slug => $translations) {
                $cityId = $cityIdsBySlug[$slug] ?? null;

                if ($cityId === null) {
                    continue;
                }

                foreach ($translations as $locale => $translation) {
                    CityTranslation::query()->updateOrCreate(
                        [
                            'city_id' => $cityId,
                            'locale'  => $locale,
                        ],
                        [
                            'name'        => $translation['name'],
                            'description' => $translation['description'],
                            'updated_at'  => $now,
                        ],
                    );
                }
            }
        });

        return ['created' => $created, 'updated' => $updated, 'skipped' => $skipped];
    }

    /**
     * @param  array<string, array{name:?string}>                     $translations
     * @param  array<string, array{description:?string}>              $descriptions
     * @param  array<int, string>                                     $locales
     * @return array<string, array{name:string, description:?string}>
     */
    private static function buildTranslations(
        array $translations,
        array $descriptions,
        array $locales,
        string $fallbackName,
        ?string $fallbackDescription,
        string $baseLocale,
        string $fallbackLocale
    ): array {
        $result = [];

        foreach ($locales as $locale) {
            $name = $translations[$locale]['name']
                ?? $translations[$fallbackLocale]['name']
                ?? $translations[$baseLocale]['name']
                ?? $fallbackName;

            $description = $descriptions[$locale]['description']
                ?? $descriptions[$fallbackLocale]['description']
                ?? $descriptions[$baseLocale]['description']
                ?? $fallbackDescription;

            $result[$locale] = [
                'name'        => $name,
                'description' => $description,
            ];
        }

        return $result;
    }

    /**
     * Normalise the payload destined for the `cities` table.
     *
     * @return array<string, mixed>
     */
    private static function buildCityRow(
        int $countryId,
        string $slug,
        array $payload,
        string $fallbackName,
        ?string $fallbackDescription,
        Carbon $now
    ): array {
        $data = [
            'country_id' => $countryId,
            'slug'       => $slug,
            'name'       => $fallbackName,
            'updated_at' => $now,
            'created_at' => $now,
        ];

        if ($fallbackDescription !== null) {
            $data['description'] = $fallbackDescription;
        }

        foreach (self::copyableColumns() as $column => $sources) {
            foreach ($sources as $source) {
                if (! array_key_exists($source, $payload)) {
                    continue;
                }

                $value = $payload[$source];

                if (in_array($column, ['latitude', 'longitude'], true)) {
                    $value = self::normaliseCoordinate($value);
                }

                if (in_array($column, ['metadata', 'postal_codes'], true)) {
                    $value = self::normaliseArray($value);
                }

                if ($value === null) {
                    continue;
                }

                $data[$column] = $value;
                break;
            }
        }

        return $data;
    }

    /**
     * Copyable columns map to raw payload keys so legacy seeders can retain curated metadata.
     *
     * @return array<string, array<int, string>>
     */
    private static function copyableColumns(): array
    {
        return [
            'code'            => ['code'],
            'is_default'      => ['is_default'],
            'is_capital'      => ['is_capital'],
            'is_enabled'      => ['is_enabled'],
            'is_active'       => ['is_active'],
            'zone_id'         => ['zone_id'],
            'region_id'       => ['region_id'],
            'level'           => ['level'],
            'latitude'        => ['latitude', 'lat'],
            'longitude'       => ['longitude', 'lng'],
            'population'      => ['population'],
            'postal_codes'    => ['postal_codes'],
            'sort_order'      => ['sort_order'],
            'metadata'        => ['metadata', 'meta'],
            'type'            => ['type'],
            'area'            => ['area'],
            'density'         => ['density'],
            'elevation'       => ['elevation'],
            'timezone'        => ['timezone'],
            'currency_code'   => ['currency_code'],
            'currency_symbol' => ['currency_symbol'],
            'language_code'   => ['language_code'],
            'language_name'   => ['language_name'],
            'phone_code'      => ['phone_code'],
            'postal_code'     => ['postal_code'],
        ];
    }

    /**
     * Provide the list of columns that should be updated when an existing record is encountered.
     *
     * @return array<int, string>
     */
    private static function updatableColumns(): array
    {
        return [
            'name',
            'description',
            'code',
            'is_default',
            'is_capital',
            'is_enabled',
            'is_active',
            'zone_id',
            'region_id',
            'level',
            'latitude',
            'longitude',
            'population',
            'postal_codes',
            'sort_order',
            'metadata',
            'type',
            'area',
            'density',
            'elevation',
            'timezone',
            'currency_code',
            'currency_symbol',
            'language_code',
            'language_name',
            'phone_code',
            'postal_code',
            'updated_at',
        ];
    }

    /**
     * Extract name and description translations from the source payload in a consistent structure.
     *
     * @return array{
     *     fallback:?string,
     *     descriptionFallback:?string,
     *     translations:array<string, array{name:?string}>,
     *     descriptions:array<string, array{description:?string}>,
     * }
     */
    private static function extractNames(array $payload): array
    {
        $translations = [];
        $descriptions = [];
        $fallbackName = null;
        $fallbackDescription = null;

        if (array_key_exists('name', $payload)) {
            $name = $payload['name'];

            if (is_array($name)) {
                foreach ($name as $locale => $value) {
                    $translations[(string) $locale]['name'] = self::stringOrNull($value);
                }
            } else {
                $fallbackName = self::stringOrNull($name);
            }
        }

        if (array_key_exists('description', $payload)) {
            $description = $payload['description'];

            if (is_array($description)) {
                foreach ($description as $locale => $value) {
                    $descriptions[(string) $locale]['description'] = self::stringOrNull($value);
                }
            } else {
                $fallbackDescription = self::stringOrNull($description);
            }
        }

        if (array_key_exists('translations', $payload) && is_array($payload['translations'])) {
            foreach ($payload['translations'] as $locale => $translation) {
                if (is_array($translation)) {
                    if (array_key_exists('name', $translation)) {
                        $translations[(string) $locale]['name'] = self::stringOrNull($translation['name']);
                    }

                    if (array_key_exists('description', $translation)) {
                        $descriptions[(string) $locale]['description'] = self::stringOrNull($translation['description']);
                    }
                } else {
                    $translations[(string) $locale]['name'] = self::stringOrNull($translation);
                }
            }
        }

        if ($fallbackName === null) {
            $fallbackName = $translations[config('app.locale', 'en')]['name']
                ?? $translations[config('app.fallback_locale', 'en')]['name']
                ?? ($translations ? reset($translations)['name'] : null);
        }

        if ($fallbackDescription === null) {
            $fallbackDescription = $descriptions[config('app.locale', 'en')]['description']
                ?? $descriptions[config('app.fallback_locale', 'en')]['description']
                ?? ($descriptions ? reset($descriptions)['description'] : null);
        }

        return [
            'fallback'            => $fallbackName,
            'descriptionFallback' => $fallbackDescription,
            'translations'        => $translations,
            'descriptions'        => $descriptions,
        ];
    }

    /**
     * Determine a unique slug for the provided city payload.
     */
    private static function determineSlug(array $payload, string $fallbackName, array &$slugAssignments, int $rowIndex): string
    {
        $providedSlug = array_key_exists('slug', $payload) ? (string) $payload['slug'] : null;
        $baseSlug = $providedSlug !== null && $providedSlug !== ''
            ? Str::slug($providedSlug)
            : Str::slug($fallbackName);

        if ($baseSlug === '') {
            $baseSlug = 'city-' . $rowIndex;
        }

        $slug = $baseSlug;
        $suffix = 2;

        while (array_key_exists($slug, $slugAssignments)) {
            $slug = $baseSlug . '-' . $suffix;
            $suffix++;
        }

        $slugAssignments[$slug] = null;

        return $slug;
    }

    /**
     * Normalise coordinates provided as strings or numbers into float values.
     */
    private static function normaliseCoordinate(mixed $value): ?float
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof Model) {
            return null;
        }

        if (is_string($value) && $value === '') {
            return null;
        }

        return is_numeric($value) ? (float) $value : null;
    }

    /**
     * Convert nested arrayable values into plain arrays so JSON columns are hydrated correctly.
     */
    private static function normaliseArray(mixed $value): ?array
    {
        if ($value instanceof Arrayable) {
            $value = $value->toArray();
        }

        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            return [$value];
        }

        if (is_array($value)) {
            return $value;
        }

        return null;
    }

    /**
     * Ensure locale codes are lower-case unique strings.
     *
     * @param  array<int, string> $locales
     * @return array<int, string>
     */
    private static function normaliseLocales(array $locales): array
    {
        $normalised = array_map(static fn (string $locale): string => Str::lower(trim($locale)), $locales);
        $normalised = array_values(array_filter($normalised, static fn (string $locale): bool => $locale !== ''));

        if ($normalised === []) {
            $normalised = [Str::lower(config('app.locale', 'en'))];
        }

        return array_values(array_unique($normalised));
    }

    /**
     * Convert arrays, arrayables, or scalar payloads into a consistent array form.
     *
     * @return array<string, mixed>
     */
    private static function toArray(mixed $payload): array
    {
        if ($payload instanceof Arrayable) {
            return $payload->toArray();
        }

        if (is_array($payload)) {
            return $payload;
        }

        if ($payload instanceof Collection) {
            return $payload->all();
        }

        return [];
    }

    private static function stringOrNull(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof Model) {
            return null;
        }

        $value = is_scalar($value) ? (string) $value : null;

        return $value !== '' ? $value : null;
    }
}
