<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\News;
use App\Models\NewsComment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class NewsCommentResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $adminUser = User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->actingAs($adminUser);
    }

    public function test_can_list_news_comments(): void
    {
        // Arrange
        $news = News::factory()->create();
        $comments = NewsComment::factory()->count(3)->create(['news_id' => $news->id]);

        // Act & Assert
        Livewire::test(\App\Filament\Resources\NewsCommentResource\Pages\ListNewsComments::class)
            ->assertCanSeeTableRecords($comments);
    }

    public function test_can_create_news_comment(): void
    {
        // Arrange
        $news = News::factory()->create();
        $commentData = [
            'news_id' => $news->id,
            'author_name' => 'John Doe',
            'author_email' => 'john@example.com',
            'content' => 'This is a test comment',
            'is_approved' => false,
            'is_visible' => true,
        ];

        // Act
        Livewire::test(\App\Filament\Resources\NewsCommentResource\Pages\CreateNewsComment::class)
            ->fillForm($commentData)
            ->call('create')
            ->assertHasNoFormErrors();

        // Assert
        $this->assertDatabaseHas('news_comments', [
            'news_id' => $news->id,
            'author_name' => 'John Doe',
            'author_email' => 'john@example.com',
            'content' => 'This is a test comment',
            'is_approved' => false,
            'is_visible' => true,
        ]);
    }

    public function test_can_edit_news_comment(): void
    {
        // Arrange
        $news = News::factory()->create();
        $comment = NewsComment::factory()->create([
            'news_id' => $news->id,
            'author_name' => 'Original Author',
            'content' => 'Original content',
        ]);

        $updatedData = [
            'author_name' => 'Updated Author',
            'content' => 'Updated content',
            'is_approved' => true,
        ];

        // Act
        Livewire::test(\App\Filament\Resources\NewsCommentResource\Pages\EditNewsComment::class, [
            'record' => $comment->getRouteKey(),
        ])
            ->fillForm($updatedData)
            ->call('save')
            ->assertHasNoFormErrors();

        // Assert
        $this->assertDatabaseHas('news_comments', [
            'id' => $comment->id,
            'author_name' => 'Updated Author',
            'content' => 'Updated content',
            'is_approved' => true,
        ]);
    }

    public function test_can_delete_news_comment(): void
    {
        // Arrange
        $news = News::factory()->create();
        $comment = NewsComment::factory()->create(['news_id' => $news->id]);

        // Act
        Livewire::test(\App\Filament\Resources\NewsCommentResource\Pages\ListNewsComments::class)
            ->callTableAction('delete', $comment);

        // Assert
        $this->assertDatabaseMissing('news_comments', ['id' => $comment->id]);
    }

    public function test_can_toggle_comment_approval(): void
    {
        // Arrange
        $news = News::factory()->create();
        $comment = NewsComment::factory()->create([
            'news_id' => $news->id,
            'is_approved' => false,
        ]);

        // Act
        Livewire::test(\App\Filament\Resources\NewsCommentResource\Pages\ListNewsComments::class)
            ->callTableAction('toggle_approval', $comment);

        // Assert
        $this->assertDatabaseHas('news_comments', [
            'id' => $comment->id,
            'is_approved' => true,
        ]);
    }

    public function test_can_filter_comments_by_news(): void
    {
        // Arrange
        $news1 = News::factory()->create();
        $news2 = News::factory()->create();
        $comment1 = NewsComment::factory()->create(['news_id' => $news1->id]);
        $comment2 = NewsComment::factory()->create(['news_id' => $news2->id]);

        // Act & Assert
        Livewire::test(\App\Filament\Resources\NewsCommentResource\Pages\ListNewsComments::class)
            ->filterTable('news_id', $news1->id)
            ->assertCanSeeTableRecords([$comment1])
            ->assertCanNotSeeTableRecords([$comment2]);
    }

    public function test_can_filter_comments_by_approval_status(): void
    {
        // Arrange
        $news = News::factory()->create();
        $approvedComment = NewsComment::factory()->create([
            'news_id' => $news->id,
            'is_approved' => true,
        ]);
        $unapprovedComment = NewsComment::factory()->create([
            'news_id' => $news->id,
            'is_approved' => false,
        ]);

        // Act & Assert
        Livewire::test(\App\Filament\Resources\NewsCommentResource\Pages\ListNewsComments::class)
            ->filterTable('is_approved', 'true')
            ->assertCanSeeTableRecords([$approvedComment])
            ->assertCanNotSeeTableRecords([$unapprovedComment]);
    }

    public function test_can_filter_comments_by_visibility(): void
    {
        // Arrange
        $news = News::factory()->create();
        $visibleComment = NewsComment::factory()->create([
            'news_id' => $news->id,
            'is_visible' => true,
        ]);
        $hiddenComment = NewsComment::factory()->create([
            'news_id' => $news->id,
            'is_visible' => false,
        ]);

        // Act & Assert
        Livewire::test(\App\Filament\Resources\NewsCommentResource\Pages\ListNewsComments::class)
            ->filterTable('is_visible', 'true')
            ->assertCanSeeTableRecords([$visibleComment])
            ->assertCanNotSeeTableRecords([$hiddenComment]);
    }

    public function test_can_bulk_approve_comments(): void
    {
        // Arrange
        $news = News::factory()->create();
        $comments = NewsComment::factory()->count(3)->create([
            'news_id' => $news->id,
            'is_approved' => false,
        ]);

        // Act
        Livewire::test(\App\Filament\Resources\NewsCommentResource\Pages\ListNewsComments::class)
            ->callTableBulkAction('approve', $comments);

        // Assert
        foreach ($comments as $comment) {
            $this->assertDatabaseHas('news_comments', [
                'id' => $comment->id,
                'is_approved' => true,
            ]);
        }
    }

    public function test_can_bulk_disapprove_comments(): void
    {
        // Arrange
        $news = News::factory()->create();
        $comments = NewsComment::factory()->count(3)->create([
            'news_id' => $news->id,
            'is_approved' => true,
        ]);

        // Act
        Livewire::test(\App\Filament\Resources\NewsCommentResource\Pages\ListNewsComments::class)
            ->callTableBulkAction('disapprove', $comments);

        // Assert
        foreach ($comments as $comment) {
            $this->assertDatabaseHas('news_comments', [
                'id' => $comment->id,
                'is_approved' => false,
            ]);
        }
    }

    public function test_comment_validation_requires_news(): void
    {
        // Act & Assert
        Livewire::test(\App\Filament\Resources\NewsCommentResource\Pages\CreateNewsComment::class)
            ->fillForm([
                'author_name' => 'John Doe',
                'author_email' => 'john@example.com',
                'content' => 'Test comment',
            ])
            ->call('create')
            ->assertHasFormErrors(['news_id']);
    }

    public function test_comment_validation_requires_author_name(): void
    {
        // Arrange
        $news = News::factory()->create();

        // Act & Assert
        Livewire::test(\App\Filament\Resources\NewsCommentResource\Pages\CreateNewsComment::class)
            ->fillForm([
                'news_id' => $news->id,
                'author_email' => 'john@example.com',
                'content' => 'Test comment',
            ])
            ->call('create')
            ->assertHasFormErrors(['author_name']);
    }

    public function test_comment_validation_requires_author_email(): void
    {
        // Arrange
        $news = News::factory()->create();

        // Act & Assert
        Livewire::test(\App\Filament\Resources\NewsCommentResource\Pages\CreateNewsComment::class)
            ->fillForm([
                'news_id' => $news->id,
                'author_name' => 'John Doe',
                'content' => 'Test comment',
            ])
            ->call('create')
            ->assertHasFormErrors(['author_email']);
    }

    public function test_comment_validation_requires_content(): void
    {
        // Arrange
        $news = News::factory()->create();

        // Act & Assert
        Livewire::test(\App\Filament\Resources\NewsCommentResource\Pages\CreateNewsComment::class)
            ->fillForm([
                'news_id' => $news->id,
                'author_name' => 'John Doe',
                'author_email' => 'john@example.com',
            ])
            ->call('create')
            ->assertHasFormErrors(['content']);
    }

    public function test_comment_validation_requires_valid_email(): void
    {
        // Arrange
        $news = News::factory()->create();

        // Act & Assert
        Livewire::test(\App\Filament\Resources\NewsCommentResource\Pages\CreateNewsComment::class)
            ->fillForm([
                'news_id' => $news->id,
                'author_name' => 'John Doe',
                'author_email' => 'invalid-email',
                'content' => 'Test comment',
            ])
            ->call('create')
            ->assertHasFormErrors(['author_email']);
    }

    public function test_can_view_comment_details(): void
    {
        // Arrange
        $news = News::factory()->create();
        $comment = NewsComment::factory()->create([
            'news_id' => $news->id,
            'author_name' => 'John Doe',
            'author_email' => 'john@example.com',
            'content' => 'Test comment content',
        ]);

        // Act & Assert
        Livewire::test(\App\Filament\Resources\NewsCommentResource\Pages\ViewNewsComment::class, [
            'record' => $comment->getRouteKey(),
        ])
            ->assertFormSet([
                'news_id' => $news->id,
                'author_name' => 'John Doe',
                'author_email' => 'john@example.com',
                'content' => 'Test comment content',
            ]);
    }

    public function test_can_search_comments_by_author(): void
    {
        // Arrange
        $news = News::factory()->create();
        $searchableComment = NewsComment::factory()->create([
            'news_id' => $news->id,
            'author_name' => 'Searchable Author',
        ]);
        $otherComment = NewsComment::factory()->create([
            'news_id' => $news->id,
            'author_name' => 'Other Author',
        ]);

        // Act & Assert
        Livewire::test(\App\Filament\Resources\NewsCommentResource\Pages\ListNewsComments::class)
            ->searchTable('Searchable')
            ->assertCanSeeTableRecords([$searchableComment])
            ->assertCanNotSeeTableRecords([$otherComment]);
    }

    public function test_can_create_nested_comment(): void
    {
        // Arrange
        $news = News::factory()->create();
        $parentComment = NewsComment::factory()->create(['news_id' => $news->id]);

        $childCommentData = [
            'news_id' => $news->id,
            'parent_id' => $parentComment->id,
            'author_name' => 'Child Author',
            'author_email' => 'child@example.com',
            'content' => 'This is a reply',
        ];

        // Act
        Livewire::test(\App\Filament\Resources\NewsCommentResource\Pages\CreateNewsComment::class)
            ->fillForm($childCommentData)
            ->call('create')
            ->assertHasNoFormErrors();

        // Assert
        $this->assertDatabaseHas('news_comments', [
            'news_id' => $news->id,
            'parent_id' => $parentComment->id,
            'author_name' => 'Child Author',
            'content' => 'This is a reply',
        ]);
    }
}
