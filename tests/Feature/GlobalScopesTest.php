<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Coupon;
use App\Models\Discount;
use App\Models\FeatureFlag;
use App\Models\News;
use App\Models\Post;
use App\Models\Product;
use App\Models\Review;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\ApprovedScope;
use App\Models\Scopes\EnabledScope;
use App\Models\Scopes\PublishedScope;
use App\Models\Scopes\VisibleScope;
use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class GlobalScopesTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_global_scopes_work(): void
    {
        // Create test products with different states
        $activeProduct = Product::factory()->create([
            'is_visible' => true,
            'is_active' => true,
            'published_at' => now()->subDay(),
        ]);

        $inactiveProduct = Product::factory()->create([
            'is_visible' => false,
            'is_active' => true,
            'published_at' => now()->subDay(),
        ]);

        $unpublishedProduct = Product::factory()->create([
            'is_visible' => true,
            'is_active' => true,
            'published_at' => now()->addDay(),
        ]);

        $inactiveUnpublishedProduct = Product::factory()->create([
            'is_visible' => false,
            'is_active' => false,
            'published_at' => now()->addDay(),
        ]);

        // Test that only active, visible, published products are returned
        $products = Product::all();
        $this->assertCount(1, $products);
        $this->assertEquals($activeProduct->id, $products->first()->id);

        // Test bypassing global scopes
        $allProducts = Product::withoutGlobalScopes()->get();
        $this->assertCount(4, $allProducts);

        // Test bypassing specific scope
        $visibleProducts = Product::withoutGlobalScope(VisibleScope::class)->get();
        $this->assertCount(2, $visibleProducts); // active + unpublished
    }

    public function test_category_global_scopes_work(): void
    {
        // Create test categories with different states
        $activeCategory = Category::factory()->create([
            'is_visible' => true,
            'is_enabled' => true,
        ]);

        $invisibleCategory = Category::factory()->create([
            'is_visible' => false,
            'is_enabled' => true,
        ]);

        $disabledCategory = Category::factory()->create([
            'is_visible' => true,
            'is_enabled' => false,
        ]);

        $inactiveCategory = Category::factory()->create([
            'is_visible' => false,
            'is_enabled' => false,
        ]);

        // Test that only active, enabled, visible categories are returned
        $categories = Category::all();
        $this->assertCount(1, $categories);
        $this->assertEquals($activeCategory->id, $categories->first()->id);

        // Test bypassing global scopes
        $allCategories = Category::withoutGlobalScopes()->get();
        $this->assertCount(4, $allCategories);
    }

    public function test_brand_global_scopes_work(): void
    {
        // Create test brands with different states
        $activeBrand = Brand::factory()->create([
            'is_enabled' => true,
        ]);

        $inactiveBrand = Brand::factory()->create([
            'is_enabled' => false,
        ]);

        // Test that only enabled brands are returned
        $brands = Brand::all();
        $this->assertCount(1, $brands);
        $this->assertEquals($activeBrand->id, $brands->first()->id);

        // Test bypassing global scopes
        $allBrands = Brand::withoutGlobalScopes()->get();
        $this->assertCount(2, $allBrands);
    }

    public function test_review_global_scopes_work(): void
    {
        // Create test reviews with different states
        $approvedReview = Review::factory()->create([
            'is_approved' => true,
        ]);

        $pendingReview = Review::factory()->create([
            'is_approved' => false,
        ]);

        // Test that only approved reviews are returned
        $reviews = Review::all();
        $this->assertCount(1, $reviews);
        $this->assertEquals($approvedReview->id, $reviews->first()->id);

        // Test bypassing global scopes
        $allReviews = Review::withoutGlobalScopes()->get();
        $this->assertCount(2, $allReviews);
    }

    public function test_news_global_scopes_work(): void
    {
        // Create test news with different states
        $publishedNews = News::factory()->create([
            'is_visible' => true,
            'published_at' => now()->subDay(),
        ]);

        $unpublishedNews = News::factory()->create([
            'is_visible' => true,
            'published_at' => now()->addDay(),
        ]);

        $invisibleNews = News::factory()->create([
            'is_visible' => false,
            'published_at' => now()->subDay(),
        ]);

        // Test that only visible, published news are returned
        $news = News::all();
        $this->assertCount(1, $news);
        $this->assertEquals($publishedNews->id, $news->first()->id);

        // Test bypassing global scopes
        $allNews = News::withoutGlobalScopes()->get();
        $this->assertCount(3, $allNews);
    }

    public function test_post_global_scopes_work(): void
    {
        // Create test posts with different states
        $publishedPost = Post::factory()->create([
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        $draftPost = Post::factory()->create([
            'status' => 'draft',
            'published_at' => now()->subDay(),
        ]);

        $scheduledPost = Post::factory()->create([
            'status' => 'published',
            'published_at' => now()->addDay(),
        ]);

        // Test that only published posts are returned
        $posts = Post::all();
        $this->assertCount(1, $posts);
        $this->assertEquals($publishedPost->id, $posts->first()->id);

        // Test bypassing global scopes
        $allPosts = Post::withoutGlobalScopes()->get();
        $this->assertCount(3, $allPosts);
    }

    public function test_user_global_scopes_work(): void
    {
        // Create test users with different states
        $activeUser = User::factory()->create([
            'is_active' => true,
        ]);

        $inactiveUser = User::factory()->create([
            'is_active' => false,
        ]);

        // Test that only active users are returned
        $users = User::all();
        $this->assertCount(1, $users);
        $this->assertEquals($activeUser->id, $users->first()->id);

        // Test bypassing global scopes
        $allUsers = User::withoutGlobalScopes()->get();
        $this->assertCount(2, $allUsers);
    }

    public function test_discount_global_scopes_work(): void
    {
        // Create test discounts with different states
        $activeDiscount = Discount::factory()->create([
            'is_active' => true,
            'is_enabled' => true,
        ]);

        $inactiveDiscount = Discount::factory()->create([
            'is_active' => false,
            'is_enabled' => true,
        ]);

        $disabledDiscount = Discount::factory()->create([
            'is_active' => true,
            'is_enabled' => false,
        ]);

        // Test that only active and enabled discounts are returned
        $discounts = Discount::all();
        $this->assertCount(1, $discounts);
        $this->assertEquals($activeDiscount->id, $discounts->first()->id);

        // Test bypassing global scopes
        $allDiscounts = Discount::withoutGlobalScopes()->get();
        $this->assertCount(3, $allDiscounts);
    }

    public function test_coupon_global_scopes_work(): void
    {
        // Create test coupons with different states
        $activeCoupon = Coupon::factory()->create([
            'is_active' => true,
        ]);

        $inactiveCoupon = Coupon::factory()->create([
            'is_active' => false,
        ]);

        // Test that only active coupons are returned
        $coupons = Coupon::all();
        $this->assertCount(1, $coupons);
        $this->assertEquals($activeCoupon->id, $coupons->first()->id);

        // Test bypassing global scopes
        $allCoupons = Coupon::withoutGlobalScopes()->get();
        $this->assertCount(2, $allCoupons);
    }

    public function test_feature_flag_global_scopes_work(): void
    {
        // Create test feature flags with different states
        $activeFlag = FeatureFlag::factory()->create([
            'is_active' => true,
            'is_enabled' => true,
        ]);

        $inactiveFlag = FeatureFlag::factory()->create([
            'is_active' => false,
            'is_enabled' => true,
        ]);

        $disabledFlag = FeatureFlag::factory()->create([
            'is_active' => true,
            'is_enabled' => false,
        ]);

        // Test that only active and enabled feature flags are returned
        $flags = FeatureFlag::all();
        $this->assertCount(1, $flags);
        $this->assertEquals($activeFlag->id, $flags->first()->id);

        // Test bypassing global scopes
        $allFlags = FeatureFlag::withoutGlobalScopes()->get();
        $this->assertCount(3, $allFlags);
    }

    public function test_subscriber_global_scopes_work(): void
    {
        // Create test subscribers with different states
        $activeSubscriber = Subscriber::factory()->create([
            'status' => 'active',
        ]);

        $inactiveSubscriber = Subscriber::factory()->create([
            'status' => 'inactive',
        ]);

        // Test that only active subscribers are returned
        $subscribers = Subscriber::all();
        $this->assertCount(1, $subscribers);
        $this->assertEquals($activeSubscriber->id, $subscribers->first()->id);

        // Test bypassing global scopes
        $allSubscribers = Subscriber::withoutGlobalScopes()->get();
        $this->assertCount(2, $allSubscribers);
    }

    public function test_collection_global_scopes_work(): void
    {
        // Create test collections with different states
        $activeCollection = Collection::factory()->create([
            'is_active' => true,
            'is_visible' => true,
        ]);

        $inactiveCollection = Collection::factory()->create([
            'is_active' => false,
            'is_visible' => true,
        ]);

        $invisibleCollection = Collection::factory()->create([
            'is_active' => true,
            'is_visible' => false,
        ]);

        // Test that only active and visible collections are returned
        $collections = Collection::all();
        $this->assertCount(1, $collections);
        $this->assertEquals($activeCollection->id, $collections->first()->id);

        // Test bypassing global scopes
        $allCollections = Collection::withoutGlobalScopes()->get();
        $this->assertCount(3, $allCollections);
    }

    public function test_global_scopes_work_with_relationships(): void
    {
        // Create a category with products
        $category = Category::factory()->create([
            'is_visible' => true,
            'is_enabled' => true,
        ]);

        $activeProduct = Product::factory()->create([
            'is_visible' => true,
            'is_active' => true,
            'published_at' => now()->subDay(),
        ]);

        $inactiveProduct = Product::factory()->create([
            'is_visible' => false,
            'is_active' => true,
            'published_at' => now()->subDay(),
        ]);

        // Attach products to category
        $category->products()->attach([$activeProduct->id, $inactiveProduct->id]);

        // Test that relationships also respect global scopes
        $categoryWithProducts = Category::with('products')->find($category->id);
        $this->assertCount(1, $categoryWithProducts->products);
        $this->assertEquals($activeProduct->id, $categoryWithProducts->products->first()->id);
    }

    public function test_global_scopes_work_with_local_scopes(): void
    {
        // Create products with different states
        $featuredProduct = Product::factory()->create([
            'is_visible' => true,
            'is_active' => true,
            'is_featured' => true,
            'published_at' => now()->subDay(),
        ]);

        $regularProduct = Product::factory()->create([
            'is_visible' => true,
            'is_active' => true,
            'is_featured' => false,
            'published_at' => now()->subDay(),
        ]);

        $inactiveFeaturedProduct = Product::factory()->create([
            'is_visible' => false,
            'is_active' => true,
            'is_featured' => true,
            'published_at' => now()->subDay(),
        ]);

        // Test that global scopes work with local scopes
        $featuredProducts = Product::featured()->get();
        $this->assertCount(1, $featuredProducts);
        $this->assertEquals($featuredProduct->id, $featuredProducts->first()->id);
    }
}
