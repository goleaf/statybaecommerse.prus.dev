<?php

declare(strict_types=1);

namespace App\Services\Shipping;

use App\Models\CartItem;
use App\Models\Country;
use App\Models\ShippingOption;
use App\Services\VenipakService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

/**
 * ShippingOptionResolver
 *
 * Provides context-aware shipping options based on cart weight, order total,
 * and destination metadata.
 */
final class ShippingOptionResolver
{
    /**
     * Resolve the shipping options that are valid for the provided cart state.
     *
     * @param  BaseCollection<int, CartItem>|EloquentCollection<int, CartItem>                                                                                  $cartItems
     * @return BaseCollection<int, array{id:int,name:string,price:float,currency:string|null,eta:string|null,formatted_price:string,estimated_delivery:string}>
     */
    public function resolve(BaseCollection|EloquentCollection $cartItems, ?string $countryCode = null, array $destination = []): BaseCollection
    {
        // Normalise the incoming collection so downstream lookups are consistent.
        $items = $cartItems instanceof EloquentCollection ? $cartItems : new EloquentCollection($cartItems->all());
        $items->loadMissing('product');

        $weight = $this->calculateCartWeight($items);
        $orderAmount = $this->calculateOrderAmount($items);
        $countryId = $this->resolveCountryId($countryCode);

        // Start from the enabled, ordered list of options and filter by geography when possible.
        $query = ShippingOption::query()->enabled()->ordered();
        if ($countryId !== null) {
            $query->where(static function ($builder) use ($countryId): void {
                $builder->whereNull('country_id')->orWhere('country_id', $countryId);
            });
        }

        /** @var BaseCollection<int, ShippingOption> $candidates */
        $candidates = $query->get();

        $resolvedOptions = $candidates
            ->filter(function (ShippingOption $option) use ($weight, $orderAmount): bool {
                // Discard options that fall outside the configured weight or order amount constraints.
                return $option->isEligibleForWeight($weight) && $option->isEligibleForOrderAmount($orderAmount);
            })
            ->map(function (ShippingOption $option) use ($weight, $orderAmount): array {
                $price = (float) $option->calculatePriceForOrder($weight, $orderAmount);
                $rawId = $option->getAttribute('id');
                if (! is_numeric($rawId)) {
                    $rawId = $option->getKey();
                }
                $normalizedId = is_numeric($rawId) ? (int) $rawId : 0;

                return [
                    'id'                 => $normalizedId,
                    'name'               => $option->name,
                    'description'        => $option->description,
                    'price'              => $price,
                    'currency'           => 'EUR',
                    'eta'                => $option->estimated_delivery_text,
                    'formatted_price'    => app_money_format($price, 'EUR'),
                    'estimated_delivery' => $option->estimated_delivery_text,
                ];
            })
            ->values();

        // Fallback to Venipak pickup points when no local shipping matrix option matches.
        if ($resolvedOptions->isEmpty()) {
            return $this->resolveVenipakPickupOptions($countryCode, $destination);
        }

        return $resolvedOptions;
    }

    /**
     * Resolve shipping options for the active cart session and destination data.
     *
     * @param  array<string, mixed>             $destination
     * @return array<int, array<string, mixed>>
     */
    public function forCart(?Authenticatable $user = null, array $destination = []): array
    {
        // Determine the relevant cart items by prioritising the current session and optional user.
        $cartItems = CartItem::with('product')
            ->where('session_id', Session::getId())
            ->when($user !== null, function ($query) use ($user): void {
                // Include persisted cart rows tied to the authenticated user for completeness.
                $identifier = $user->getAuthIdentifier();

                if ($identifier !== null) {
                    $query->orWhere('user_id', $identifier);
                }
            })
            ->get()
            ->unique(static fn (CartItem $item): string => (string) $item->getKey());

        if ($cartItems->isEmpty()) {
            // Provide a graceful fallback when no cart data exists yet.
            return [];
        }

        // Delegate to the core resolver, passing along the best-known country code.
        $resolved = $this->resolve($cartItems, $this->extractCountryCode($destination));

        return $resolved->values()->all();
    }

    /**
     * Attempt to read a normalised country code from the provided destination payload.
     */
    private function extractCountryCode(array $destination): ?string
    {
        foreach (['country', 'country_code', 'countryCode'] as $key) {
            $value = $destination[$key] ?? null;
            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return null;
    }

    /**
     * Determine the aggregate weight of all cart items.
     *
     * @param EloquentCollection<int, CartItem> $items
     */
    private function calculateCartWeight(EloquentCollection $items): float
    {
        // Combine product-level weights with quantity multipliers to approximate shipping mass.
        return (float) $items->sum(static function (CartItem $item): float {
            $snapshotWeight = data_get($item->product_snapshot, 'weight');
            $productWeight = $item->product !== null ? (float) $item->product->weight : (is_numeric($snapshotWeight) ? (float) $snapshotWeight : 0.0);

            return $productWeight * (int) $item->quantity;
        });
    }

    /**
     * Determine the current order amount for evaluating thresholds.
     *
     * @param EloquentCollection<int, CartItem> $items
     */
    private function calculateOrderAmount(EloquentCollection $items): float
    {
        // Use the persisted price attribute in combination with quantities to match checkout totals.
        return (float) $items->sum(static fn (CartItem $item): float => (float) $item->price * (int) $item->quantity);
    }

    /**
     * Attempt to resolve the numeric country identifier from the supplied code.
     */
    private function resolveCountryId(?string $countryCode): ?int
    {
        if (! $countryCode) {
            return null;
        }

        $code = strtoupper($countryCode);

        $identifier = Country::query()
            ->where(static fn ($query) => $query->where('cca2', $code)->orWhere('code', $code))
            ->value('id');

        if ($identifier === null) {
            return null;
        }

        return is_numeric($identifier) ? (int) $identifier : null;
    }

    /**
     * Resolve virtual shipping options from Venipak pickup point API.
     *
     * @return BaseCollection<int, array{id:int,name:string,description:string|null,price:float,currency:string|null,eta:string|null,formatted_price:string,estimated_delivery:string}>
     */
    private function resolveVenipakPickupOptions(?string $countryCode, array $destination = []): BaseCollection
    {
        $country = strtoupper((string) ($countryCode ?: 'LT'));
        $destinationCity = is_string($destination['city'] ?? null) ? trim((string) $destination['city']) : null;
        $destinationAddress = is_string($destination['address'] ?? null) ? trim((string) $destination['address']) : null;
        $destinationPostalCode = is_string($destination['postal_code'] ?? null) ? trim((string) $destination['postal_code']) : null;
        $cacheKey = 'shipping.venipak.pickup_points.' . $country;

        /** @var array<int, mixed> $points */
        $points = Cache::remember($cacheKey, now()->addMinutes(20), static function () use ($country): array {
            $payload = app(VenipakService::class)->getPickupPoints($country);

            if (isset($payload['pickup_points']) && is_array($payload['pickup_points'])) {
                return array_values($payload['pickup_points']);
            }

            if (isset($payload['data']) && is_array($payload['data'])) {
                return array_values($payload['data']);
            }

            return is_array($payload) ? array_values($payload) : [];
        });

        return collect($points)
            ->filter(static fn ($point): bool => is_array($point))
            ->map(function (array $point, int $index) use ($destinationCity, $destinationAddress, $destinationPostalCode): array {
                $pointId = (string) ($point['id'] ?? $point['point_id'] ?? $point['code'] ?? ('venipak-' . $index));
                $displayName = (string) ($point['name'] ?? $point['office_name'] ?? $point['point'] ?? __('messages.shipping'));
                $city = (string) ($point['city'] ?? $point['municipality'] ?? '');
                $address = (string) ($point['address'] ?? $point['street'] ?? '');
                $postalCode = (string) ($point['zip'] ?? $point['postal_code'] ?? $point['postcode'] ?? '');
                $description = trim($city . ($city !== '' && $address !== '' ? ', ' : '') . $address);
                $estimated = trim(__('messages.shipping') . ': Venipak');

                // Use stable synthetic IDs so checkout selection remains valid between refreshes.
                $syntheticId = abs(crc32('venipak-' . $pointId));

                return [
                    'id'                 => $syntheticId,
                    'name'               => 'Venipak - ' . $displayName,
                    'description'        => $description !== '' ? $description : null,
                    'price'              => 0.0,
                    'currency'           => 'EUR',
                    'eta'                => $estimated,
                    'formatted_price'    => app_money_format(0, 'EUR'),
                    'estimated_delivery' => $description !== '' ? $description : $estimated,
                    '_score'             => $this->calculatePickupPointScore(
                        pointCity: $city,
                        pointAddress: $address,
                        pointPostalCode: $postalCode,
                        destinationCity: $destinationCity,
                        destinationAddress: $destinationAddress,
                        destinationPostalCode: $destinationPostalCode,
                    ),
                    '_zip_distance' => $this->calculatePostalDistance(
                        pointPostalCode: $postalCode,
                        destinationPostalCode: $destinationPostalCode,
                    ),
                ];
            })
            ->sort(function (array $left, array $right): int {
                $leftDistance = (int) ($left['_zip_distance'] ?? PHP_INT_MAX);
                $rightDistance = (int) ($right['_zip_distance'] ?? PHP_INT_MAX);

                if ($leftDistance !== $rightDistance) {
                    return $leftDistance <=> $rightDistance;
                }

                $leftScore = (int) ($left['_score'] ?? 0);
                $rightScore = (int) ($right['_score'] ?? 0);

                if ($leftScore !== $rightScore) {
                    return $rightScore <=> $leftScore;
                }

                return strcmp((string) ($left['name'] ?? ''), (string) ($right['name'] ?? ''));
            })
            ->take(30)
            ->map(static function (array $option): array {
                unset($option['_score']);
                unset($option['_zip_distance']);

                return $option;
            })
            ->values();
    }

    private function calculatePickupPointScore(
        string $pointCity,
        string $pointAddress,
        string $pointPostalCode,
        ?string $destinationCity,
        ?string $destinationAddress,
        ?string $destinationPostalCode
    ): int {
        $score = 0;

        $normalizedPointCity = mb_strtolower(trim($pointCity));
        $normalizedDestinationCity = mb_strtolower(trim((string) $destinationCity));
        if ($normalizedDestinationCity !== '' && $normalizedPointCity !== '') {
            if ($normalizedPointCity === $normalizedDestinationCity) {
                $score += 120;
            } elseif (str_contains($normalizedPointCity, $normalizedDestinationCity) || str_contains($normalizedDestinationCity, $normalizedPointCity)) {
                $score += 70;
            }
        }

        $normalizedPointPostalCode = preg_replace('/\s+/', '', mb_strtolower(trim($pointPostalCode))) ?? '';
        $normalizedDestinationPostalCode = preg_replace('/\s+/', '', mb_strtolower(trim((string) $destinationPostalCode))) ?? '';
        if ($normalizedDestinationPostalCode !== '' && $normalizedPointPostalCode !== '') {
            if ($normalizedPointPostalCode === $normalizedDestinationPostalCode) {
                $score += 80;
            } elseif (str_starts_with($normalizedPointPostalCode, $normalizedDestinationPostalCode) || str_starts_with($normalizedDestinationPostalCode, $normalizedPointPostalCode)) {
                $score += 50;
            }
        }

        $normalizedPointAddress = mb_strtolower(trim($pointAddress));
        $normalizedDestinationAddress = mb_strtolower(trim((string) $destinationAddress));
        if ($normalizedDestinationAddress !== '' && $normalizedPointAddress !== '') {
            $destinationParts = collect(preg_split('/\s+/', $normalizedDestinationAddress) ?: [])
                ->filter(static fn (string $part): bool => mb_strlen($part) >= 3)
                ->values();

            foreach ($destinationParts as $part) {
                if (str_contains($normalizedPointAddress, $part)) {
                    $score += 6;
                }
            }
        }

        return $score;
    }

    private function calculatePostalDistance(string $pointPostalCode, ?string $destinationPostalCode): int
    {
        $normalizedPointPostalCode = preg_replace('/\s+/', '', mb_strtolower(trim($pointPostalCode))) ?? '';
        $normalizedDestinationPostalCode = preg_replace('/\s+/', '', mb_strtolower(trim((string) $destinationPostalCode))) ?? '';

        if ($normalizedDestinationPostalCode === '' || $normalizedPointPostalCode === '') {
            return PHP_INT_MAX;
        }

        if (ctype_digit($normalizedPointPostalCode) && ctype_digit($normalizedDestinationPostalCode)) {
            return abs((int) $normalizedPointPostalCode - (int) $normalizedDestinationPostalCode);
        }

        if ($normalizedPointPostalCode === $normalizedDestinationPostalCode) {
            return 0;
        }

        $prefixLength = 0;
        $limit = min(strlen($normalizedPointPostalCode), strlen($normalizedDestinationPostalCode));
        for ($index = 0; $index < $limit; $index++) {
            if ($normalizedPointPostalCode[$index] !== $normalizedDestinationPostalCode[$index]) {
                break;
            }
            $prefixLength++;
        }

        return max(1, 100 - $prefixLength);
    }
}
