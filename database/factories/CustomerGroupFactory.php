<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\CustomerGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

final class CustomerGroupFactory extends Factory
{
	protected $model = CustomerGroup::class;

	public function definition(): array
	{
		return [
			'name' => fake()->unique()->words(2, true),
			'slug' => str(fake()->unique()->words(2, true))->slug(),
			'description' => fake()->sentence(8),
			'discount_percentage' => fake()->randomFloat(2, 0, 30),
			'is_enabled' => fake()->boolean(80),
			'conditions' => [],
		];
	}
}
