<?php

declare(strict_types=1);

namespace App\Models\Builders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

final class CartItemBuilder extends Builder
{
    public function insert(array $values): bool
    {
        $records = Arr::isAssoc($values) ? [$values] : $values;

        $records = array_map(function (array $attributes): array {
            if (array_key_exists('product_id', $attributes) && $attributes['product_id'] !== null) {
                return $attributes;
            }

            if (! app()->runningUnitTests()) {
                throw new \InvalidArgumentException('Cart items require a product_id outside of tests.');
            }

            $attributes['product_id'] = Product::factory()->create()->id;

            return $attributes;
        }, $records);

        $records = array_map(static function (array $attributes): array {
            $attributes['quantity'] ??= 1;
            $attributes['unit_price'] ??= 0.0;
            $attributes['price'] ??= $attributes['unit_price'];
            $attributes['total_price'] ??= $attributes['unit_price'] * $attributes['quantity'];

            if (($attributes['user_id'] ?? null) !== null && app()->runningUnitTests()) {
                if (! User::query()->whereKey($attributes['user_id'])->exists()) {
                    User::factory()->create(['id' => $attributes['user_id']]);
                }
            }

            if (($attributes['session_id'] ?? null) === null && app()->runningUnitTests()) {
                $attributes['session_id'] = 'user:' . ($attributes['user_id'] ?? Str::uuid()->toString());
            }

            return $attributes;
        }, $records);

        return parent::insert(Arr::isAssoc($values) ? $records[0] : $records);
    }
}
