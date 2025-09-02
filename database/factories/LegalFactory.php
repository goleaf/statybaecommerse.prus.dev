<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\Legal;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\Legal>
 */
class LegalFactory extends Factory
{
    protected $model = Legal::class;

    public function definition(): array
    {
        $title = $this->faker->unique()->randomElement([
            'Privacy Policy',
            'Terms of Use',
            'Refund Policy',
            'Shipping Policy',
        ]);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'content' => '<p>' . $this->faker->paragraphs(3, true) . '</p>',
            'is_enabled' => true,
        ];
    }
}
