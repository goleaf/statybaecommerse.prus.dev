<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\Discount;
use App\Models\DiscountCode;
use Illuminate\Database\Eloquent\Factories\Factory;

final class DiscountCodeFactory extends Factory
{
    protected $model = DiscountCode::class;

    public function definition(): array
    {
        return [
            'discount_id' => Discount::factory(),
            'code' => strtoupper($this->faker->bothify('CODE####')),
            'expires_at' => now()->addDays(30),
            'max_uses' => $this->faker->randomElement([null, 100, 500]),
            'usage_count' => 0,
            'metadata' => [],
        ];
    }
}


