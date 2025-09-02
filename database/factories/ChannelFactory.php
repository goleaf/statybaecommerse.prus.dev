<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\Channel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\Channel>
 */
class ChannelFactory extends Factory
{
    protected $model = Channel::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->company() . ' Channel';
        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $this->faker->boolean(50) ? $this->faker->sentence(10) : null,
            'timezone' => $this->faker->timezone(),
            'url' => $this->faker->url(),
            'is_default' => false,
            'is_enabled' => true,
            'metadata' => null,
        ];
    }
}
