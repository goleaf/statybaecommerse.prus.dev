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
     * @return array<int, SearchResult>
     */
    public static function results(string $term, int $limit = 15): array
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, Address> $addresses */
        $addresses = self::addressQuery($term)
            ->limit($limit)
            ->get();

        return $addresses
            ->map(function (Address $address): SearchResult {
                /** @var int|string|null $identifier */
                $identifier = $address->getKey();
                $payload = self::payload($address);

                $result = SearchResult::make((string) ($identifier ?? ''), $payload['label']);

                return $result
                    ->withData('address_id', $address->getKey())
                    ->withData('payload', $payload);
            })
            ->all();
    }

    public static function payload(Address $address): array
    {
        /** @var string|null $line1 */
        $line1 = $address->getAttribute('address_line_1');
        /** @var string|null $line2 */
        $line2 = $address->getAttribute('address_line_2');
        /** @var string|null $city */
        $city = $address->getAttribute('city');
        /** @var string|null $state */
        $state = $address->getAttribute('state');
        /** @var string|null $postal */
        $postal = $address->getAttribute('postal_code');
        /** @var string|null $country */
        $country = $address->getAttribute('country_code');
        /** @var string|null $firstName */
        $firstName = $address->getAttribute('first_name');
        /** @var string|null $lastName */
        $lastName = $address->getAttribute('last_name');
        /** @var string|null $company */
        $company = $address->getAttribute('company_name');
        /** @var string|null $phone */
        $phone = $address->getAttribute('phone');

        $label = self::formatAddress($address);

        return [
            'address_id'     => $address->getKey(),
            'label'          => $label,
            'formatted'      => $label,
            'first_name'     => $firstName ?? '',
            'last_name'      => $lastName ?? '',
            'company_name'   => $company ?? '',
            'address_line_1' => $line1 ?? '',
            'address_line_2' => $line2 ?? '',
            'city'           => $city ?? '',
            'state'          => $state ?? '',
            'postal_code'    => $postal ?? '',
            'country_code'   => $country ?? '',
            'phone'          => $phone ?? '',
            'city_id'        => $address->getAttribute('city_id'),
        ];
    }

    public static function payloadFromId(int $addressId): ?array
    {
        $address = Address::query()
            ->select([
                'id',
                'first_name',
                'last_name',
                'company_name',
                'address_line_1',
                'address_line_2',
                'city',
                'state',
                'postal_code',
                'country_code',
                'phone',
                'city_id',
            ])
            ->find($addressId);

        if (! $address instanceof Address) {
            return null;
        }

        return self::payload($address);
    }

    public static function formatPayload(array $payload): string
    {
        $parts = array_filter([
            (string) ($payload['address_line_1'] ?? ''),
            (string) ($payload['city'] ?? ''),
            (string) ($payload['state'] ?? ''),
            (string) ($payload['postal_code'] ?? ''),
            Str::upper((string) ($payload['country_code'] ?? '')),
        ], fn (string $value): bool => $value !== '');

        if ($parts === []) {
            return (string) ($payload['label'] ?? '');
        }

        return implode(', ', $parts);
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

    private static function stringValue(mixed $value): string
    {
        return is_string($value) ? $value : '';
    }
}
