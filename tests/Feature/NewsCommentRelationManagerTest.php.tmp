<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\News;
use App\Models\NewsComment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\TestCase;

final class NewsCommentRelationManagerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]));
    }

    public function test_can_list_news_comments(): void
    {
        $news = News::factory()->create();
        NewsComment::factory()->count(3)->create(['news_id' => $news->id]);

        $this
            ->get("/admin/news/{$news->id}/edit")
            ->assertOk()
            ->assertSee('Comments');
    }

    public function test_can_create_news_comment(): void
    {
        $news = News::factory()->create();

        $commentData = [
            'author_name' => 'Test Commenter',
            'author_email' => 'commenter@example.com',
            'content' => 'This is a test comment.',
            'is_approved' => true,
            'is_visible' => true,
        ];

        $this
            ->post("/admin/news/{$news->id}/comments", $commentData)
            ->assertRedirect();

        $this->assertDatabaseHas('news_comments', [
            'news_id' => $news->id,
            'author_name' => 'Test Commenter',
            'author_email' => 'commenter@example.com',
            'content' => 'This is a test comment.',
            'is_approved' => true,
            'is_visible' => true,
        ]);
    }

    public function test_can_edit_news_comment(): void
    {
        $news = News::factory()->create();
        $comment = NewsComment::factory()->create([
            'news_id' => $news->id,
            'author_name' => 'Original Author',
        ]);

        $updateData = [
            'author_name' => 'Updated Author',
            'is_approved' => true,
        ];

        $this
            ->put("/admin/news/{$news->id}/comments/{$comment->id}", $updateData)
            ->assertRedirect();

        $this->assertDatabaseHas('news_comments', [
            'id' => $comment->id,
            'author_name' => 'Updated Author',
            'is_approved' => true,
        ]);
    }

    public function test_can_delete_news_comment(): void
    {
        $news = News::factory()->create();
        $comment = NewsComment::factory()->create(['news_id' => $news->id]);

        $this
            ->delete("/admin/news/{$news->id}/comments/{$comment->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('news_comments', [
            'id' => $comment->id,
        ]);
    }

    public function test_can_create_nested_comment(): void
    {
        $news = News::factory()->create();
        $parentComment = NewsComment::factory()->create([
            'news_id' => $news->id,
        ]);

        $replyData = [
            'author_name' => 'Reply Author',
            'author_email' => 'reply@example.com',
            'content' => 'This is a reply to the parent comment.',
            'parent_id' => $parentComment->id,
            'is_approved' => true,
            'is_visible' => true,
        ];

        $this
            ->post("/admin/news/{$news->id}/comments", $replyData)
            ->assertRedirect();

        $this->assertDatabaseHas('news_comments', [
            'news_id' => $news->id,
            'parent_id' => $parentComment->id,
            'author_name' => 'Reply Author',
            'content' => 'This is a reply to the parent comment.',
        ]);
    }

    public function test_can_filter_comments_by_approval_status(): void
    {
        $news = News::factory()->create();
        NewsComment::factory()->create([
            'news_id' => $news->id,
            'is_approved' => true,
        ]);
        NewsComment::factory()->create([
            'news_id' => $news->id,
            'is_approved' => false,
        ]);

        $response = $this->get("/admin/news/{$news->id}/edit?tableFilters[is_approved][value]=1");

        $response->assertOk();
    }

    public function test_can_filter_comments_by_visibility(): void
    {
        $news = News::factory()->create();
        NewsComment::factory()->create([
            'news_id' => $news->id,
            'is_visible' => true,
        ]);
        NewsComment::factory()->create([
            'news_id' => $news->id,
            'is_visible' => false,
        ]);

        $response = $this->get("/admin/news/{$news->id}/edit?tableFilters[is_visible][value]=1");

        $response->assertOk();
    }

    public function test_comment_validation_requires_author_name(): void
    {
        $news = News::factory()->create();

        $commentData = [
            'author_email' => 'commenter@example.com',
            'content' => 'This is a test comment.',
            'is_approved' => true,
            'is_visible' => true,
        ];

        $response = $this->post("/admin/news/{$news->id}/comments", $commentData);
        $response->assertSessionHasErrors('author_name');
    }

    public function test_comment_validation_requires_author_email(): void
    {
        $news = News::factory()->create();

        $commentData = [
            'author_name' => 'Test Commenter',
            'content' => 'This is a test comment.',
            'is_approved' => true,
            'is_visible' => true,
        ];

        $response = $this->post("/admin/news/{$news->id}/comments", $commentData);
        $response->assertSessionHasErrors('author_email');
    }

    public function test_comment_validation_requires_content(): void
    {
        $news = News::factory()->create();

        $commentData = [
            'author_name' => 'Test Commenter',
            'author_email' => 'commenter@example.com',
            'is_approved' => true,
            'is_visible' => true,
        ];

        $response = $this->post("/admin/news/{$news->id}/comments", $commentData);
        $response->assertSessionHasErrors('content');
    }

    public function test_comment_validation_author_email_must_be_valid(): void
    {
        $news = News::factory()->create();

        $commentData = [
            'author_name' => 'Test Commenter',
            'author_email' => 'invalid-email',
            'content' => 'This is a test comment.',
            'is_approved' => true,
            'is_visible' => true,
        ];

        $response = $this->post("/admin/news/{$news->id}/comments", $commentData);
        $response->assertSessionHasErrors('author_email');
    }

    public function test_can_search_comments_by_content(): void
    {
        $news = News::factory()->create();
        NewsComment::factory()->create([
            'news_id' => $news->id,
            'content' => 'Great article about technology!',
        ]);
        NewsComment::factory()->create([
            'news_id' => $news->id,
            'content' => 'This article is about sports.',
        ]);

        $response = $this->get("/admin/news/{$news->id}/edit?search=technology");

        $response->assertOk();
    }

    public function test_can_search_comments_by_author_name(): void
    {
        $news = News::factory()->create();
        NewsComment::factory()->create([
            'news_id' => $news->id,
            'author_name' => 'John Doe',
            'content' => 'Test comment',
        ]);
        NewsComment::factory()->create([
            'news_id' => $news->id,
            'author_name' => 'Jane Smith',
            'content' => 'Another comment',
        ]);

        $response = $this->get("/admin/news/{$news->id}/edit?search=John");

        $response->assertOk();
    }
}
