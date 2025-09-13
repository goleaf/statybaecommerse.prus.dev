<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PostTest extends TestCase
{
    use RefreshDatabase;

    private function createTestUser(): User
    {
        return User::factory()->create([
            'name' => 'Test Author',
            'email' => 'author@example.com',
        ]);
    }

    public function test_post_can_be_created(): void
    {
        $user = $this->createTestUser();

        $post = Post::factory()->create([
            'title' => 'Test Post',
            'slug' => 'test-post',
            'content' => 'This is a test post content.',
            'excerpt' => 'This is a test excerpt.',
            'status' => 'published',
            'featured' => true,
            'user_id' => $user->id,
            'published_at' => now(),
        ]);

        $this->assertInstanceOf(Post::class, $post);
        $this->assertEquals('Test Post', $post->title);
        $this->assertEquals('test-post', $post->slug);
        $this->assertEquals('This is a test post content.', $post->content);
        $this->assertEquals('This is a test excerpt.', $post->excerpt);
        $this->assertEquals('published', $post->status);
        $this->assertTrue($post->featured);
        $this->assertEquals($user->id, $post->user_id);
    }

    public function test_post_translation_methods(): void
    {
        $user = $this->createTestUser();
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'title' => 'Original Title',
            'content' => 'Original Content',
            'excerpt' => 'Original Excerpt',
        ]);
        
        // Test translation methods
        $this->assertEquals('Original Title', $post->getTranslatedTitle());
        $this->assertEquals('Original Content', $post->getTranslatedContent());
        $this->assertEquals('Original Excerpt', $post->getTranslatedExcerpt());
        
        // Test with translation
        $post->updateTranslation('en', [
            'title' => 'English Title',
            'content' => 'English Content',
            'excerpt' => 'English Excerpt',
        ]);
        
        $this->assertEquals('English Title', $post->getTranslatedTitle('en'));
        $this->assertEquals('English Content', $post->getTranslatedContent('en'));
        $this->assertEquals('English Excerpt', $post->getTranslatedExcerpt('en'));
    }

    public function test_post_scopes(): void
    {
        $user = $this->createTestUser();
        
        // Clear any existing posts first
        Post::query()->delete();

        // Create test posts with specific attributes
        $publishedPost = Post::factory()->create([
            'user_id' => $user->id,
            'status' => 'published',
            'featured' => false,
        ]);
        $draftPost = Post::factory()->create([
            'user_id' => $user->id,
            'status' => 'draft',
        ]);
        $featuredPost = Post::factory()->create([
            'user_id' => $user->id,
            'featured' => true,
        ]);
        $pinnedPost = Post::factory()->create([
            'user_id' => $user->id,
            'is_pinned' => true,
        ]);

        // Test published scope
        $publishedPosts = Post::published()->get();
        $this->assertCount(1, $publishedPosts);
        $this->assertEquals($publishedPost->id, $publishedPosts->first()->id);

        // Test featured scope
        $featuredPosts = Post::featured()->get();
        $this->assertCount(1, $featuredPosts);
        $this->assertEquals($featuredPost->id, $featuredPosts->first()->id);

        // Test pinned scope
        $pinnedPosts = Post::pinned()->get();
        $this->assertCount(1, $pinnedPosts);
        $this->assertEquals($pinnedPost->id, $pinnedPosts->first()->id);

        // Test by author scope
        $authorPosts = Post::byAuthor($user->id)->get();
        $this->assertCount(4, $authorPosts);
    }

    public function test_post_helper_methods(): void
    {
        $user = $this->createTestUser();
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'title' => 'Test Post',
            'content' => 'This is a test post with multiple words to test word count functionality.',
            'status' => 'published',
            'featured' => true,
            'views_count' => 100,
            'likes_count' => 10,
            'comments_count' => 5,
        ]);

        // Test info methods
        $postInfo = $post->getPostInfo();
        $this->assertArrayHasKey('id', $postInfo);
        $this->assertArrayHasKey('title', $postInfo);
        $this->assertArrayHasKey('status', $postInfo);

        $seoInfo = $post->getSeoInfo();
        $this->assertArrayHasKey('meta_title', $seoInfo);
        $this->assertArrayHasKey('canonical_url', $seoInfo);

        $contentInfo = $post->getContentInfo();
        $this->assertArrayHasKey('word_count', $contentInfo);
        $this->assertArrayHasKey('reading_time', $contentInfo);

        $statusInfo = $post->getStatusInfo();
        $this->assertArrayHasKey('status', $statusInfo);
        $this->assertArrayHasKey('is_published', $statusInfo);

        $engagementInfo = $post->getEngagementInfo();
        $this->assertArrayHasKey('views_count', $engagementInfo);
        $this->assertArrayHasKey('engagement_rate', $engagementInfo);

        $businessInfo = $post->getBusinessInfo();
        $this->assertArrayHasKey('author', $businessInfo);
        $this->assertArrayHasKey('days_since_created', $businessInfo);

        $completeInfo = $post->getCompleteInfo();
        $this->assertArrayHasKey('translations', $completeInfo);
        $this->assertArrayHasKey('has_translations', $completeInfo);
    }

    public function test_post_status_methods(): void
    {
        $user = $this->createTestUser();
        
        // Test published post
        $publishedPost = Post::factory()->create([
            'user_id' => $user->id,
            'status' => 'published',
        ]);
        $this->assertTrue($publishedPost->isPublished());
        $this->assertFalse($publishedPost->isDraft());
        $this->assertFalse($publishedPost->isArchived());
        $this->assertEquals('success', $publishedPost->getStatusColor());

        // Test draft post
        $draftPost = Post::factory()->create([
            'user_id' => $user->id,
            'status' => 'draft',
        ]);
        $this->assertFalse($draftPost->isPublished());
        $this->assertTrue($draftPost->isDraft());
        $this->assertFalse($draftPost->isArchived());
        $this->assertEquals('warning', $draftPost->getStatusColor());

        // Test archived post
        $archivedPost = Post::factory()->create([
            'user_id' => $user->id,
            'status' => 'archived',
        ]);
        $this->assertFalse($archivedPost->isPublished());
        $this->assertFalse($archivedPost->isDraft());
        $this->assertTrue($archivedPost->isArchived());
        $this->assertEquals('danger', $archivedPost->getStatusColor());
    }

    public function test_post_content_methods(): void
    {
        $user = $this->createTestUser();
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'content' => 'This is a test post with exactly ten words to test word count functionality.',
        ]);

        // Test word count
        $this->assertEquals(15, $post->getWordCount());

        // Test reading time
        $readingTime = $post->getReadingTime();
        $this->assertGreaterThanOrEqual(1, $readingTime);
        $this->assertLessThanOrEqual(5, $readingTime); // Should be around 1 minute for 15 words
    }

    public function test_post_engagement_methods(): void
    {
        $user = $this->createTestUser();
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'views_count' => 100,
            'likes_count' => 10,
            'comments_count' => 5,
        ]);

        // Test engagement rate
        $engagementRate = $post->getEngagementRate();
        $this->assertEquals(15.0, $engagementRate); // (10 + 5) / 100 * 100 = 15%

        // Test popularity score
        $popularityScore = $post->getPopularityScore();
        $this->assertEquals(135, $popularityScore); // (100 * 1) + (10 * 2) + (5 * 3) = 135
    }

    public function test_post_relations(): void
    {
        $user = $this->createTestUser();
        $post = Post::factory()->create(['user_id' => $user->id]);

        // Test user relation
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $post->user());
        $this->assertEquals($user->id, $post->user->id);
    }

    public function test_post_translation_management(): void
    {
        $user = $this->createTestUser();
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'title' => 'Original Title',
            'content' => 'Original Content',
        ]);

        // Test available locales (should be empty initially)
        $this->assertEmpty($post->getAvailableLocales());

        // Test has translation for
        $this->assertFalse($post->hasTranslationFor('en'));

        // Test get or create translation
        $translation = $post->getOrCreateTranslation('en');
        $this->assertInstanceOf(\App\Models\Translations\PostTranslation::class, $translation);
        $this->assertEquals('en', $translation->locale);

        // Test update translation
        $this->assertTrue($post->updateTranslation('en', [
            'title' => 'English Title',
            'content' => 'English Content',
        ]));

        // Test available locales now includes 'en'
        $this->assertContains('en', $post->getAvailableLocales());
        $this->assertTrue($post->hasTranslationFor('en'));
    }

    public function test_post_full_display_name(): void
    {
        $user = $this->createTestUser();
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'title' => 'Test Post',
            'status' => 'published',
        ]);

        $displayName = $post->getFullDisplayName();
        $this->assertStringContains('Test Post', $displayName);
        $this->assertStringContains('Published', $displayName);
    }

    public function test_post_additional_scopes(): void
    {
        $user = $this->createTestUser();
        
        // Clear any existing posts first
        Post::query()->delete();

        // Create test posts
        $recentPost = Post::factory()->create([
            'user_id' => $user->id,
            'created_at' => now()->subDays(15),
            'views_count' => 150,
            'likes_count' => 15,
            'comments_count' => 8,
        ]);
        $oldPost = Post::factory()->create([
            'user_id' => $user->id,
            'created_at' => now()->subDays(45),
            'views_count' => 50,
        ]);
        $popularPost = Post::factory()->create([
            'user_id' => $user->id,
            'views_count' => 200,
        ]);

        // Test recent scope
        $recentPosts = Post::recent(30)->get();
        $this->assertCount(1, $recentPosts);
        $this->assertEquals($recentPost->id, $recentPosts->first()->id);

        // Test popular scope
        $popularPosts = Post::popular(100)->get();
        $this->assertCount(2, $popularPosts); // recentPost and popularPost

        // Test with high engagement scope
        $highEngagementPosts = Post::withHighEngagement(5.0)->get();
        $this->assertCount(1, $highEngagementPosts);
        $this->assertEquals($recentPost->id, $highEngagementPosts->first()->id);
    }

    public function test_post_media_methods(): void
    {
        $user = $this->createTestUser();
        $post = Post::factory()->create(['user_id' => $user->id]);

        // Test media methods (without actual media files)
        $this->assertFalse($post->hasFeaturedImage());
        $this->assertEquals(0, $post->getGalleryCount());
        $this->assertEquals(0, $post->getMediaCount());
    }

    public function test_post_days_methods(): void
    {
        $user = $this->createTestUser();
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'created_at' => now()->subDays(10),
            'published_at' => now()->subDays(5),
        ]);

        // Test days since created
        $this->assertEquals(10, $post->getDaysSinceCreated());

        // Test days since published
        $this->assertEquals(5, $post->getDaysSincePublished());
    }
}
