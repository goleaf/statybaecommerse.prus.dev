<?php

declare(strict_types=1);

namespace App\Support\Search;

use App\Models\Location;
use App\Support\Filament\SearchableComponentHelper;
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
        $locations = self::baseQuery($term)
            ->limit($limit)
            ->get();

        return $locations
            ->map(static fn (Location $location): SearchResult => self::toResult($location))
            ->all();
    }

    public static function label(Location $location): string
    {
        $code = self::stringValue($location->getAttribute('code'));
        $name = self::stringValue($location->getAttribute('name'));
        $city = self::stringValue($location->getAttribute('city'));
        $country = self::stringValue($location->getAttribute('country_code'));

        $locationPart = trim(implode(', ', array_filter([$city, $country])));

        $segments = array_filter([
            $code !== '' ? sprintf('[%s]', $code) : null,
            $name !== '' ? $name : null,
            $locationPart !== '' ? $locationPart : null,
        ]);

        return trim(implode(' ', $segments));
    }

    public static function hydrateComponent(SearchableInput $component, ?int $state): void
    {
        SearchableComponentHelper::hydrate(
            $component,
            $state,
            static function (int $identifier): ?Location {
                return Location::query()
                    ->select(['id', 'name', 'code', 'city', 'country_code'])
                    ->find($identifier);
            },
            static function (Location $location): array {
                $result = self::toResult($location);
                $payload = SearchResultPayload::hydrate($result)['payload'];

                return [
                    'value'   => $result->value(),
                    'label'   => $result->label(),
                    'payload' => $payload,
                ];
            },
        );
    }

    private static function baseQuery(string $term): Builder
    {
        $search = trim($term);

        return Location::query()
            ->select(['id', 'name', 'code', 'city', 'country_code'])
            ->when($search !== '', static function (Builder $builder) use ($search): void {
                $builder->where(static function (Builder $query) use ($search): void {
                    $like = "%{$search}%";

                    $query
                        ->where('name', 'like', $like)
                        ->orWhere('code', 'like', $like)
                        ->orWhere('city', 'like', $like)
                        ->orWhere('country_code', 'like', $like);
                });
            })
            ->orderBy('name');
    }

    private static function toResult(Location $location): SearchResult
    {
        $identifier = (string) ($location->getKey() ?? '');
        $result = SearchResult::make($identifier, self::label($location));

        return SearchResultPayload::normalise($result, [
            'location_id'  => $location->getKey(),
            'name'         => self::stringValue($location->getAttribute('name')),
            'code'         => self::stringValue($location->getAttribute('code')),
            'city'         => self::stringValue($location->getAttribute('city')),
            'country_code' => self::stringValue($location->getAttribute('country_code')),
        ]);
    }

    private static function stringValue(mixed $value): string
    {
        return is_string($value) ? $value : '';
    }
}
