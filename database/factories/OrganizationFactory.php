<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Organization>
 */
final class OrganizationFactory extends Factory
{
    protected $model = Organization::class;

    public function definition(): array
    {
        $name = $this->faker->company();
        
        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $this->faker->paragraph(),
            'type' => $this->faker->randomElement(['company', 'team', 'department']),
            'is_active' => true,
            'settings' => [
                'timezone' => $this->faker->timezone(),
                'currency' => 'EUR',
                'features' => $this->faker->randomElements(['projects', 'tasks', 'files', 'reports'], 3),
            ],
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function withMembers(int $count = 3): static
    {
        return $this->afterCreating(function (Organization $organization) use ($count) {
            $users = \App\Models\User::factory($count)->create();
            
            $users->each(function ($user, $index) use ($organization) {
                $role = match ($index) {
                    0 => 'owner',
                    1 => 'admin',
                    default => 'member',
                };
                
                $organization->addUser($user, $role, ['read', 'write']);
            });
        });
    }
}