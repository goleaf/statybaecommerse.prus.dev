<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

/**
 * @extends Factory<\Spatie\Permission\Models\Permission>
 */
final class PermissionFactory extends Factory
{
    protected $model = Permission::class;

    public function definition(): array
    {
        $name = Str::slug($this->faker->unique()->sentence(2), '_');

        return [
            'name' => $name,
            'guard_name' => 'web',
        ];
    }
}
