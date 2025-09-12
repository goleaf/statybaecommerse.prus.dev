<?php declare(strict_types=1);

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->post = Post::factory()->published()->create(['user_id' => $this->user->id]);
});

it('can display posts index page', function () {
    $response = $this->get(route('posts.index'));

    $response
        ->assertOk()
        ->assertViewIs('posts.index')
        ->assertSee($this->post->getTranslatedTitle());
});

it('can display featured posts page', function () {
    $featuredPost = Post::factory()->featured()->published()->create(['user_id' => $this->user->id]);

    $response = $this->get(route('posts.featured'));

    $response
        ->assertOk()
        ->assertViewIs('posts.featured')
        ->assertSee($featuredPost->getTranslatedTitle());
});

it('can display post show page', function () {
    $response = $this->get(route('posts.show', $this->post));

    $response
        ->assertOk()
        ->assertViewIs('posts.show')
        ->assertSee($this->post->getTranslatedTitle())
        ->assertSee($this->post->getTranslatedContent());
});

it('can search posts', function () {
    $searchablePost = Post::factory()->published()->create([
        'title' => 'Unique Searchable Title',
        'user_id' => $this->user->id,
    ]);

    $response = $this->get(route('posts.search', ['q' => 'Unique Searchable']));

    $response
        ->assertOk()
        ->assertViewIs('posts.search')
        ->assertSee($searchablePost->getTranslatedTitle());
});

it('can filter posts by author', function () {
    $anotherUser = User::factory()->create();
    $anotherPost = Post::factory()->published()->create(['user_id' => $anotherUser->id]);

    $response = $this->get(route('posts.by-author', $this->user->id));

    $response
        ->assertOk()
        ->assertViewIs('posts.by-author')
        ->assertSee($this->post->getTranslatedTitle())
        ->assertDontSee($anotherPost->getTranslatedTitle());
});

it('increments views count when viewing post', function () {
    $initialViews = $this->post->views_count;

    $this->get(route('posts.show', $this->post));

    expect($this->post->fresh()->views_count)->toBe($initialViews + 1);
});

it('does not show draft posts on index', function () {
    $draftPost = Post::factory()->draft()->create(['user_id' => $this->user->id]);

    $response = $this->get(route('posts.index'));

    $response
        ->assertOk()
        ->assertDontSee($draftPost->getTranslatedTitle());
});

it('does not show archived posts on index', function () {
    $archivedPost = Post::factory()->archived()->create(['user_id' => $this->user->id]);

    $response = $this->get(route('posts.index'));

    $response
        ->assertOk()
        ->assertDontSee($archivedPost->getTranslatedTitle());
});

it('returns 404 for draft post show page', function () {
    $draftPost = Post::factory()->draft()->create(['user_id' => $this->user->id]);

    $response = $this->get(route('posts.show', $draftPost));

    $response->assertNotFound();
});

it('returns 404 for archived post show page', function () {
    $archivedPost = Post::factory()->archived()->create(['user_id' => $this->user->id]);

    $response = $this->get(route('posts.show', $archivedPost));

    $response->assertNotFound();
});

it('can filter posts by featured status', function () {
    $featuredPost = Post::factory()->featured()->published()->create(['user_id' => $this->user->id]);
    $regularPost = Post::factory()->published()->create(['featured' => false, 'user_id' => $this->user->id]);

    $response = $this->get(route('posts.index', ['featured' => true]));

    $response
        ->assertOk()
        ->assertSee($featuredPost->getTranslatedTitle())
        ->assertDontSee($regularPost->getTranslatedTitle());
});

it('can filter posts by author on index', function () {
    $anotherUser = User::factory()->create();
    $anotherPost = Post::factory()->published()->create(['user_id' => $anotherUser->id]);

    $response = $this->get(route('posts.index', ['author' => $this->user->id]));

    $response
        ->assertOk()
        ->assertSee($this->post->getTranslatedTitle())
        ->assertDontSee($anotherPost->getTranslatedTitle());
});

it('can search posts by title', function () {
    $searchablePost = Post::factory()->published()->create([
        'title' => 'Searchable Title',
        'user_id' => $this->user->id,
    ]);

    $response = $this->get(route('posts.index', ['search' => 'Searchable']));

    $response
        ->assertOk()
        ->assertSee($searchablePost->getTranslatedTitle());
});

it('can search posts by excerpt', function () {
    $searchablePost = Post::factory()->published()->create([
        'excerpt' => 'Searchable excerpt content',
        'user_id' => $this->user->id,
    ]);

    $response = $this->get(route('posts.index', ['search' => 'Searchable excerpt']));

    $response
        ->assertOk()
        ->assertSee($searchablePost->getTranslatedTitle());
});

it('can search posts by content', function () {
    $searchablePost = Post::factory()->published()->create([
        'content' => 'Searchable content text',
        'user_id' => $this->user->id,
    ]);

    $response = $this->get(route('posts.index', ['search' => 'Searchable content']));

    $response
        ->assertOk()
        ->assertSee($searchablePost->getTranslatedTitle());
});

it('shows related posts on post show page', function () {
    $relatedPost = Post::factory()->published()->create(['user_id' => $this->user->id]);

    $response = $this->get(route('posts.show', $this->post));

    $response
        ->assertOk()
        ->assertSee($relatedPost->getTranslatedTitle());
});

it('shows post meta information', function () {
    $post = Post::factory()->published()->create([
        'meta_title' => 'Custom Meta Title',
        'meta_description' => 'Custom meta description',
        'user_id' => $this->user->id,
    ]);

    $response = $this->get(route('posts.show', $post));

    $response
        ->assertOk()
        ->assertSee('Custom Meta Title', false)
        ->assertSee('Custom meta description', false);
});

it('shows post tags if available', function () {
    $post = Post::factory()->published()->create([
        'tags' => 'tag1, tag2, tag3',
        'user_id' => $this->user->id,
    ]);

    $response = $this->get(route('posts.show', $post));

    $response
        ->assertOk()
        ->assertSee('tag1')
        ->assertSee('tag2')
        ->assertSee('tag3');
});

it('shows featured badge for featured posts', function () {
    $featuredPost = Post::factory()->featured()->published()->create(['user_id' => $this->user->id]);

    $response = $this->get(route('posts.show', $featuredPost));

    $response
        ->assertOk()
        ->assertSee(__('posts.fields.featured'));
});

it('shows post statistics', function () {
    $post = Post::factory()->published()->create([
        'views_count' => 100,
        'likes_count' => 25,
        'comments_count' => 10,
        'user_id' => $this->user->id,
    ]);

    $response = $this->get(route('posts.show', $post));

    $response
        ->assertOk()
        ->assertSee('100')
        ->assertSee('25')
        ->assertSee('10');
});

