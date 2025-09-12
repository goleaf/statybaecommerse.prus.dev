<?php

namespace Database\Factories;

use App\Models\Partner;
use App\Models\PartnerTier;
use Illuminate\Database\Eloquent\Factories\Factory;

class PartnerFactory extends Factory
{
    protected $model = Partner::class;

    public function definition(): array
    {
        $name = $this->faker->company();

        return [
            'name' => $name,
            'code' => strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name)).'-'.$this->faker->unique()->numerify('###'),
            'tier_id' => PartnerTier::factory(),
            'contact_email' => $this->faker->unique()->safeEmail(),
            'contact_phone' => $this->faker->phoneNumber(),
            'is_enabled' => true,
            'discount_rate' => $this->faker->randomFloat(4, 0, 0.3),
            'commission_rate' => $this->faker->randomFloat(4, 0, 0.1),
            'metadata' => ['website' => $this->faker->url()],
        ];
    }
}
