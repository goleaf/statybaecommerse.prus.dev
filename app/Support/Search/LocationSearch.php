<?php

declare(strict_types=1);

namespace App\Support\Search;

use App\Models\Location;
use DefStudio\SearchableInput\DTO\SearchResult;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

final class LocationSearch
{
    /**
     * @return array<int, SearchResult>
     */
    public static function results(string $term, int $limit = 15): array
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, Location> $locations */
        $locations = self::query($term)
            ->limit($limit)
            ->get();

        return $locations
            ->map(static function (Location $location): SearchResult {
                /** @var int|string|null $identifier */
                $identifier = $location->getKey();

                $label = self::label($location);

                $result = SearchResult::make((string) ($identifier ?? ''), $label);

                $result
                    ->withData('location_id', $location->getKey())
                    ->withData('name', self::stringValue($location->getAttribute('name')))
                    ->withData('code', self::stringValue($location->getAttribute('code')))
                    ->withData('city', self::stringValue($location->getAttribute('city')))
                    ->withData('country_code', self::stringValue($location->getAttribute('country_code')));

                return $result;
            })
            ->all();
    }

    public static function label(Location $location): string
    {
        /** @var string|null $rawName */
        $rawName = $location->getAttribute('name');
        /** @var string|null $rawCity */
        $rawCity = $location->getAttribute('city');
        /** @var string|null $rawCode */
        $rawCode = $location->getAttribute('code');
        /** @var string|null $rawCountry */
        $rawCountry = $location->getAttribute('country_code');

        $name = $rawName ?? '';
        $city = $rawCity ?? '';
        $code = $rawCode ?? '';
        $country = $rawCountry ?? '';

        return trim(implode(' • ', array_filter([
            $name !== '' ? $name : __('inventory.locations.unknown'),
            $city !== '' ? $city : null,
            $code !== '' ? $code : null,
            $country !== '' ? Str::upper($country) : null,
        ])));
    }

    /**
     * @return Builder<Location>
     */
    private static function query(string $term): Builder
    {
        $search = trim($term);

        return Location::query()
            ->select(['id', 'name', 'code', 'city', 'country_code'])
            ->when($search !== '', static function (Builder $builder) use ($search): void {
                $builder->where(static function (Builder $query) use ($search): void {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhere('country_code', 'like', "%{$search}%");
                });
            })
            ->orderBy('name');
    }

    private static function stringValue(mixed $value): string
    {
        return is_string($value) ? $value : '';
    }
}

