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

        $comments = [
            [
                'content' => 'This is a great article! Very informative and well-written.',
                'author_name' => 'John Doe',
                'author_email' => 'john.doe@example.com',
                'is_approved' => true,
                'is_visible' => true,
            ],
            [
                'content' => 'I disagree with some points made here. Let me explain why...',
                'author_name' => 'Jane Smith',
                'author_email' => 'jane.smith@example.com',
                'is_approved' => true,
                'is_visible' => true,
            ],
            [
                'content' => 'Thanks for sharing this information. It was very helpful!',
                'author_name' => 'Mike Johnson',
                'author_email' => 'mike.johnson@example.com',
                'is_approved' => false,
                'is_visible' => true,
            ],
            [
                'content' => 'This is spam content that should not be approved.',
                'author_name' => 'Spam User',
                'author_email' => 'spam@spam.com',
                'is_approved' => false,
                'is_visible' => false,
            ],
            [
                'content' => 'Excellent article! I learned a lot from reading this.',
                'author_name' => 'Sarah Wilson',
                'author_email' => 'sarah.wilson@example.com',
                'is_approved' => true,
                'is_visible' => true,
            ],
            [
                'content' => 'I have a question about the third point mentioned in the article.',
                'author_name' => 'David Brown',
                'author_email' => 'david.brown@example.com',
                'is_approved' => true,
                'is_visible' => true,
            ],
            [
                'content' => 'This article changed my perspective on the topic completely.',
                'author_name' => 'Lisa Davis',
                'author_email' => 'lisa.davis@example.com',
                'is_approved' => true,
                'is_visible' => true,
            ],
            [
                'content' => 'Could you provide more details about the statistics mentioned?',
                'author_name' => 'Robert Taylor',
                'author_email' => 'robert.taylor@example.com',
                'is_approved' => false,
                'is_visible' => true,
            ],
        ];

        foreach ($news as $newsArticle) {
            // Create 2-5 comments per news article
            $commentCount = rand(2, 5);

            for ($i = 0; $i < $commentCount; $i++) {
                $commentData = $comments[array_rand($comments)];
                $commentData['news_id'] = $newsArticle->id;

                $comment = NewsComment::create($commentData);

                // Sometimes create a reply to this comment
                if (rand(0, 1) && $comment->is_approved) {
                    $replyData = $comments[array_rand($comments)];
                    $replyData['news_id'] = $newsArticle->id;
                    $replyData['parent_id'] = $comment->id;

                    NewsComment::create($replyData);
                }
            }
        }

        $this->command->info('News comments seeded successfully!');
    }
}
