<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AdminUser;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AdminUser>
 */
final class AdminUserFactory extends Factory
{
    protected $model = AdminUser::class;

    public function definition(): array
    {
        return [
            'name'              => fake()->name(),
            'email'             => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            // Use a strong default so SecurePasswordHandling validates before hashing.
            'password'       => 'Admin123!',
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function withPassword(string $password): static
    {
        return $this->state(fn (array $attributes) => [
            // Accept raw passwords so the model mutator can validate and hash consistently.
            'password' => $password,
        ]);
    }
}
