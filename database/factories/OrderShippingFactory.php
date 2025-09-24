<?php declare(strict_types=1);

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
            'shipping_method' => fake()->randomElement(['standard', 'express', 'overnight', 'pickup', 'international']),
            'carrier' => fake()->randomElement(['DHL', 'FedEx', 'UPS']),
            'service' => fake()->randomElement(['Express', 'Standard']),
            'service_type' => fake()->randomElement(['Express', 'Standard']),
            'tracking_number' => strtoupper(fake()->bothify('TRK#########')),
            'tracking_url' => fake()->url(),
            'shipped_at' => null,
            'estimated_delivery' => null,
            'delivered_at' => null,
            'weight' => fake()->randomFloat(3, 0.1, 10),
            'dimensions' => '30x20x10',
            'base_cost' => fake()->randomFloat(2, 1, 50),
            'insurance_cost' => fake()->randomFloat(2, 0, 10),
            'total_cost' => fn(array $attributes) => ($attributes['base_cost'] ?? 0) + ($attributes['insurance_cost'] ?? 0),
            'metadata' => [],
            'status' => fake()->randomElement(['pending', 'processing', 'shipped', 'delivered']),
            'is_delivered' => false,
        ];
    }
}
