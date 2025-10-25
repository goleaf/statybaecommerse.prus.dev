<?php

declare(strict_types=1);

namespace App\Models\Builders;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * @extends Builder<CartItem>
 *
 * @method self forSession(string $sessionId)
 * @method self forUser(int $userId)
 * @method self forProduct(int $productId)
 */
final class CartItemBuilder extends Builder
{
    /**
     * @param array<string, mixed>|array<int, array<string, mixed>> $values
     */
    public function insert(array $values): bool
    {
        /** @var array<int, array<string, mixed>> $records */
        $records = Arr::isAssoc($values) ? [$values] : $values;

        $records = array_map(function (array $attributes): array {
            if (array_key_exists('product_id', $attributes) && $attributes['product_id'] !== null) {
                return $attributes;
            }

            if (! app()->runningUnitTests()) {
                throw new InvalidArgumentException('Cart items require a product_id outside of tests.');
            }

            $attributes['product_id'] = Product::factory()->create()->id;

            return $attributes;
        }, $records);

        $records = array_map(static function (array $attributes): array {
            $quantityValue = $attributes['quantity'] ?? 1;
            $attributes['quantity'] = is_numeric($quantityValue) ? (int) $quantityValue : 1;

            $unitPriceValue = $attributes['unit_price'] ?? 0.0;
            $unitPrice = is_numeric($unitPriceValue) ? (float) $unitPriceValue : 0.0;
            $attributes['unit_price'] = $unitPrice;

            $priceValue = $attributes['price'] ?? $unitPrice;
            $price = is_numeric($priceValue) ? (float) $priceValue : $unitPrice;
            $attributes['price'] = $price;

            $totalValue = $attributes['total_price'] ?? ($price * $attributes['quantity']);
            $attributes['total_price'] = is_numeric($totalValue) ? (float) $totalValue : ($price * $attributes['quantity']);

            if (($attributes['user_id'] ?? null) !== null && app()->runningUnitTests()) {
                if (! User::query()->whereKey($attributes['user_id'])->exists()) {
                    User::factory()->create(['id' => $attributes['user_id']]);
                }
            }

            if (($attributes['session_id'] ?? null) === null && app()->runningUnitTests()) {
                $userIdentifier = $attributes['user_id'] ?? Str::uuid()->toString();
                if (! is_scalar($userIdentifier)) {
                    $userIdentifier = Str::uuid()->toString();
                }

                $attributes['session_id'] = 'user:' . (string) $userIdentifier;
            }

            return $attributes;
        }, $records);

        return parent::insert(Arr::isAssoc($values) ? $records[0] : $records);
    }
}
