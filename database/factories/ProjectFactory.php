<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Organization;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Project>
 */
final class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        $name = $this->faker->sentence(3);

        return [
            'name'            => $name,
            'slug'            => Str::slug($name),
            'description'     => $this->faker->paragraph(),
            'status'          => $this->faker->randomElement(['active', 'completed', 'archived']),
            'type'            => 'organizational',
            'organization_id' => Organization::factory(),
            'start_date'      => $this->faker->dateTimeBetween('-6 months', 'now'),
            'end_date'        => $this->faker->optional()->dateTimeBetween('now', '+6 months'),
            'metadata'        => [
                'priority' => $this->faker->randomElement(['low', 'medium', 'high']),
                'budget'   => $this->faker->numberBetween(1000, 100000),
                'tags'     => $this->faker->words(3),
            ],
        ];
    }

    public function personal(): static
    {
        return $this->state(fn (array $attributes) => [
            'type'            => 'personal',
            'user_id'         => User::factory(),
            'organization_id' => null,
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'   => 'completed',
            'end_date' => $this->faker->dateTimeBetween('-3 months', 'now'),
        ]);
    }

    public function withMembers(int $count = 3): static
    {
        return $this->afterCreating(function (Project $project) use ($count) {
            $users = User::factory($count)->create();

            $users->each(function ($user, $index) use ($project) {
                $role = $index === 0 ? 'lead' : 'member';
                $project->addMember($user, $role);
            });
        });
    }
}
