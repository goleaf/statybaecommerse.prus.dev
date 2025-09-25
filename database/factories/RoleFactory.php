<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * @extends Factory<\Spatie\Permission\Models\Role>
 */
final class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        $name = Str::slug($this->faker->unique()->jobTitle(), '_');

        return [
            'name' => $name,
            'guard_name' => 'web',
        ];
    }
}
