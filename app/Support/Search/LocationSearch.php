<?php

declare(strict_types=1);

namespace App\Support\Search;

use App\Models\Location;
use DefStudio\SearchableInput\DTO\SearchResult;
use DefStudio\SearchableInput\Forms\Components\SearchableInput;
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
            ->map(static fn (Location $location): SearchResult => self::toResult($location))
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

    public static function hydrateComponent(SearchableInput $component, ?int $state): void
    {
        if ($state === null) {
            SearchableComponentHelper::forget($component);

            return;
        }

        $location = Location::query()
            ->select(['id', 'name', 'code', 'city', 'country_code'])
            ->find($state);

        if (! $location instanceof Location) {
            return;
        }

        SearchableComponentHelper::apply($component, self::toResult($location));
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

    private static function toResult(Location $location): SearchResult
    {
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
    }
}
