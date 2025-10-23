<?php

declare(strict_types=1);

namespace App\Support\Search;

use App\Models\Address;
use App\Models\City;
use DefStudio\SearchableInput\DTO\SearchResult;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

final class AddressSearch
{
    /**
     * @return array<int, string>
     */
    public static function labels(string $term, int $limit = 15): array
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, Address> $addresses */
        $addresses = self::addressQuery($term)
            ->limit($limit)
            ->get();

        return $addresses
            ->map(fn (Address $address): string => self::formatAddress($address))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    public static function cities(string $term, int $limit = 15): array
    {
        return self::cityQuery($term)
            ->limit($limit)
            ->pluck('name')
            // Keep only non-empty string values because upstream databases could surface
            // nulls when a translation is missing or the record is incomplete.
            ->filter(fn (mixed $name): bool => is_string($name) && $name !== '')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return array<int, SearchResult>
     */
    public static function cityResults(string $term, int $limit = 15): array
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, City> $cities */
        $cities = self::cityQuery($term)
            ->limit($limit)
            ->get();

        return $cities
            ->map(function (City $city): SearchResult {
                /** @var string|null $rawName */
                $rawName = $city->getAttribute('name');
                /** @var string|null $rawCode */
                $rawCode = $city->getAttribute('code');
                /** @var string|null $rawCountry */
                $rawCountry = $city->getAttribute('country_code');

                $label = trim($rawName ?? '');
                $code = $rawCode ?? '';
                $country = $rawCountry ?? '';

                /** @var int|string|null $identifier */
                $identifier = $city->getKey();
                $result = SearchResult::make((string) ($identifier ?? ''), $label);

                // Provide the city metadata inside the normalised payload for downstream consumers.
                return SearchResultPayload::normalise($result, [
                    'city_id'      => $city->getKey(),
                    'code'         => $code,
                    'country_code' => $country,
                ]);
            })
            ->all();
    }

    /**
     * @return array<int, SearchResult>
     */
    public static function addressResults(string $term, int $limit = 15): array
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, Address> $addresses */
        $addresses = self::addressQuery($term)
            ->limit($limit)
            ->get();

        return $addresses
            ->map(function (Address $address): SearchResult {
                $label = self::formatAddress($address);
                /** @var int|string|null $identifier */
                $identifier = $address->getKey();
                $result = SearchResult::make((string) ($identifier ?? ''), $label);

                $result
                    ->withData('address', self::addressPayload($address))
                    ->withData('address_id', $address->getKey());

                return $result;
            })
            ->all();
    }

    /**
     * @return Builder<Address>
     */
    private static function addressQuery(string $term): Builder
    {
        $search = trim($term);

        // Ignore user ownership scopes so administrative lookups can return the full set
        // of address suggestions irrespective of the currently authenticated context.
        return Address::query()->withoutGlobalScopes()
            ->select(['id', 'address_line_1', 'city', 'state', 'postal_code', 'country_code'])
            ->when($search !== '', function (Builder $builder) use ($search): void {
                $builder->where(function (Builder $query) use ($search): void {
                    $query
                        ->where('address_line_1', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhere('postal_code', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('updated_at');
    }

    /**
     * @return Builder<City>
     */
    private static function cityQuery(string $term): Builder
    {
        $search = trim($term);

        return City::query()
            ->select(['id', 'name', 'code', 'country_code'])
            ->when($search !== '', function (Builder $builder) use ($search): void {
                $builder->where(function (Builder $query) use ($search): void {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->orderBy('name');
    }

    private static function formatAddress(Address $address): string
    {
        /** @var string|null $line1 */
        $line1 = $address->getAttribute('address_line_1');
        /** @var string|null $city */
        $city = $address->getAttribute('city');
        /** @var string|null $state */
        $state = $address->getAttribute('state');
        /** @var string|null $postal */
        $postal = $address->getAttribute('postal_code');
        /** @var string|null $country */
        $country = $address->getAttribute('country_code');

        $parts = array_filter([
            $line1 ?? '',
            $city ?? '',
            $state ?? '',
            $postal ?? '',
            Str::upper($country ?? ''),
        ], fn (string $value): bool => $value !== '');

        return implode(', ', $parts);
    }

    /**
     * @return array<string, string>
     */
    private static function addressPayload(Address $address): array
    {
        $payload = [
            'name' => trim(implode(' ', array_filter([
                $address->getAttribute('first_name'),
                $address->getAttribute('last_name'),
            ]))),
            'company'        => (string) ($address->getAttribute('company') ?? ''),
            'address_line_1' => (string) ($address->getAttribute('address_line_1') ?? ''),
            'address_line_2' => (string) ($address->getAttribute('address_line_2') ?? ''),
            'city'           => (string) ($address->getAttribute('city') ?? ''),
            'state'          => (string) ($address->getAttribute('state') ?? ''),
            'postal_code'    => (string) ($address->getAttribute('postal_code') ?? ''),
            'country_code'   => (string) ($address->getAttribute('country_code') ?? ''),
            'phone'          => (string) ($address->getAttribute('phone') ?? ''),
            'email'          => (string) ($address->getAttribute('email') ?? ''),
        ];

        return collect($payload)
            ->filter(fn (string $value): bool => trim($value) !== '')
            ->all();
    }
}
