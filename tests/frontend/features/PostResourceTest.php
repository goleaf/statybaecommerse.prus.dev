<?php

declare(strict_types=1);

use App\Enums\ModerationState;
use App\Filament\Resources\PostResource;
use App\Filament\Resources\PostResource\Pages\CreatePost;
use App\Filament\Resources\PostResource\Pages\EditPost;
use App\Filament\Resources\PostResource\Pages\ListPosts;
use App\Filament\Resources\PostResource\Pages\ViewPost;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('can list posts', function (): void {
    $posts = Post::factory()->count(3)->create();

    Livewire::test(ListPosts::class)
        ->assertCanSeeTableRecords($posts);
});

it('can create a post', function (): void {
    $newPost = Post::factory()->make();

    Livewire::test(CreatePost::class)
        ->fillForm([
            'title.lt'            => $newPost->title,
            'title.en'            => $newPost->title,
            'title.de'            => null,
            'title.ru'            => null,
            'slug'                => $newPost->slug,
            'content.lt'          => $newPost->content,
            'content.en'          => $newPost->content,
            'content.de'          => null,
            'content.ru'          => null,
            'excerpt.lt'          => $newPost->excerpt,
            'excerpt.en'          => $newPost->excerpt,
            'excerpt.de'          => null,
            'excerpt.ru'          => null,
            'status'              => $newPost->status,
            'user_id'             => $this->user->id,
            'meta_title.lt'       => $newPost->meta_title,
            'meta_title.en'       => $newPost->meta_title,
            'meta_title.de'       => null,
            'meta_title.ru'       => null,
            'meta_description.lt' => $newPost->meta_description,
            'meta_description.en' => $newPost->meta_description,
            'meta_description.de' => null,
            'meta_description.ru' => null,
            'featured'            => $newPost->featured,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('posts', [
        'title'   => $newPost->title,
        'slug'    => $newPost->slug,
        'user_id' => $this->user->id,
    ]);
});

it('can edit a post', function (): void {
    $post = Post::factory()->published()->create(['user_id' => $this->user->id]);
    $newTitle = 'Updated Title';

    Livewire::test(EditPost::class, ['record' => $post->getRouteKey()])
        ->fillForm([
            'title.lt' => $newTitle,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($post->fresh()->title)->toBe($newTitle);
});

it('saves tags as a comma separated string', function () {
    $post = Post::factory()->create([
        'user_id' => $this->user->id,
        'tags'    => 'alpha, beta',
    ]);

    Livewire::test(EditPost::class, ['record' => $post->getRouteKey()])
        ->fillForm([
            'tags' => ['news', 'updates', 'features'],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($post->fresh()->tags)->toBe('news, updates, features');
});

it('can view a post', function () {
    $post = Post::factory()->published()->create(['user_id' => $this->user->id]);

    $expectedTitleState = [
        'lt' => $post->title,
        'en' => $post->title_translations['en'] ?? null,
        'de' => $post->title_translations['de'] ?? null,
        'ru' => $post->title_translations['ru'] ?? null,
    ];

    $expectedContentState = [
        'lt' => $post->content,
        'en' => $post->content_translations['en'] ?? null,
        'de' => $post->content_translations['de'] ?? null,
        'ru' => $post->content_translations['ru'] ?? null,
    ];

    Livewire::test(ViewPost::class, ['record' => $post->getRouteKey()])
        ->assertFormSet([
            'title'   => $expectedTitleState,
            'content' => $expectedContentState,
        ]);
});

it('can delete a post', function (): void {
    $post = Post::factory()->create(['user_id' => $this->user->id]);

    Livewire::test(ListPosts::class)
        ->callTableAction('delete', $post);

    $this->assertDatabaseMissing('posts', [
        'id' => $post->id,
    ]);
});

it('persists translations from language tabs', function (): void {
    $formPayload = [
        // Provide translations for every multilingual field to emulate a real Language Tabs submission.
        'title.lt'            => 'Pavadinimas',
        'title.en'            => 'Title',
        'title.de'            => 'Titel',
        'title.ru'            => null,
        'excerpt.lt'          => 'Santrauka',
        'excerpt.en'          => 'Summary',
        'excerpt.de'          => 'Zusammenfassung',
        'excerpt.ru'          => null,
        'content.lt'          => '<p>Turinys</p>',
        'content.en'          => '<p>Content</p>',
        'content.de'          => '<p>Inhalt</p>',
        'content.ru'          => null,
        'meta_title.lt'       => 'Meta LT',
        'meta_title.en'       => 'Meta EN',
        'meta_title.de'       => 'Meta DE',
        'meta_title.ru'       => null,
        'meta_description.lt' => 'LT meta',
        'meta_description.en' => 'EN meta',
        'meta_description.de' => 'DE meta',
        'meta_description.ru' => null,
        'slug'                => 'pavadinimas',
        'status'              => 'draft',
        'user_id'             => $this->user->id,
        'featured'            => false,
    ];

    Livewire::test(CreatePost::class)
        ->fillForm($formPayload)
        ->assertSet('data.title', function ($value): bool {
            expect($value)->toBeArray();
            expect($value)->toMatchArray([
                'lt' => 'Pavadinimas',
                'en' => 'Title',
                'de' => 'Titel',
                'ru' => null,
            ]);

            return true;
        });
});

it('can publish a post', function (): void {
    $post = Post::factory()->draft()->create(['user_id' => $this->user->id]);

    Livewire::test(ListPosts::class)
        ->callTableAction('publish', $post);

    $post->refresh();

    expect($post->status)->toBe('published');
    expect($post->moderation_state)->toBe(ModerationState::Published);
    expect($post->published_at)->not->toBeNull();
    expect($post->submitted_for_review_at)->not->toBeNull();
    expect($post->approved_at)->not->toBeNull();
    expect($post->approved_by_id)->toBe($this->user->id);
});

it('can unpublish a post', function (): void {
    $post = Post::factory()->published()->create(['user_id' => $this->user->id]);

    Livewire::test(ListPosts::class)
        ->callTableAction('unpublish', $post);

    $post->refresh();

    expect($post->status)->toBe('draft');
    expect($post->moderation_state)->toBe(ModerationState::Draft);
    expect($post->published_at)->toBeNull();
    expect($post->submitted_for_review_at)->toBeNull();
    expect($post->approved_at)->toBeNull();
    expect($post->approved_by_id)->toBeNull();
});

it('can archive a post', function (): void {
    $post = Post::factory()->published()->create(['user_id' => $this->user->id]);

    Livewire::test(ListPosts::class)
        ->callTableAction('archive', $post);

    $post->refresh();

    expect($post->status)->toBe('archived');
    expect($post->moderation_state)->toBe(ModerationState::Draft);
    expect($post->approved_at)->toBeNull();
    expect($post->approved_by_id)->toBeNull();
});

it('can feature a post', function (): void {
    $post = Post::factory()->create(['featured' => false, 'user_id' => $this->user->id]);

    Livewire::test(ListPosts::class)
        ->callTableAction('feature', $post);

    expect($post->fresh()->featured)->toBeTrue();
});

it('can unfeature a post', function (): void {
    $post = Post::factory()->featured()->create(['user_id' => $this->user->id]);

    Livewire::test(ListPosts::class)
        ->callTableAction('unfeature', $post);

    expect($post->fresh()->featured)->toBeFalse();
});

it('can filter posts by status', function (): void {
    Post::factory()->published()->create();
    Post::factory()->draft()->create();
    Post::factory()->archived()->create();

    Livewire::test(ListPosts::class)
        ->filterTable('status', 'published')
        ->assertCanSeeTableRecords(Post::where('status', 'published')->get())
        ->assertCanNotSeeTableRecords(Post::where('status', 'draft')->get());
});

it('can filter posts by featured status', function (): void {
    Post::factory()->featured()->create();
    Post::factory()->create(['featured' => false]);

    Livewire::test(ListPosts::class)
        ->filterTable('featured', true)
        ->assertCanSeeTableRecords(Post::where('featured', true)->get())
        ->assertCanNotSeeTableRecords(Post::where('featured', false)->get());
});

it('can filter posts by author', function (): void {
    $anotherUser = User::factory()->create();
    Post::factory()->create(['user_id' => $this->user->id]);
    Post::factory()->create(['user_id' => $anotherUser->id]);

    Livewire::test(ListPosts::class)
        ->filterTable('user_id', $this->user->id)
        ->assertCanSeeTableRecords(Post::where('user_id', $this->user->id)->get())
        ->assertCanNotSeeTableRecords(Post::where('user_id', $anotherUser->id)->get());
});

it('can filter posts by published date range', function (): void {
    $oldPost = Post::factory()->create(['published_at' => now()->subYear()]);
    $recentPost = Post::factory()->create(['published_at' => now()->subMonth()]);

    Livewire::test(ListPosts::class)
        ->filterTable('published_at', [
            'published_from'  => now()->subMonths(2)->format('Y-m-d'),
            'published_until' => now()->format('Y-m-d'),
        ])
        ->assertCanSeeTableRecords([$recentPost])
        ->assertCanNotSeeTableRecords([$oldPost]);
});

it('can search posts by title', function (): void {
    $post1 = Post::factory()->create(['title' => 'Unique Title']);
    $post2 = Post::factory()->create(['title' => 'Another Title']);

    Livewire::test(ListPosts::class)
        ->searchTable('Unique')
        ->assertCanSeeTableRecords([$post1])
        ->assertCanNotSeeTableRecords([$post2]);
});

it('can search posts by excerpt', function (): void {
    $post1 = Post::factory()->create(['excerpt' => 'Unique excerpt content']);
    $post2 = Post::factory()->create(['excerpt' => 'Another excerpt content']);

    Livewire::test(ListPosts::class)
        ->searchTable('Unique excerpt')
        ->assertCanSeeTableRecords([$post1])
        ->assertCanNotSeeTableRecords([$post2]);
});

it('can sort posts by title', function (): void {
    $post1 = Post::factory()->create(['title' => 'A Title']);
    $post2 = Post::factory()->create(['title' => 'Z Title']);

    Livewire::test(ListPosts::class)
        ->sortTable('title')
        ->assertCanSeeTableRecords([$post1, $post2], inOrder: true);
});

it('can sort posts by created date', function (): void {
    $post1 = Post::factory()->create(['created_at' => now()->subDay()]);
    $post2 = Post::factory()->create(['created_at' => now()]);

    Livewire::test(ListPosts::class)
        ->sortTable('created_at', 'desc')
        ->assertCanSeeTableRecords([$post2, $post1], inOrder: true);
});

it('can export posts', function (): void {
    Post::factory()->count(3)->create();

    Livewire::test(ListPosts::class)
        ->callTableBulkAction('export')
        ->assertFileDownloaded();
});

it('can bulk delete posts', function (): void {
    $posts = Post::factory()->count(3)->create();

    Livewire::test(ListPosts::class)
        ->callTableBulkAction('delete', $posts);

    foreach ($posts as $post) {
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }
});

it('validates required fields on create', function (): void {
    Livewire::test(CreatePost::class)
        ->fillForm([
            'title'   => '',
            'slug'    => '',
            'content' => '',
            'user_id' => '',
        ])
        ->call('create')
        ->assertHasFormErrors(['title', 'slug', 'content', 'user_id']);
});

it('validates unique slug', function (): void {
    $existingPost = Post::factory()->create();
    $newPost = Post::factory()->make(['slug' => $existingPost->slug]);

    Livewire::test(CreatePost::class)
        ->fillForm([
            'title'   => $newPost->title,
            'slug'    => $newPost->slug,
            'content' => $newPost->content,
            'user_id' => $this->user->id,
        ])
        ->call('create')
        ->assertHasFormErrors(['slug']);
});

it('validates slug format', function (): void {
    Livewire::test(CreatePost::class)
        ->fillForm([
            'title'   => 'Test Title',
            'slug'    => 'invalid slug with spaces!',
            'content' => 'Test content',
            'user_id' => $this->user->id,
        ])
        ->call('create')
        ->assertHasFormErrors(['slug']);
});

it('can access post resource pages', function (): void {
    $this->get(PostResource::getUrl('index'))->assertOk();
    $this->get(PostResource::getUrl('create'))->assertOk();
});

it('can access post resource pages with record', function (): void {
    $post = Post::factory()->create(['user_id' => $this->user->id]);

    $this->get(PostResource::getUrl('view', ['record' => $post]))->assertOk();
    $this->get(PostResource::getUrl('edit', ['record' => $post]))->assertOk();
});
