<?php declare(strict_types=1);

use App\Filament\Resources\PostResource\Pages\CreatePost;
use App\Filament\Resources\PostResource\Pages\EditPost;
use App\Filament\Resources\PostResource\Pages\ListPosts;
use App\Filament\Resources\PostResource\Pages\ViewPost;
use App\Filament\Resources\PostResource;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('can list posts', function () {
    $posts = Post::factory()->count(3)->create();

    Livewire::test(ListPosts::class)
        ->assertCanSeeTableRecords($posts);
});

it('can create a post', function () {
    $newPost = Post::factory()->make();

    Livewire::test(CreatePost::class)
        ->fillForm([
            'title' => $newPost->title,
            'slug' => $newPost->slug,
            'content' => $newPost->content,
            'excerpt' => $newPost->excerpt,
            'status' => $newPost->status,
            'user_id' => $this->user->id,
            'meta_title' => $newPost->meta_title,
            'meta_description' => $newPost->meta_description,
            'featured' => $newPost->featured,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('posts', [
        'title' => $newPost->title,
        'slug' => $newPost->slug,
        'user_id' => $this->user->id,
    ]);
});

it('can edit a post', function () {
    $post = Post::factory()->create(['user_id' => $this->user->id]);
    $newTitle = 'Updated Title';

    Livewire::test(EditPost::class, ['record' => $post->getRouteKey()])
        ->fillForm([
            'title' => $newTitle,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($post->fresh()->title)->toBe($newTitle);
});

it('can view a post', function () {
    $post = Post::factory()->create(['user_id' => $this->user->id]);

    Livewire::test(ViewPost::class, ['record' => $post->getRouteKey()])
        ->assertFormSet([
            'title' => $post->title,
            'content' => $post->content,
        ]);
});

it('can delete a post', function () {
    $post = Post::factory()->create(['user_id' => $this->user->id]);

    Livewire::test(ListPosts::class)
        ->callTableAction('delete', $post);

    $this->assertDatabaseMissing('posts', [
        'id' => $post->id,
    ]);
});

it('can publish a post', function () {
    $post = Post::factory()->draft()->create(['user_id' => $this->user->id]);

    Livewire::test(ListPosts::class)
        ->callTableAction('publish', $post);

    expect($post->fresh()->status)->toBe('published');
    expect($post->fresh()->published_at)->not->toBeNull();
});

it('can unpublish a post', function () {
    $post = Post::factory()->published()->create(['user_id' => $this->user->id]);

    Livewire::test(ListPosts::class)
        ->callTableAction('unpublish', $post);

    expect($post->fresh()->status)->toBe('draft');
});

it('can archive a post', function () {
    $post = Post::factory()->published()->create(['user_id' => $this->user->id]);

    Livewire::test(ListPosts::class)
        ->callTableAction('archive', $post);

    expect($post->fresh()->status)->toBe('archived');
});

it('can feature a post', function () {
    $post = Post::factory()->create(['featured' => false, 'user_id' => $this->user->id]);

    Livewire::test(ListPosts::class)
        ->callTableAction('feature', $post);

    expect($post->fresh()->featured)->toBeTrue();
});

it('can unfeature a post', function () {
    $post = Post::factory()->featured()->create(['user_id' => $this->user->id]);

    Livewire::test(ListPosts::class)
        ->callTableAction('unfeature', $post);

    expect($post->fresh()->featured)->toBeFalse();
});

it('can filter posts by status', function () {
    Post::factory()->published()->create();
    Post::factory()->draft()->create();
    Post::factory()->archived()->create();

    Livewire::test(ListPosts::class)
        ->filterTable('status', 'published')
        ->assertCanSeeTableRecords(Post::where('status', 'published')->get())
        ->assertCanNotSeeTableRecords(Post::where('status', 'draft')->get());
});

it('can filter posts by featured status', function () {
    Post::factory()->featured()->create();
    Post::factory()->create(['featured' => false]);

    Livewire::test(ListPosts::class)
        ->filterTable('featured', true)
        ->assertCanSeeTableRecords(Post::where('featured', true)->get())
        ->assertCanNotSeeTableRecords(Post::where('featured', false)->get());
});

it('can filter posts by author', function () {
    $anotherUser = User::factory()->create();
    Post::factory()->create(['user_id' => $this->user->id]);
    Post::factory()->create(['user_id' => $anotherUser->id]);

    Livewire::test(ListPosts::class)
        ->filterTable('user_id', $this->user->id)
        ->assertCanSeeTableRecords(Post::where('user_id', $this->user->id)->get())
        ->assertCanNotSeeTableRecords(Post::where('user_id', $anotherUser->id)->get());
});

it('can filter posts by published date range', function () {
    $oldPost = Post::factory()->create(['published_at' => now()->subYear()]);
    $recentPost = Post::factory()->create(['published_at' => now()->subMonth()]);

    Livewire::test(ListPosts::class)
        ->filterTable('published_at', [
            'published_from' => now()->subMonths(2)->format('Y-m-d'),
            'published_until' => now()->format('Y-m-d'),
        ])
        ->assertCanSeeTableRecords([$recentPost])
        ->assertCanNotSeeTableRecords([$oldPost]);
});

it('can search posts by title', function () {
    $post1 = Post::factory()->create(['title' => 'Unique Title']);
    $post2 = Post::factory()->create(['title' => 'Another Title']);

    Livewire::test(ListPosts::class)
        ->searchTable('Unique')
        ->assertCanSeeTableRecords([$post1])
        ->assertCanNotSeeTableRecords([$post2]);
});

it('can search posts by excerpt', function () {
    $post1 = Post::factory()->create(['excerpt' => 'Unique excerpt content']);
    $post2 = Post::factory()->create(['excerpt' => 'Another excerpt content']);

    Livewire::test(ListPosts::class)
        ->searchTable('Unique excerpt')
        ->assertCanSeeTableRecords([$post1])
        ->assertCanNotSeeTableRecords([$post2]);
});

it('can sort posts by title', function () {
    $post1 = Post::factory()->create(['title' => 'A Title']);
    $post2 = Post::factory()->create(['title' => 'Z Title']);

    Livewire::test(ListPosts::class)
        ->sortTable('title')
        ->assertCanSeeTableRecords([$post1, $post2], inOrder: true);
});

it('can sort posts by created date', function () {
    $post1 = Post::factory()->create(['created_at' => now()->subDay()]);
    $post2 = Post::factory()->create(['created_at' => now()]);

    Livewire::test(ListPosts::class)
        ->sortTable('created_at', 'desc')
        ->assertCanSeeTableRecords([$post2, $post1], inOrder: true);
});

it('can export posts', function () {
    Post::factory()->count(3)->create();

    Livewire::test(ListPosts::class)
        ->callTableBulkAction('export')
        ->assertFileDownloaded();
});

it('can bulk delete posts', function () {
    $posts = Post::factory()->count(3)->create();

    Livewire::test(ListPosts::class)
        ->callTableBulkAction('delete', $posts);

    foreach ($posts as $post) {
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }
});

it('validates required fields on create', function () {
    Livewire::test(CreatePost::class)
        ->fillForm([
            'title' => '',
            'slug' => '',
            'content' => '',
            'user_id' => '',
        ])
        ->call('create')
        ->assertHasFormErrors(['title', 'slug', 'content', 'user_id']);
});

it('validates unique slug', function () {
    $existingPost = Post::factory()->create();
    $newPost = Post::factory()->make(['slug' => $existingPost->slug]);

    Livewire::test(CreatePost::class)
        ->fillForm([
            'title' => $newPost->title,
            'slug' => $newPost->slug,
            'content' => $newPost->content,
            'user_id' => $this->user->id,
        ])
        ->call('create')
        ->assertHasFormErrors(['slug']);
});

it('validates slug format', function () {
    Livewire::test(CreatePost::class)
        ->fillForm([
            'title' => 'Test Title',
            'slug' => 'invalid slug with spaces!',
            'content' => 'Test content',
            'user_id' => $this->user->id,
        ])
        ->call('create')
        ->assertHasFormErrors(['slug']);
});

it('can access post resource pages', function () {
    $this->get(PostResource::getUrl('index'))->assertOk();
    $this->get(PostResource::getUrl('create'))->assertOk();
});

it('can access post resource pages with record', function () {
    $post = Post::factory()->create(['user_id' => $this->user->id]);

    $this->get(PostResource::getUrl('view', ['record' => $post]))->assertOk();
    $this->get(PostResource::getUrl('edit', ['record' => $post]))->assertOk();
});

