<?php

declare(strict_types=1);

namespace App\Services\Shipping;

use App\Models\CartItem;
use App\Models\Country;
use App\Models\ShippingOption;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection as BaseCollection;
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
     * @param  BaseCollection<int, CartItem>|EloquentCollection<int, CartItem>                                             $cartItems
     * @return BaseCollection<int, array{id:int,name:string,price:float,currency:string|null,eta:string|null,formatted_price:string,estimated_delivery:string}>
     */
    public function resolve(BaseCollection|EloquentCollection $cartItems, ?string $countryCode = null): BaseCollection
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

        return $candidates
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
                    'currency'           => $option->currency_code,
                    'eta'                => $option->estimated_delivery_text,
                    'formatted_price'    => app_money_format($price, $option->currency_code),
                    'estimated_delivery' => $option->estimated_delivery_text,
                ];
            })
            ->values();
    }

    /**
     * Resolve shipping options for the active cart session and destination data.
     *
     * @param  Authenticatable|null         $user
     * @param  array<string, mixed>         $destination
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
}
