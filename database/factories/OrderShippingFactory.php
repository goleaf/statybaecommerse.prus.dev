<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderShipping;
use Illuminate\Database\Eloquent\Factories\Factory;

final class OrderShippingFactory extends Factory
{
    protected $model = OrderShipping::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'carrier_name' => fake()->randomElement(['DHL', 'FedEx', 'UPS']),
            'service' => fake()->randomElement(['Express', 'Standard']),
            'tracking_number' => strtoupper(fake()->bothify('TRK#########')),
            'tracking_url' => fake()->url(),
            'shipped_at' => null,
            'estimated_delivery' => null,
            'delivered_at' => null,
            'weight' => fake()->randomFloat(3, 0.1, 10),
            'dimensions' => '30x20x10',
            'cost' => fake()->randomFloat(2, 1, 50),
            'metadata' => [],
        ];
    }
}
