<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Country;
use App\Models\ShippingOption;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ShippingOption>
 */
final class ShippingOptionFactory extends Factory
{
    protected $model = ShippingOption::class;

    public function free(): static
    {
        return $this->state(fn (array $attributes) => [
            'name'         => 'Free Shipping',
            'carrier_name' => 'Lietuvos Paštas',
            'service_type' => 'Standard',
            'price'        => 0.00,
        ]);
    }
}

