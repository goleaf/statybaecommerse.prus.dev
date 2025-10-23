<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Channel;
use App\Models\Product;
use App\Models\ShippingOption;
use App\Models\User;
use Tests\TestCase;

final class MatrixFieldCastTest extends TestCase
{
    public function test_user_permissions_matrix_casts_to_array(): void
    {
        $matrix = [
            'products' => ['viewAny', 'view', 'update'],
            'orders'   => ['viewAny', 'update'],
        ];

        $user = new User;
        $user->forceFill([
            'permissions_matrix' => $matrix,
        ]);
        $user->syncOriginal();

        $this->assertSame($matrix, $user->permissions_matrix);
    }

    public function test_shipping_matrix_casts_to_array(): void
    {
        $matrix = [
            'lt' => ['courier', 'pickup'],
            'eu' => ['courier'],
        ];

        $option = new ShippingOption;
        $option->forceFill([
            'shipping_matrix' => $matrix,
        ]);
        $option->syncOriginal();

        $this->assertSame($matrix, $option->shipping_matrix);
    }

    public function test_channel_payment_matrix_casts_to_array(): void
    {
        $matrix = [
            'lt' => ['web', 'pos'],
            'eu' => ['web'],
        ];

        $channel = new Channel;
        $channel->forceFill([
            'payment_matrix' => $matrix,
        ]);
        $channel->syncOriginal();

        $this->assertSame($matrix, $channel->payment_matrix);
    }

    public function test_product_variant_attribute_matrix_casts_to_array(): void
    {
        $matrix = [
            'size'  => ['primary', 'bundle'],
            'color' => ['primary'],
        ];

        $product = new Product;
        $product->forceFill([
            'variant_attribute_matrix' => $matrix,
        ]);
        $product->syncOriginal();

        $this->assertSame($matrix, $product->variant_attribute_matrix);
    }
}
