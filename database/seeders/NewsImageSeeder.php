<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\News;
use App\Models\NewsImage;
use Illuminate\Database\Seeder;

final class NewsImageSeeder extends Seeder
{
    public function run(): void
    {
        $news = News::all();

        if ($news->isEmpty()) {
            $this->command->warn('No news articles found. Please run NewsSeeder first.');

            return;
        }

        foreach ($news as $newsArticle) {
            NewsImage::factory()
                ->for($newsArticle, 'news')
                ->count(fake()->numberBetween(1, 5))
                ->state(function (array $attributes, NewsImage $newsImage, int $index) {
                    return [
                        'is_featured' => $index === 0,
                        'sort_order' => $index + 1,
                        'file_path' => 'news-images/'.fake()->uuid().'.jpg',
                        'alt_text' => fake()->sentence(6),
                        'caption' => fake()->sentence(10),
                        'file_size' => fake()->numberBetween(100000, 2000000),
                        'mime_type' => fake()->randomElement([
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                            'image/webp',
                        ]),
                        'dimensions' => [
                            'width' => fake()->numberBetween(400, 1920),
                            'height' => fake()->numberBetween(300, 1080),
                        ],
                    ];
                })
                ->create();
        }

        $this->command->info('News images seeded successfully.');
    }
}
