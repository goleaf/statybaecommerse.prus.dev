<?php declare(strict_types=1);

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->post = Post::factory()->create(['user_id' => $this->user->id]);
});

it('can create a post', function () {
    expect($this->post)->toBeInstanceOf(Post::class);
    expect($this->post->title)->toBeString();
    expect($this->post->user_id)->toBe($this->user->id);
});

it('belongs to a user', function () {
    expect($this->post->user)->toBeInstanceOf(User::class);
    expect($this->post->user->id)->toBe($this->user->id);
});

it('has correct fillable attributes', function () {
    $fillable = [
        'title',
        'title_translations',
        'slug',
        'content',
        'content_translations',
        'excerpt',
        'excerpt_translations',
        'status',
        'published_at',
        'user_id',
        'meta_title',
        'meta_title_translations',
        'meta_description',
        'meta_description_translations',
        'featured',
        'tags',
        'tags_translations',
        'views_count',
        'likes_count',
        'comments_count',
        'allow_comments',
        'is_pinned',
    ];

    expect($this->post->getFillable())->toBe($fillable);
});

it('has correct casts', function () {
    expect($this->post->published_at)->toBeInstanceOf(\Carbon\Carbon::class);
    expect($this->post->featured)->toBeBool();
    expect($this->post->title_translations)->toBeArray();
    expect($this->post->content_translations)->toBeArray();
    expect($this->post->excerpt_translations)->toBeArray();
    expect($this->post->meta_title_translations)->toBeArray();
    expect($this->post->meta_description_translations)->toBeArray();
    expect($this->post->tags_translations)->toBeArray();
    expect($this->post->allow_comments)->toBeBool();
    expect($this->post->is_pinned)->toBeBool();
});

it('can get translated title', function () {
    $this->post->title_translations = [
        'lt' => 'Lietuviškas pavadinimas',
        'en' => 'English title',
    ];
    $this->post->save();

    expect($this->post->getTranslatedTitle('lt'))->toBe('Lietuviškas pavadinimas');
    expect($this->post->getTranslatedTitle('en'))->toBe('English title');
    expect($this->post->getTranslatedTitle('de'))->toBe($this->post->title);  // fallback
});

it('can get translated content', function () {
    $this->post->content_translations = [
        'lt' => 'Lietuviškas turinys',
        'en' => 'English content',
    ];
    $this->post->save();

    expect($this->post->getTranslatedContent('lt'))->toBe('Lietuviškas turinys');
    expect($this->post->getTranslatedContent('en'))->toBe('English content');
});

it('can get translated excerpt', function () {
    $this->post->excerpt_translations = [
        'lt' => 'Lietuviška santrauka',
        'en' => 'English excerpt',
    ];
    $this->post->save();

    expect($this->post->getTranslatedExcerpt('lt'))->toBe('Lietuviška santrauka');
    expect($this->post->getTranslatedExcerpt('en'))->toBe('English excerpt');
});

it('can get translated meta title', function () {
    $this->post->meta_title_translations = [
        'lt' => 'Lietuviškas meta pavadinimas',
        'en' => 'English meta title',
    ];
    $this->post->save();

    expect($this->post->getTranslatedMetaTitle('lt'))->toBe('Lietuviškas meta pavadinimas');
    expect($this->post->getTranslatedMetaTitle('en'))->toBe('English meta title');
});

it('can get translated meta description', function () {
    $this->post->meta_description_translations = [
        'lt' => 'Lietuviškas meta aprašymas',
        'en' => 'English meta description',
    ];
    $this->post->save();

    expect($this->post->getTranslatedMetaDescription('lt'))->toBe('Lietuviškas meta aprašymas');
    expect($this->post->getTranslatedMetaDescription('en'))->toBe('English meta description');
});

it('can get translated tags', function () {
    $this->post->tags_translations = [
        'lt' => 'žymos, lietuva, naujienos',
        'en' => 'tags, lithuania, news',
    ];
    $this->post->save();

    expect($this->post->getTranslatedTags('lt'))->toBe('žymos, lietuva, naujienos');
    expect($this->post->getTranslatedTags('en'))->toBe('tags, lithuania, news');
});

it('can scope published posts', function () {
    Post::factory()->published()->create();
    Post::factory()->draft()->create();
    Post::factory()->archived()->create();

    $publishedPosts = Post::published()->get();

    expect($publishedPosts)->toHaveCount(1);
    expect($publishedPosts->first()->status)->toBe('published');
});

it('can scope featured posts', function () {
    Post::factory()->featured()->create();
    Post::factory()->create(['featured' => false]);

    $featuredPosts = Post::featured()->get();

    expect($featuredPosts)->toHaveCount(1);
    expect($featuredPosts->first()->featured)->toBeTrue();
});

it('can scope pinned posts', function () {
    Post::factory()->pinned()->create();
    Post::factory()->create(['is_pinned' => false]);

    $pinnedPosts = Post::pinned()->get();

    expect($pinnedPosts)->toHaveCount(1);
    expect($pinnedPosts->first()->is_pinned)->toBeTrue();
});

it('can scope posts by author', function () {
    $anotherUser = User::factory()->create();
    Post::factory()->create(['user_id' => $this->user->id]);
    Post::factory()->create(['user_id' => $anotherUser->id]);

    $userPosts = Post::byAuthor($this->user->id)->get();

    expect($userPosts)->toHaveCount(1);
    expect($userPosts->first()->user_id)->toBe($this->user->id);
});

it('can get formatted published at attribute', function () {
    $this->post->published_at = now();
    $this->post->save();

    expect($this->post->formatted_published_at)->toBeString();
    expect($this->post->formatted_published_at)->toMatch('/\d{2}\/\d{2}\/\d{4} \d{2}:\d{2}/');
});

it('can get status label attribute', function () {
    $this->post->status = 'published';
    $this->post->save();

    expect($this->post->status_label)->toBeString();
});

it('can increment views count', function () {
    $initialViews = $this->post->views_count;

    $this->post->increment('views_count');

    expect($this->post->fresh()->views_count)->toBe($initialViews + 1);
});

it('can increment likes count', function () {
    $initialLikes = $this->post->likes_count;

    $this->post->increment('likes_count');

    expect($this->post->fresh()->likes_count)->toBe($initialLikes + 1);
});

it('can increment comments count', function () {
    $initialComments = $this->post->comments_count;

    $this->post->increment('comments_count');

    expect($this->post->fresh()->comments_count)->toBe($initialComments + 1);
});

it('can toggle featured status', function () {
    $this->post->featured = false;
    $this->post->save();

    $this->post->update(['featured' => true]);

    expect($this->post->fresh()->featured)->toBeTrue();
});

it('can toggle pinned status', function () {
    $this->post->is_pinned = false;
    $this->post->save();

    $this->post->update(['is_pinned' => true]);

    expect($this->post->fresh()->is_pinned)->toBeTrue();
});

it('can toggle comments allowance', function () {
    $this->post->allow_comments = true;
    $this->post->save();

    $this->post->update(['allow_comments' => false]);

    expect($this->post->fresh()->allow_comments)->toBeFalse();
});

