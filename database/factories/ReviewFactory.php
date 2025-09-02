<?php declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewFactory extends Factory
{
    protected $model = \Shop\Core\Models\Review::class;

    public function definition(): array
    {
        $productId = \App\Models\Product::query()->inRandomOrder()->value('id');
        if (!$productId) {
            $productId = \App\Models\Product::factory()->create()->id;
        }

        $userId = \App\Models\User::query()->inRandomOrder()->value('id');
        if (!$userId) {
            $userId = \App\Models\User::factory()->create()->id;
        }

        return [
            'reviewrateable_type' => \App\Models\Product::class,
            'reviewrateable_id' => $productId,
            'author_type' => \App\Models\User::class,
            'author_id' => $userId,
            'title' => $this->faker->sentence(6),
            'content' => $this->faker->paragraphs(2, true),
            'rating' => $this->faker->numberBetween(1, 5),
            'is_recommended' => $this->faker->boolean(10),
            'approved' => $this->faker->boolean(60),
            'locale' => $this->faker->randomElement(array_map('trim', explode(',', (string) config('app.supported_locales', 'en')))),
        ];
    }
}
