<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\NewsResource\Pages\EditNews;
use App\Filament\Resources\NewsResource\RelationManagers\CommentsRelationManager;
use App\Models\News;
use App\Models\NewsComment;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Tables\Testing\TestBulkAction;
use Filament\Tables\Testing\TestFilter;
use Filament\Tables\Testing\TestSearch;
use Filament\Testing\Livewire\Concerns\InteractsWithForms;
use Filament\Testing\Livewire\Concerns\InteractsWithTable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Pest\Laravel\actingAs;
use Livewire\Livewire;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;
use function PHPUnit\Framework\assertCount;

beforeEach(function (): void {
    Filament::setCurrentPanel('admin');

    $this->adminUser = User::factory()->create([
        'email' => 'admin@example.com',
        'is_admin' => true,
    ]);

    actingAs($this->adminUser);

    $this->news = News::factory()->create([
        'title' => 'Test News',
    ]);
});

it('displays comments table with existing records', function (): void {
    $comments = NewsComment::factory()->count(3)->create([
        'news_id' => $this->news->id,
    ]);

    livewire(CommentsRelationManager::class, [
        'ownerRecord' => $this->news,
        'pageClass' => EditNews::class,
    ])
        ->assertCanSeeTableRecords($comments)
        ->assertTableColumnState('content', $comments->first(), fn (?string $state) => $state !== null);
});

it('creates a comment through the relation manager', function (): void {
    $component = livewire(CommentsRelationManager::class, [
        'ownerRecord' => $this->news,
        'pageClass' => EditNews::class,
    ]);

    $component
        ->callTableAction('create', data: [
            'author_name' => 'Jonas Jonaitis',
            'author_email' => 'jonas@example.lt',
            'content' => 'Puikus straipsnis apie technologijas.',
            'is_approved' => true,
            'is_visible' => true,
        ])
        ->assertHasNoTableActionErrors();

    expect(NewsComment::query()->where('news_id', $this->news->id)->exists())->toBeTrue();
});

it('edits an existing comment', function (): void {
    $comment = NewsComment::factory()->create([
        'news_id' => $this->news->id,
        'author_name' => 'Pradinis Autorius',
    ]);

    livewire(CommentsRelationManager::class, [
        'ownerRecord' => $this->news,
        'pageClass' => EditNews::class,
    ])
        ->callTableAction('edit', $comment, [
            'author_name' => 'Atnaujintas Autorius',
            'content' => 'Atnaujintas komentaras',
            'is_visible' => false,
        ])
        ->assertHasNoTableActionErrors();

    expect($comment->fresh())
        ->author_name->toBe('Atnaujintas Autorius')
        ->content->toBe('Atnaujintas komentaras')
        ->is_visible->toBeFalse();
});

it('deletes a comment via table action', function (): void {
    $comment = NewsComment::factory()->create([
        'news_id' => $this->news->id,
    ]);

    livewire(CommentsRelationManager::class, [
        'ownerRecord' => $this->news,
        'pageClass' => EditNews::class,
    ])
        ->callTableAction('delete', $comment)
        ->assertHasNoTableActionErrors();

    expect(NewsComment::query()->whereKey($comment->getKey())->exists())->toBeFalse();
});

it('creates nested replies', function (): void {
    $parent = NewsComment::factory()->create([
        'news_id' => $this->news->id,
    ]);

    livewire(CommentsRelationManager::class, [
        'ownerRecord' => $this->news,
        'pageClass' => EditNews::class,
    ])
        ->callTableAction('create', data: [
            'author_name' => 'Atsakymo Autorius',
            'author_email' => 'atsakymas@example.lt',
            'content' => 'Tai atsakymas į komentarą.',
            'parent_id' => $parent->id,
        ])
        ->assertHasNoTableActionErrors();

    $reply = NewsComment::query()
        ->where('parent_id', $parent->id)
        ->where('news_id', $this->news->id)
        ->first();

    expect($reply)->not->toBeNull();
});

it('filters by approval and visibility', function (): void {
    $approved = NewsComment::factory()->create([
        'news_id' => $this->news->id,
        'is_approved' => true,
        'is_visible' => true,
    ]);

    $pending = NewsComment::factory()->create([
        'news_id' => $this->news->id,
        'is_approved' => false,
        'is_visible' => false,
    ]);

    $component = livewire(CommentsRelationManager::class, [
        'ownerRecord' => $this->news,
        'pageClass' => EditNews::class,
    ]);

    $component
        ->filterTable('is_approved', 'true')
        ->assertCanSeeTableRecords([$approved])
        ->assertCanNotSeeTableRecords([$pending]);

    $component
        ->filterTable('is_visible', 'true')
        ->assertCanSeeTableRecords([$approved])
        ->assertCanNotSeeTableRecords([$pending]);
});

it('searches by author and content', function (): void {
    $matching = NewsComment::factory()->create([
        'news_id' => $this->news->id,
        'author_name' => 'Technologijų Guru',
        'content' => 'Technologijų pažanga yra nuostabi.',
    ]);

    $other = NewsComment::factory()->create([
        'news_id' => $this->news->id,
        'author_name' => 'Sporto Fanatikas',
        'content' => 'Šis komentaras apie sportą.',
    ]);

    livewire(CommentsRelationManager::class, [
        'ownerRecord' => $this->news,
        'pageClass' => EditNews::class,
    ])
        ->searchTable('technologijų')
        ->assertCanSeeTableRecords([$matching])
        ->assertCanNotSeeTableRecords([$other]);
});

it('toggles approval status via custom action', function (): void {
    $comment = NewsComment::factory()->create([
        'news_id' => $this->news->id,
        'is_approved' => false,
    ]);

    livewire(CommentsRelationManager::class, [
        'ownerRecord' => $this->news,
        'pageClass' => EditNews::class,
    ])
        ->callTableAction('toggle_approval', $comment)
        ->assertHasNoTableActionErrors();

    expect($comment->fresh()->is_approved)->toBeTrue();
});

it('bulk approves and disapproves comments', function (): void {
    $records = NewsComment::factory()->count(3)->create([
        'news_id' => $this->news->id,
        'is_approved' => false,
    ]);

    $component = livewire(CommentsRelationManager::class, [
        'ownerRecord' => $this->news,
        'pageClass' => EditNews::class,
    ]);

    $component
        ->callTableBulkAction('approve', $records)
        ->assertHasNoTableActionErrors();

    expect($records->fresh()->every(fn (NewsComment $comment): bool => $comment->is_approved))->toBeTrue();

    $component
        ->callTableBulkAction('disapprove', $records)
        ->assertHasNoTableActionErrors();

    expect($records->fresh()->every(fn (NewsComment $comment): bool => $comment->is_approved === false))->toBeTrue();
});

it('validates required fields when creating a comment', function (): void {
    livewire(CommentsRelationManager::class, [
        'ownerRecord' => $this->news,
        'pageClass' => EditNews::class,
    ])
        ->callTableAction('create', data: [
            'author_name' => null,
            'author_email' => 'neteisingas-el',
            'content' => null,
        ])
        ->assertHasTableActionErrors([
            'author_name' => ['validation.required'],
            'author_email' => ['validation.email'],
            'content' => ['validation.required'],
        ]);
});

it('validates parent selection for nested replies', function (): void {
    livewire(CommentsRelationManager::class, [
        'ownerRecord' => $this->news,
        'pageClass' => EditNews::class,
    ])
        ->callTableAction('create', data: [
            'author_name' => 'Komentatorius',
            'author_email' => 'komentatorius@example.lt',
            'content' => 'Sveiki!',
            'parent_id' => 999999,
        ])
        ->assertHasTableActionErrors([
            'parent_id' => ['validation.exists'],
        ]);
});
