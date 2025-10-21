<?php

declare(strict_types=1);

namespace App\Support\Search;

use App\Models\Location;
use DefStudio\SearchableInput\DTO\SearchResult;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

final class LocationSearch
{
    /**
     * @return array<int, SearchResult>
     */
    public static function results(string $term, int $limit = 15): array
    {
        /** @var EloquentCollection<int, Location> $locations */
        $locations = Location::query()
            ->select(['id', 'name', 'code', 'city', 'country_code'])
            ->when(trim($term) !== '', function (Builder $builder) use ($term): void {
                $builder->where(function (Builder $query) use ($term): void {
                    $query
                        ->where('name', 'like', "%{$term}%")
                        ->orWhere('code', 'like', "%{$term}%")
                        ->orWhere('city', 'like', "%{$term}%")
                        ->orWhere('country_code', 'like', "%{$term}%");
                });
            })
            ->orderBy('name')
            ->limit($limit)
            ->get();

        return $locations
            ->map(function (Location $location): SearchResult {
                $identifier = (string) $location->getKey();
                $result = SearchResult::make($identifier, self::label($location));

                return $result
                    ->withData('location_id', $location->getKey())
                    ->withData('code', (string) ($location->getAttribute('code') ?? ''))
                    ->withData('city', (string) ($location->getAttribute('city') ?? ''))
                    ->withData('country_code', (string) ($location->getAttribute('country_code') ?? ''));
            })
            ->all();
    }

    public static function label(Location $location): string
    {
        /** @var string|null $rawName */
        $rawName = $location->getAttribute('name');
        /** @var string|null $rawCode */
        $rawCode = $location->getAttribute('code');
        /** @var string|null $rawCity */
        $rawCity = $location->getAttribute('city');
        /** @var string|null $rawCountry */
        $rawCountry = $location->getAttribute('country_code');

        $code = $rawCode ?: '—';
        $name = $rawName ?? '';
        $city = $rawCity ?? '';
        $country = $rawCountry ?? '';

        $locationPart = trim(sprintf('%s, %s', $city, $country));

        return trim(sprintf('[%s] %s%s', $code, $name, $locationPart !== '' ? " — {$locationPart}" : ''));
    }
}
