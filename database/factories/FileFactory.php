<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\File;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<File>
 */
final class FileFactory extends Factory
{
    protected $model = File::class;

    public function definition(): array
    {
        $originalName = $this->faker->word() . '.' . $this->faker->fileExtension();
        
        return [
            'name' => $this->faker->uuid() . '.' . $this->faker->fileExtension(),
            'original_name' => $originalName,
            'path' => 'uploads/' . $this->faker->uuid() . '/' . $originalName,
            'disk' => 'local',
            'mime_type' => $this->faker->mimeType(),
            'size' => $this->faker->numberBetween(1024, 10485760), // 1KB to 10MB
            'hash' => $this->faker->sha256(),
            'uploaded_by' => User::factory(),
            'metadata' => [
                'width' => $this->faker->optional()->numberBetween(100, 1920),
                'height' => $this->faker->optional()->numberBetween(100, 1080),
            ],
        ];
    }

    public function image(): static
    {
        return $this->state(fn (array $attributes) => [
            'mime_type' => $this->faker->randomElement(['image/jpeg', 'image/png', 'image/gif']),
            'name' => $this->faker->uuid() . '.' . $this->faker->randomElement(['jpg', 'png', 'gif']),
        ]);
    }

    public function document(): static
    {
        return $this->state(fn (array $attributes) => [
            'mime_type' => $this->faker->randomElement(['application/pdf', 'application/msword']),
            'name' => $this->faker->uuid() . '.' . $this->faker->randomElement(['pdf', 'doc']),
        ]);
    }
}