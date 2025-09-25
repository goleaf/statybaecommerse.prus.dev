<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Zone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Zone>
 */
final class ZoneFactory extends Factory
{
    protected $model = Zone::class;

    public function definition(): array
    {
        return [
            'name' => fake()->state(),
            'code' => strtoupper(fake()->lexify('??')).fake()->randomNumber(2, true),
            'is_enabled' => true,
        ];
    }
}

