<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Models\News;
use App\Models\NewsComment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class NewsCommentsResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $adminUser = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->actingAs($adminUser);
    }

    public function test_list_shows_unapproved_and_hidden_comments(): void
    {
        $news = News::factory()->create();
        $comment = NewsComment::factory()->create([
            'news_id'     => $news->id,
            'is_approved' => false,
            'is_visible'  => false,
        ]);

        Livewire::test(\App\Filament\Resources\NewsComments\Pages\ListNewsComments::class)
            ->assertCanSeeTableRecords([$comment]);
    }

    public function test_toggle_approval_action_updates_record(): void
    {
        $news = News::factory()->create();
        $comment = NewsComment::factory()->create([
            'news_id'     => $news->id,
            'is_approved' => false,
        ]);

        Livewire::test(\App\Filament\Resources\NewsComments\Pages\ListNewsComments::class)
            ->callTableAction('toggle_approval', $comment)
            ->assertHasNoTableActionErrors();

        $this->assertTrue($comment->fresh()->is_approved);
    }

    public function test_bulk_approve_action_marks_records_as_approved(): void
    {
        $news = News::factory()->create();
        $comments = NewsComment::factory()->count(3)->create([
            'news_id'     => $news->id,
            'is_approved' => false,
        ]);

        Livewire::test(\App\Filament\Resources\NewsComments\Pages\ListNewsComments::class)
            ->callTableBulkAction('approve', $comments)
            ->assertHasNoTableBulkActionErrors();

        foreach ($comments as $comment) {
            $this->assertTrue($comment->fresh()->is_approved);
        }
    }
}
