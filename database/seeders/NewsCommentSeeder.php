<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\News;
use App\Models\NewsComment;
use Illuminate\Database\Seeder;

final class NewsCommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $news = News::all();

        if ($news->isEmpty()) {
            $this->command->warn('No news articles found. Please run NewsSeeder first.');

            return;
        }

        foreach ($news as $newsArticle) {
            $comments = NewsComment::factory()
                ->for($newsArticle, 'news')
                ->count(fake()->numberBetween(2, 5))
                ->create();

            $comments
                ->where('is_approved', true)
                ->each(function (NewsComment $comment): void {
                    if (fake()->boolean()) {
                        NewsComment::factory()
                            ->reply($comment)
                            ->state(['is_approved' => fake()->boolean()])
                            ->create();
                    }
                });
        }

        $this->command->info('News comments seeded successfully!');
    }
}
