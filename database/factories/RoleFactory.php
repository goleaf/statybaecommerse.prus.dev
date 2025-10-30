<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Role>
 */
final class RoleFactory extends Factory
{
    /**
     * Assigns the factory's associated model.
     *
     * @var class-string<Role>
     */
    protected $model = Role::class;

    /**
     * Defines sensible defaults so tests can create roles without verbose setup.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Use a deterministic slug-style label to avoid collisions in permission sync tests.
            'name' => sprintf('role_%s', Str::slug($this->faker->unique()->words(2, true))),
            // Filament uses the web guard by default, so mirroring that keeps seeded roles consistent.
            'guard_name' => 'web',
            // Seed an empty matrix so tests can merge the specific permissions they need.
            'permissions_matrix' => [],
        ];
    }
}
