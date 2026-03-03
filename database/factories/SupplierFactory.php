<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Supplier>
 */
class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

    public function definition(): array
    {
        $name = $this->faker->company();
        $base = Str::upper(Str::slug($name, '-'));

        if ($base === '') {
            $base = 'SUPPLIER';
        }

        return [
            'name'          => $name,
            'code'          => $base . '-' . $this->faker->unique()->numerify('###'),
            'contact_email' => $this->faker->unique()->safeEmail(),
            'contact_phone' => $this->faker->phoneNumber(),
            'notes'         => $this->faker->optional()->sentence(),
            'is_enabled'    => $this->faker->boolean(90),
        ];
    }
}
