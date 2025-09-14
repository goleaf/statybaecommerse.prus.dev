<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AttributeValue;
use App\Models\CollectionRule;
use App\Models\CouponUsage;
use App\Models\NewsCategory;
use App\Models\NewsComment;
use App\Models\NewsImage;
use App\Models\NewsTag;
use App\Models\User;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\ApprovedScope;
use App\Models\Scopes\EnabledScope;
use App\Models\Scopes\UserOwnedScope;
use App\Models\Scopes\VisibleScope;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class NewsGlobalScopesTest extends TestCase
{
    use RefreshDatabase;

    public function test_news_category_model_has_active_scope(): void
    {
        // Create test news categories
        $activeCategory = NewsCategory::factory()->create(['is_active' => true]);
        $inactiveCategory = NewsCategory::factory()->create(['is_active' => false]);

        // Test that only active categories are returned
        $categories = NewsCategory::all();
        
        $this->assertCount(1, $categories);
        $this->assertEquals($activeCategory->id, $categories->first()->id);

        // Test bypassing scopes
        $allCategories = NewsCategory::withoutGlobalScopes()->get();
        $this->assertCount(2, $allCategories);
    }

    public function test_news_tag_model_has_active_scope(): void
    {
        // Create test news tags
        $activeTag = NewsTag::factory()->create(['is_active' => true]);
        $inactiveTag = NewsTag::factory()->create(['is_active' => false]);

        // Test that only active tags are returned
        $tags = NewsTag::all();
        
        $this->assertCount(1, $tags);
        $this->assertEquals($activeTag->id, $tags->first()->id);

        // Test bypassing scopes
        $allTags = NewsTag::withoutGlobalScopes()->get();
        $this->assertCount(2, $allTags);
    }

    public function test_news_comment_model_has_multiple_scopes(): void
    {
        // Create test news comments
        $activeApprovedVisibleComment = NewsComment::factory()->create([
            'is_active' => true,
            'is_approved' => true,
            'is_visible' => true,
        ]);

        $inactiveComment = NewsComment::factory()->create([
            'is_active' => false,
            'is_approved' => true,
            'is_visible' => true,
        ]);

        $unapprovedComment = NewsComment::factory()->create([
            'is_active' => true,
            'is_approved' => false,
            'is_visible' => true,
        ]);

        $invisibleComment = NewsComment::factory()->create([
            'is_active' => true,
            'is_approved' => true,
            'is_visible' => false,
        ]);

        // Test that only active, approved, and visible comments are returned
        $comments = NewsComment::all();
        
        $this->assertCount(1, $comments);
        $this->assertEquals($activeApprovedVisibleComment->id, $comments->first()->id);

        // Test bypassing scopes
        $allComments = NewsComment::withoutGlobalScopes()->get();
        $this->assertCount(4, $allComments);
    }

    public function test_news_image_model_has_active_scope(): void
    {
        // Create test news images
        $activeImage = NewsImage::factory()->create(['is_active' => true]);
        $inactiveImage = NewsImage::factory()->create(['is_active' => false]);

        // Test that only active images are returned
        $images = NewsImage::all();
        
        $this->assertCount(1, $images);
        $this->assertEquals($activeImage->id, $images->first()->id);

        // Test bypassing scopes
        $allImages = NewsImage::withoutGlobalScopes()->get();
        $this->assertCount(2, $allImages);
    }

    public function test_attribute_value_model_has_multiple_scopes(): void
    {
        // Create test attribute values
        $activeEnabledValue = AttributeValue::factory()->create([
            'is_active' => true,
            'is_enabled' => true,
        ]);

        $inactiveValue = AttributeValue::factory()->create([
            'is_active' => false,
            'is_enabled' => true,
        ]);

        $disabledValue = AttributeValue::factory()->create([
            'is_active' => true,
            'is_enabled' => false,
        ]);

        // Test that only active and enabled values are returned
        $values = AttributeValue::all();
        
        $this->assertCount(1, $values);
        $this->assertEquals($activeEnabledValue->id, $values->first()->id);

        // Test bypassing scopes
        $allValues = AttributeValue::withoutGlobalScopes()->get();
        $this->assertCount(3, $allValues);
    }

    public function test_collection_rule_model_has_active_scope(): void
    {
        // Create test collection rules
        $activeRule = CollectionRule::factory()->create(['is_active' => true]);
        $inactiveRule = CollectionRule::factory()->create(['is_active' => false]);

        // Test that only active rules are returned
        $rules = CollectionRule::all();
        
        $this->assertCount(1, $rules);
        $this->assertEquals($activeRule->id, $rules->first()->id);

        // Test bypassing scopes
        $allRules = CollectionRule::withoutGlobalScopes()->get();
        $this->assertCount(2, $allRules);
    }

    public function test_coupon_usage_model_has_user_owned_scope(): void
    {
        // Create test users
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Create test coupon usages
        $user1Usage = CouponUsage::factory()->create(['user_id' => $user1->id]);
        $user2Usage = CouponUsage::factory()->create(['user_id' => $user2->id]);

        // Test that only current user's usages are returned
        $this->actingAs($user1);
        $usages = CouponUsage::all();
        
        $this->assertCount(1, $usages);
        $this->assertEquals($user1Usage->id, $usages->first()->id);

        // Test bypassing scopes
        $allUsages = CouponUsage::withoutGlobalScopes()->get();
        $this->assertCount(2, $allUsages);
    }

    public function test_global_scopes_can_be_combined_with_local_scopes(): void
    {
        // Create test data
        $user = User::factory()->create();
        $usage = CouponUsage::factory()->create(['user_id' => $user->id]);

        // Test that global scopes work with local scopes
        $this->actingAs($user);
        $usages = CouponUsage::where('discount_amount', '>', 0)->get();
        $this->assertCount(1, $usages); // User's usage with discount

        // Test bypassing global scopes with local scopes
        $allUsages = CouponUsage::withoutGlobalScopes()->where('user_id', $user->id)->get();
        $this->assertCount(1, $allUsages);
        $this->assertEquals($usage->id, $allUsages->first()->id);
    }

    public function test_global_scopes_are_applied_to_relationships(): void
    {
        // Create test data with relationships
        $user = User::factory()->create();
        $usage = CouponUsage::factory()->create(['user_id' => $user->id]);

        // Test that relationships also apply global scopes
        $this->actingAs($user);
        $userUsages = $user->couponUsages;
        $this->assertCount(1, $userUsages);
        $this->assertEquals($usage->id, $userUsages->first()->id);
    }

    public function test_user_owned_scope_works_without_authentication(): void
    {
        // Create test data
        $user = User::factory()->create();
        $usage = CouponUsage::factory()->create(['user_id' => $user->id]);

        // Test without authentication
        $usages = CouponUsage::all();
        $this->assertCount(0, $usages); // No usages returned without auth

        // Test with authentication
        $this->actingAs($user);
        $usages = CouponUsage::all();
        $this->assertCount(1, $usages);
        $this->assertEquals($usage->id, $usages->first()->id);
    }

    public function test_news_comment_scope_combinations(): void
    {
        // Test different combinations of comment scopes
        $comment1 = NewsComment::factory()->create([
            'is_active' => true,
            'is_approved' => true,
            'is_visible' => true,
        ]);

        $comment2 = NewsComment::factory()->create([
            'is_active' => false,
            'is_approved' => true,
            'is_visible' => true,
        ]);

        $comment3 = NewsComment::factory()->create([
            'is_active' => true,
            'is_approved' => false,
            'is_visible' => true,
        ]);

        $comment4 = NewsComment::factory()->create([
            'is_active' => true,
            'is_approved' => true,
            'is_visible' => false,
        ]);

        // Test bypassing specific scopes
        $activeComments = NewsComment::withoutGlobalScope(ApprovedScope::class)->withoutGlobalScope(VisibleScope::class)->get();
        $this->assertCount(2, $activeComments); // Only active comments

        $approvedComments = NewsComment::withoutGlobalScope(ActiveScope::class)->withoutGlobalScope(VisibleScope::class)->get();
        $this->assertCount(2, $approvedComments); // Only approved comments

        $visibleComments = NewsComment::withoutGlobalScope(ActiveScope::class)->withoutGlobalScope(ApprovedScope::class)->get();
        $this->assertCount(2, $visibleComments); // Only visible comments
    }
}
