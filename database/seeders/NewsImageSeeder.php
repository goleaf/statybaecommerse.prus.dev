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
            // Create 1-5 images per news article
            $imageCount = fake()->numberBetween(1, 5);

            for ($i = 0; $i < $imageCount; $i++) {
                NewsImage::factory()
                    ->forNews($newsArticle)
                    ->withSortOrder($i + 1)
                    ->create([
                        'file_path' => 'news-images/'.fake()->uuid().'.jpg',
                        'alt_text' => fake()->sentence(6),
                        'caption' => fake()->sentence(10),
                        'is_featured' => $i === 0,  // First image is featured
                        'sort_order' => $i + 1,
                        'file_size' => fake()->numberBetween(100000, 2000000),  // 100KB to 2MB
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
                    ]);
            }
        }

        $this->command->info('News images seeded successfully.');
    }
}
