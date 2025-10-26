<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\News;
use App\Models\NewsComment;
use App\Models\Translations\NewsTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class NewsCommentControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_creates_pending_comment_for_published_news(): void
    {
        // Establish a predictable locale for slug lookup.
        app()->setLocale('en');

        // Create a published news article with a known slug translation.
        $news = News::factory()->create();
        NewsTranslation::factory()->create([
            // Associate the translation to the created news entry manually because the factory lacks a relation helper.
            'news_id' => $news->id,
            'locale'  => 'en',
            'slug'    => 'test-news',
        ]);

        // Submit a comment payload to the controller.
        $response = $this->from(route('news.show', 'test-news'))->post(route('news.comments.store', 'test-news'), [
            'author_name'  => 'Jane Tester',
            'author_email' => 'jane@example.com',
            'content'      => 'Thoughtful comment goes here.',
        ]);

        // Confirm the visitor is redirected back to the article with a success flash message.
        $response->assertRedirect(route('news.show', 'test-news'));
        $response->assertSessionHas('success', __('news.comment_success'));

        // Verify the comment record was stored with the expected defaults.
        $this->assertDatabaseHas('news_comments', [
            'news_id'      => $news->id,
            'author_email' => 'jane@example.com',
            'is_approved'  => false,
            'is_visible'   => true,
        ]);
    }

    public function test_store_rejects_parent_comment_from_different_article(): void
    {
        // Work within the English locale for consistent slug generation.
        app()->setLocale('en');

        // Create two separate news articles with translations so routes can resolve.
        $firstNews = News::factory()->create();
        NewsTranslation::factory()->create([
            // Bind the translation back to the first article for slug resolution.
            'news_id' => $firstNews->id,
            'locale'  => 'en',
            'slug'    => 'first-news',
        ]);

        $secondNews = News::factory()->create();
        NewsTranslation::factory()->create([
            // Attach the translation to the second article to expose the slug in tests.
            'news_id' => $secondNews->id,
            'locale'  => 'en',
            'slug'    => 'second-news',
        ]);

        // Seed an approved parent comment on the first news article.
        $foreignParent = NewsComment::factory()->create([
            'news_id'      => $firstNews->id,
            'author_email' => 'parent@example.com',
        ]);

        // Attempt to reply to the second article while referencing the foreign parent comment.
        $response = $this->from(route('news.show', 'second-news'))->post(route('news.comments.store', 'second-news'), [
            'parent_id'    => $foreignParent->id,
            'author_name'  => 'Reply Author',
            'author_email' => 'reply@example.com',
            'content'      => 'Replying to an unrelated comment should fail.',
        ]);

        // The controller should reject the payload and surface a validation error for the parent comment.
        $response->assertRedirect(route('news.show', 'second-news'));
        $response->assertSessionHasErrors(['parent_id']);

        // Ensure a new comment was not persisted for the second article.
        $this->assertSame(1, NewsComment::query()->count());
    }
}
