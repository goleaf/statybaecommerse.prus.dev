<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\Campaign;
use App\Models\CampaignConversion;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CampaignConversion>
 */
final class CampaignConversionFactory extends Factory
{
    protected $model = CampaignConversion::class;

    public function definition(): array
    {
        return [
            'campaign_id' => Campaign::factory(),
            'order_id' => $this->faker->optional(0.7)->randomElement(Order::pluck('id')->toArray()),
            'customer_id' => $this->faker->optional(0.6)->randomElement(Customer::pluck('id')->toArray()),
            'conversion_type' => $this->faker->randomElement(['purchase', 'signup', 'download', 'registration']),
            'conversion_value' => $this->faker->randomFloat(2, 0, 1000),
            'session_id' => $this->faker->uuid(),
            'conversion_data' => [
                'source' => $this->faker->randomElement(['web', 'mobile', 'email', 'social']),
                'device' => $this->faker->randomElement(['desktop', 'mobile', 'tablet']),
                'browser' => $this->faker->randomElement(['Chrome', 'Firefox', 'Safari', 'Edge']),
            ],
            'converted_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }

    public function purchase(): static
    {
        return $this->state(fn(array $attributes) => [
            'conversion_type' => 'purchase',
            'conversion_value' => $this->faker->randomFloat(2, 10, 500),
            'order_id' => Order::factory(),
        ]);
    }

    public function signup(): static
    {
        return $this->state(fn(array $attributes) => [
            'conversion_type' => 'signup',
            'conversion_value' => 0,
            'customer_id' => Customer::factory(),
        ]);
    }

    public function download(): static
    {
        return $this->state(fn(array $attributes) => [
            'conversion_type' => 'download',
            'conversion_value' => 0,
            'conversion_data' => [
                'file_name' => $this->faker->word() . '.pdf',
                'file_size' => $this->faker->numberBetween(1000, 10000000),
            ],
        ]);
    }

    public function highValue(): static
    {
        return $this->state(fn(array $attributes) => [
            'conversion_type' => 'purchase',
            'conversion_value' => $this->faker->randomFloat(2, 500, 2000),
        ]);
    }

    public function recent(): static
    {
        return $this->state(fn(array $attributes) => [
            'converted_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    public function withCustomer(): static
    {
        return $this->state(fn(array $attributes) => [
            'customer_id' => Customer::factory(),
        ]);
    }

    public function withOrder(): static
    {
        return $this->state(fn(array $attributes) => [
            'order_id' => Order::factory(),
            'conversion_type' => 'purchase',
        ]);
    }
}
