<?php

declare(strict_types=1);

namespace App\Services\Pricing;

use App\Data\Pricing\PriceBreakdown;
use App\Models\City;
use App\Models\Country;
use App\Services\Pricing\Exceptions\RateTamperingException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use JsonException;

final class DeterministicTotalsService
{
    public function __construct(
        private readonly PriceCalculator $calculator,
        private readonly PriceConfiguration $configuration,
    ) {}

    /**
     * @param  array{destination: array{country:string, region:string, postal_code:string}, service:string, items: array<int, arra
y{quantity:int, unit_price:float|int|string}>, discounts?: array{amount?: float|int|string}, client_rates?: array<string, float|i
nt|string>}  $payload
     * @return array{breakdown: PriceBreakdown, tax_component: array<string, mixed>, shipping_component: array<string, mixed>, ro
unding: array<string, mixed>}
     *
     * @throws ValidationException
     * @throws RateTamperingException
     */
    public function quote(array $payload): array
    {
        $destination = $payload['destination'];

        $country = $this->resolveCountry($destination['country']);
        $region = $this->resolveRegion($country->getKey(), $destination['region']);
        $this->assertPostalCodeExists($country->getKey(), $region['id'] ?? null, $destination['postal_code']);

        $taxMeta = $this->resolveTaxRate($country, $region);
        $serviceMeta = $this->resolveService($payload['service']);

        $subtotal = $this->calculateSubtotal($payload['items']);
        $discount = $this->normaliseDiscount($payload['discounts']['amount'] ?? 0.0, $subtotal);

        $breakdown = $this->calculator->breakdown(
            $subtotal,
            $discount,
            $serviceMeta['amount'],
            $taxMeta['rate'],
        );

        $this->guardAgainstTampering(
            $payload['client_rates'] ?? [],
            $breakdown,
            $taxMeta['rate'],
            $serviceMeta['amount'],
        );

        return [
            'breakdown' => $breakdown,
            'tax_component' => [
                'basis' => $breakdown->taxableAmount,
                'rate' => $taxMeta['rate'],
                'amount' => $breakdown->tax,
                'origin' => $taxMeta['origin'],
                'source' => $taxMeta['source'],
            ],
            'shipping_component' => [
                'service' => $serviceMeta['code'],
                'label' => $serviceMeta['label'],
                'amount' => $breakdown->shipping,
            ],
            'rounding' => [
                'precision' => $this->configuration->precision(),
                'mode' => $this->describeRoundingMode($this->configuration->roundingMode()),
            ],
        ];
    }

    private function resolveCountry(string $countryCode): Country
    {
        $country = Country::query()
            ->whereRaw('upper(cca2) = ?', [$countryCode])
            ->first();

        if ($country === null) {
            throw ValidationException::withMessages([
                'destination.country' => __('The selected country is not supported.'),
            ]);
        }

        return $country;
    }

    /**
     * @return array{id:int, code:string, metadata: array<string, mixed>}|
     *         array{id:int, code:string, metadata: array<string, mixed>, source:string}|
     *         array{id:int, code:string, metadata: array<string, mixed>}|
     *         array
     */
    private function resolveRegion(int $countryId, string $regionCode): array
    {
        $record = DB::table('regions')
            ->select(['id', 'code', 'metadata'])
            ->whereRaw('upper(code) = ?', [$regionCode])
            ->where('country_id', $countryId)
            ->first();

        if ($record === null) {
            throw ValidationException::withMessages([
                'destination.region' => __('The selected region does not belong to the specified country.'),
            ]);
        }

        $metadata = [];
        if (isset($record->metadata)) {
            if (is_array($record->metadata)) {
                $metadata = $record->metadata;
            } else {
                try {
                    $metadata = json_decode((string) $record->metadata, true, 512, JSON_THROW_ON_ERROR | JSON_BIGINT_AS_STRING);
                } catch (JsonException) {
                    // Corrupt JSON should not block the API; fall back to an empty payload instead.
                    $metadata = [];
                }
            }
        }

        return [
            'id' => (int) $record->id,
            'code' => (string) $record->code,
            'metadata' => is_array($metadata) ? $metadata : [],
        ];
    }

    private function assertPostalCodeExists(int $countryId, ?int $regionId, string $postalCode): void
    {
        // Fetch a narrow slice of candidate cities so we can validate the postal code without loading entire tables.
        $cities = City::query()
            ->where('country_id', $countryId)
            ->when($regionId !== null, static fn ($query) => $query->where('region_id', $regionId))
            ->get(['postal_codes', 'postal_code']);

        foreach ($cities as $city) {
            $codes = [];
            if (is_array($city->postal_codes)) {
                $codes = array_map(static fn ($code) => (string) $code, $city->postal_codes);
            }
            if (isset($city->postal_code) && $city->postal_code !== null) {
                $codes[] = (string) $city->postal_code;
            }

            if (in_array($postalCode, $codes, true)) {
                return;
            }
        }

        throw ValidationException::withMessages([
            'destination.postal_code' => __('We could not verify the supplied postal code for the chosen region.'),
        ]);
    }

    /**
     * @param  array{id:int, code:string, metadata: array<string, mixed>}  $region
     * @return array{rate: float, origin: string, source: string}
     */
    private function resolveTaxRate(Country $country, array $region): array
    {
        $cacheKey = sprintf('deterministic_totals:tax:%d:%s', $country->getKey(), $region['code']);
        $ttl = (int) config('deterministic_totals.cache_ttl', 3600);
        $store = config('deterministic_totals.cache_store');

        // Resolve the cache repository while gracefully handling misconfigured stores.
        $preferredStore = is_string($store) && $store !== '' ? $store : null;
        $repositoryName = null;

        if ($preferredStore !== null && is_array(config("cache.stores.{$preferredStore}"))) {
            // Respect the configured store when it exists so production can leverage shared caches.
            $repositoryName = $preferredStore;
        }

        if ($repositoryName === null) {
            // During automated tests we default to the array cache to avoid SQLite driver requirements.
            $repositoryName = app()->runningUnitTests() ? 'array' : (string) config('cache.default', 'array');

            if (! is_array(config("cache.stores.{$repositoryName}"))) {
                // As a final guard fall back to Laravel's in-memory cache to prevent runtime SQL errors.
                $repositoryName = 'array';
            }
        }

        $repository = Cache::store($repositoryName);

        return $repository->remember($cacheKey, $ttl, function () use ($country, $region): array {
            $metadata = $region['metadata'];
            if (isset($metadata['tax_rate'])) {
                return [
                    'rate' => $this->normaliseRate((float) $metadata['tax_rate']),
                    'origin' => 'region',
                    'source' => $region['code'],
                ];
            }

            $countryRate = $country->getAttribute('vat_rate');
            if ($countryRate !== null) {
                return [
                    'rate' => $this->normaliseRate((float) $countryRate),
                    'origin' => 'country',
                    'source' => (string) $country->cca2,
                ];
            }

            return [
                'rate' => $this->configuration->vatRate(),
                'origin' => 'configuration',
                'source' => 'pricing.vat.rate',
            ];
        });
    }

    /**
     * @return array{code: string, label: string, amount: float}
     */
    private function resolveService(string $serviceCode): array
    {
        $services = config('deterministic_totals.services', []);
        if (! array_key_exists($serviceCode, $services)) {
            throw ValidationException::withMessages([
                'service' => __('The requested service is not available.'),
            ]);
        }

        $service = $services[$serviceCode];

        return [
            'code' => $serviceCode,
            'label' => (string) Arr::get($service, 'label', Str::title($serviceCode)),
            'amount' => $this->configuration->round((float) Arr::get($service, 'amount', 0.0)),
        ];
    }

    /**
     * @param  array<int, array{quantity:int, unit_price:float|int|string}>  $items
     */
    private function calculateSubtotal(array $items): float
    {
        $subtotal = 0.0;

        foreach ($items as $item) {
            $quantity = max(0, (int) Arr::get($item, 'quantity', 0));
            $unitPrice = (float) Arr::get($item, 'unit_price', 0.0);
            $subtotal += $quantity * $unitPrice;
        }

        return $this->configuration->round(max(0.0, $subtotal));
    }

    private function normaliseDiscount(float|int|string $discount, float $subtotal): float
    {
        $discountValue = max(0.0, (float) $discount);

        return $this->configuration->round(min($discountValue, $subtotal));
    }

    private function guardAgainstTampering(
        array $clientRates,
        PriceBreakdown $breakdown,
        float $expectedRate,
        float $expectedShipping,
    ): void {
        // If the client did not echo any calculated rates we can skip the tampering guard entirely.
        if ($clientRates === []) {
            return;
        }

        // Compute a tolerance buffer that respects our rounding precision so integrators do not hit false positives.
        $tolerance = 1 / (10 ** max(2, $this->configuration->precision() + 1));

        if (array_key_exists('shipping_amount', $clientRates)) {
            $provided = $this->configuration->round((float) $clientRates['shipping_amount']);
            if ($this->isMismatch($provided, $breakdown->shipping, $tolerance)) {
                throw RateTamperingException::forField('client_rates.shipping_amount');
            }
        }

        if (array_key_exists('tax_rate', $clientRates)) {
            $provided = $this->normaliseRate((float) $clientRates['tax_rate']);
            if ($this->isMismatch($provided, $expectedRate, $tolerance)) {
                throw RateTamperingException::forField('client_rates.tax_rate');
            }
        }

        if (array_key_exists('tax_amount', $clientRates)) {
            $provided = $this->configuration->round((float) $clientRates['tax_amount']);
            if ($this->isMismatch($provided, $breakdown->tax, $tolerance)) {
                throw RateTamperingException::forField('client_rates.tax_amount');
            }
        }
    }

    private function isMismatch(float $provided, float $expected, float $tolerance): bool
    {
        return abs($provided - $expected) > $tolerance;
    }

    private function normaliseRate(float $rate): float
    {
        if ($rate > 1.0) {
            $rate /= 100;
        }

        return max(0.0, $rate);
    }

    private function describeRoundingMode(int $mode): string
    {
        return match ($mode) {
            PHP_ROUND_HALF_DOWN => 'half_down',
            PHP_ROUND_HALF_EVEN => 'half_even',
            PHP_ROUND_HALF_ODD => 'half_odd',
            default => 'half_up',
        };
    }
}
