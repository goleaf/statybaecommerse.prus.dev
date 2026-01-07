<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
final class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'title'       => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'status'      => $this->faker->randomElement(['pending', 'in_progress', 'completed', 'cancelled']),
            'priority'    => $this->faker->randomElement(['low', 'medium', 'high', 'urgent']),
            'project_id'  => Project::factory(),
            'created_by'  => User::factory(),
            'due_date'    => $this->faker->optional()->dateTimeBetween('now', '+3 months'),
            'metadata'    => [
                'estimated_hours' => $this->faker->numberBetween(1, 40),
                'complexity'      => $this->faker->randomElement(['simple', 'medium', 'complex']),
            ],
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'       => 'pending',
            'completed_at' => null,
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'       => 'in_progress',
            'completed_at' => null,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'       => 'completed',
            'completed_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'due_date' => $this->faker->dateTimeBetween('-1 month', '-1 day'),
            'status'   => $this->faker->randomElement(['pending', 'in_progress']),
        ]);
    }

    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => $this->faker->randomElement(['high', 'urgent']),
        ]);
    }

    public function subtask(): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_task_id' => Task::factory(),
        ]);
    }

    public function withAssignees(int $count = 2): static
    {
        return $this->afterCreating(function (Task $task) use ($count) {
            $users = User::factory($count)->create();

            $users->each(function ($user, $index) use ($task) {
                $responsibility = match ($index) {
                    0       => 'assignee',
                    1       => 'reviewer',
                    default => 'watcher',
                };

                $task->assignUser($user, $responsibility);
            });
        });
    }

    public function withComments(int $count = 3): static
    {
        return $this->afterCreating(function (Task $task) use ($count) {
            $users = User::factory($count)->create();

            $users->each(function ($user) use ($task) {
                $task->addComment($this->faker->paragraph(), $user);
            });
        });
    }
}
