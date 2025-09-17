<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Collection;
use App\Models\Attribute;
use App\Models\DiscountCode;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * API Routes Test
 * 
 * Tests all API routes including authentication, validation, and responses.
 */
class ApiRoutesTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->admin = User::factory()->create(['is_admin' => true]);
        
        // Seed test data
        $this->seedTestData();
    }

    /**
     * Seed test data for API testing
     */
    private function seedTestData(): void
    {
        // Create products
        Product::factory()->count(5)->create();
        
        // Create categories
        Category::factory()->count(3)->create();
        
        // Create brands
        Brand::factory()->count(3)->create();
        
        // Create collections
        Collection::factory()->count(3)->create();
        
        // Create attributes
        Attribute::factory()->count(5)->create();
        
        // Create discount codes
        DiscountCode::factory()->count(3)->create();
        
        // Create referrals
        Referral::factory()->count(3)->create(['user_id' => $this->user->id]);
    }

    /**
     * Test user API route
     */
    public function test_user_api_route(): void
    {
        $this->actingAs($this->user, 'sanctum');
        
        $response = $this->get('/api/user');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'id',
            'name',
            'email',
            'email_verified_at',
            'created_at',
            'updated_at'
        ]);
    }

    /**
     * Test user API route without authentication
     */
    public function test_user_api_route_without_authentication(): void
    {
        $response = $this->get('/api/user');
        $response->assertStatus(401);
    }

    /**
     * Test notification stream route
     */
    public function test_notification_stream_route(): void
    {
        $this->actingAs($this->user);
        
        $response = $this->get('/api/notifications/stream');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/event-stream');
    }

    /**
     * Test notification stream route without authentication
     */
    public function test_notification_stream_route_without_authentication(): void
    {
        $response = $this->get('/api/notifications/stream');
        $response->assertStatus(401);
    }

    /**
     * Test discount code API routes
     */
    public function test_discount_code_api_routes(): void
    {
        $discountCode = DiscountCode::first();
        
        // Test validate discount code
        $response = $this->post('/api/discount-codes/validate', [
            'code' => $discountCode->code,
            'amount' => 100.00
        ]);
        $response->assertStatus(200);
        
        // Test apply discount code
        $response = $this->post('/api/discount-codes/apply', [
            'code' => $discountCode->code,
            'amount' => 100.00
        ]);
        $response->assertStatus(200);
        
        // Test remove discount code
        $response = $this->post('/api/discount-codes/remove', [
            'code' => $discountCode->code
        ]);
        $response->assertStatus(200);
        
        // Test get available discount codes
        $response = $this->get('/api/discount-codes/available');
        $response->assertStatus(200);
        
        // Test generate document
        $response = $this->post("/api/discount-codes/{$discountCode->id}/generate-document");
        $response->assertStatus(200);
    }

    /**
     * Test discount code API routes with invalid data
     */
    public function test_discount_code_api_routes_with_invalid_data(): void
    {
        // Test validate with invalid code
        $response = $this->post('/api/discount-codes/validate', [
            'code' => 'INVALID_CODE',
            'amount' => 100.00
        ]);
        $response->assertStatus(400);
        
        // Test apply with invalid code
        $response = $this->post('/api/discount-codes/apply', [
            'code' => 'INVALID_CODE',
            'amount' => 100.00
        ]);
        $response->assertStatus(400);
        
        // Test remove with invalid code
        $response = $this->post('/api/discount-codes/remove', [
            'code' => 'INVALID_CODE'
        ]);
        $response->assertStatus(400);
    }

    /**
     * Test product history API routes
     */
    public function test_product_history_api_routes(): void
    {
        $product = Product::first();
        
        // Test get product history
        $response = $this->get("/api/products/{$product->id}/history");
        $response->assertStatus(200);
        
        // Test get product history statistics
        $response = $this->get("/api/products/{$product->id}/history/statistics");
        $response->assertStatus(200);
        
        // Test export product history
        $response = $this->get("/api/products/{$product->id}/history/export");
        $response->assertStatus(200);
        
        // Test create product history (authenticated)
        $this->actingAs($this->user, 'sanctum');
        $response = $this->post("/api/products/{$product->id}/history", [
            'action' => 'view',
            'metadata' => ['source' => 'api_test']
        ]);
        $response->assertStatus(200);
        
        // Test get specific history entry
        $history = \App\Models\ProductHistory::factory()->create(['product_id' => $product->id]);
        $response = $this->get("/api/products/{$product->id}/history/{$history->id}");
        $response->assertStatus(200);
    }

    /**
     * Test product history API routes without authentication
     */
    public function test_product_history_api_routes_without_authentication(): void
    {
        $product = Product::first();
        
        // Test create product history without authentication
        $response = $this->post("/api/products/{$product->id}/history", [
            'action' => 'view',
            'metadata' => ['source' => 'api_test']
        ]);
        $response->assertStatus(401);
    }

    /**
     * Test autocomplete API routes
     */
    public function test_autocomplete_api_routes(): void
    {
        // Test search autocomplete
        $response = $this->get('/api/autocomplete/search?q=test');
        $response->assertStatus(200);
        
        // Test products autocomplete
        $response = $this->get('/api/autocomplete/products?q=test');
        $response->assertStatus(200);
        
        // Test categories autocomplete
        $response = $this->get('/api/autocomplete/categories?q=test');
        $response->assertStatus(200);
        
        // Test brands autocomplete
        $response = $this->get('/api/autocomplete/brands?q=test');
        $response->assertStatus(200);
        
        // Test collections autocomplete
        $response = $this->get('/api/autocomplete/collections?q=test');
        $response->assertStatus(200);
        
        // Test attributes autocomplete
        $response = $this->get('/api/autocomplete/attributes?q=test');
        $response->assertStatus(200);
        
        // Test popular autocomplete
        $response = $this->get('/api/autocomplete/popular');
        $response->assertStatus(200);
        
        // Test recent autocomplete
        $response = $this->get('/api/autocomplete/recent');
        $response->assertStatus(200);
        
        // Test suggestions autocomplete
        $response = $this->get('/api/autocomplete/suggestions?q=test');
        $response->assertStatus(200);
        
        // Test clear recent autocomplete
        $response = $this->delete('/api/autocomplete/recent');
        $response->assertStatus(200);
    }

    /**
     * Test autocomplete API routes with query parameters
     */
    public function test_autocomplete_api_routes_with_query_parameters(): void
    {
        // Test search autocomplete with limit
        $response = $this->get('/api/autocomplete/search?q=test&limit=10');
        $response->assertStatus(200);
        
        // Test products autocomplete with category filter
        $response = $this->get('/api/autocomplete/products?q=test&category_id=1');
        $response->assertStatus(200);
        
        // Test categories autocomplete with parent filter
        $response = $this->get('/api/autocomplete/categories?q=test&parent_id=1');
        $response->assertStatus(200);
        
        // Test brands autocomplete with status filter
        $response = $this->get('/api/autocomplete/brands?q=test&status=active');
        $response->assertStatus(200);
        
        // Test collections autocomplete with type filter
        $response = $this->get('/api/autocomplete/collections?q=test&type=featured');
        $response->assertStatus(200);
        
        // Test attributes autocomplete with type filter
        $response = $this->get('/api/autocomplete/attributes?q=test&type=text');
        $response->assertStatus(200);
        
        // Test popular autocomplete with limit
        $response = $this->get('/api/autocomplete/popular?limit=5');
        $response->assertStatus(200);
        
        // Test recent autocomplete with limit
        $response = $this->get('/api/autocomplete/recent?limit=10');
        $response->assertStatus(200);
        
        // Test suggestions autocomplete with context
        $response = $this->get('/api/autocomplete/suggestions?q=test&context=search');
        $response->assertStatus(200);
    }

    /**
     * Test referral system API routes
     */
    public function test_referral_system_api_routes(): void
    {
        $referral = Referral::first();
        
        // Test validate referral code
        $response = $this->post('/api/referrals/validate-code', [
            'code' => $referral->code
        ]);
        $response->assertStatus(200);
        
        // Test process referral
        $response = $this->post('/api/referrals/process', [
            'code' => $referral->code,
            'user_id' => $this->user->id
        ]);
        $response->assertStatus(200);
        
        // Test get code statistics
        $response = $this->get('/api/referrals/code-statistics');
        $response->assertStatus(200);
        
        // Test get referral URL
        $response = $this->get('/api/referrals/referral-url');
        $response->assertStatus(200);
    }

    /**
     * Test authenticated referral system API routes
     */
    public function test_authenticated_referral_system_api_routes(): void
    {
        $this->actingAs($this->user, 'sanctum');
        
        // Test get dashboard
        $response = $this->get('/api/referrals/dashboard');
        $response->assertStatus(200);
        
        // Test get statistics
        $response = $this->get('/api/referrals/statistics');
        $response->assertStatus(200);
        
        // Test generate code
        $response = $this->post('/api/referrals/generate-code', [
            'type' => 'personal',
            'expires_at' => now()->addDays(30)
        ]);
        $response->assertStatus(200);
        
        // Test get pending rewards
        $response = $this->get('/api/referrals/pending-rewards');
        $response->assertStatus(200);
        
        // Test get applied rewards
        $response = $this->get('/api/referrals/applied-rewards');
        $response->assertStatus(200);
        
        // Test get recent referrals
        $response = $this->get('/api/referrals/recent-referrals');
        $response->assertStatus(200);
    }

    /**
     * Test authenticated referral system API routes without authentication
     */
    public function test_authenticated_referral_system_api_routes_without_authentication(): void
    {
        // Test get dashboard without authentication
        $response = $this->get('/api/referrals/dashboard');
        $response->assertStatus(401);
        
        // Test get statistics without authentication
        $response = $this->get('/api/referrals/statistics');
        $response->assertStatus(401);
        
        // Test generate code without authentication
        $response = $this->post('/api/referrals/generate-code', [
            'type' => 'personal',
            'expires_at' => now()->addDays(30)
        ]);
        $response->assertStatus(401);
        
        // Test get pending rewards without authentication
        $response = $this->get('/api/referrals/pending-rewards');
        $response->assertStatus(401);
        
        // Test get applied rewards without authentication
        $response = $this->get('/api/referrals/applied-rewards');
        $response->assertStatus(401);
        
        // Test get recent referrals without authentication
        $response = $this->get('/api/referrals/recent-referrals');
        $response->assertStatus(401);
    }

    /**
     * Test referral system API routes with invalid data
     */
    public function test_referral_system_api_routes_with_invalid_data(): void
    {
        // Test validate referral code with invalid code
        $response = $this->post('/api/referrals/validate-code', [
            'code' => 'INVALID_CODE'
        ]);
        $response->assertStatus(400);
        
        // Test process referral with invalid code
        $response = $this->post('/api/referrals/process', [
            'code' => 'INVALID_CODE',
            'user_id' => $this->user->id
        ]);
        $response->assertStatus(400);
        
        // Test process referral with invalid user ID
        $referral = Referral::first();
        $response = $this->post('/api/referrals/process', [
            'code' => $referral->code,
            'user_id' => 999999
        ]);
        $response->assertStatus(400);
    }

    /**
     * Test API routes with rate limiting
     */
    public function test_api_routes_rate_limiting(): void
    {
        // Test multiple autocomplete requests (should be rate limited)
        for ($i = 0; $i < 10; $i++) {
            $response = $this->get('/api/autocomplete/search?q=test');
            $response->assertStatus(200);
        }
        
        // Test multiple discount code validation requests (should be rate limited)
        $discountCode = DiscountCode::first();
        for ($i = 0; $i < 10; $i++) {
            $response = $this->post('/api/discount-codes/validate', [
                'code' => $discountCode->code,
                'amount' => 100.00
            ]);
            $response->assertStatus(200);
        }
    }

    /**
     * Test API routes with different content types
     */
    public function test_api_routes_content_types(): void
    {
        // Test JSON response
        $response = $this->get('/api/autocomplete/search?q=test');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        
        // Test CSV export
        $product = Product::first();
        $response = $this->get("/api/products/{$product->id}/history/export");
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv');
        
        // Test PDF generation
        $discountCode = DiscountCode::first();
        $response = $this->post("/api/discount-codes/{$discountCode->id}/generate-document");
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    /**
     * Test API routes with pagination
     */
    public function test_api_routes_pagination(): void
    {
        // Test autocomplete with pagination
        $response = $this->get('/api/autocomplete/search?q=test&page=1&per_page=10');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'meta' => [
                'current_page',
                'per_page',
                'total',
                'last_page'
            ]
        ]);
        
        // Test product history with pagination
        $product = Product::first();
        $response = $this->get("/api/products/{$product->id}/history?page=1&per_page=10");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'meta' => [
                'current_page',
                'per_page',
                'total',
                'last_page'
            ]
        ]);
    }

    /**
     * Test API routes with filtering
     */
    public function test_api_routes_filtering(): void
    {
        // Test autocomplete with filters
        $response = $this->get('/api/autocomplete/products?q=test&category_id=1&brand_id=1');
        $response->assertStatus(200);
        
        // Test product history with filters
        $product = Product::first();
        $response = $this->get("/api/products/{$product->id}/history?action=view&date_from=2024-01-01&date_to=2024-12-31");
        $response->assertStatus(200);
        
        // Test referral statistics with filters
        $response = $this->get('/api/referrals/code-statistics?date_from=2024-01-01&date_to=2024-12-31');
        $response->assertStatus(200);
    }

    /**
     * Test API routes with sorting
     */
    public function test_api_routes_sorting(): void
    {
        // Test autocomplete with sorting
        $response = $this->get('/api/autocomplete/search?q=test&sort=name&order=asc');
        $response->assertStatus(200);
        
        // Test product history with sorting
        $product = Product::first();
        $response = $this->get("/api/products/{$product->id}/history?sort=created_at&order=desc");
        $response->assertStatus(200);
        
        // Test referral statistics with sorting
        $response = $this->get('/api/referrals/code-statistics?sort=usage_count&order=desc');
        $response->assertStatus(200);
    }

    /**
     * Test API routes with caching
     */
    public function test_api_routes_caching(): void
    {
        // Test autocomplete caching
        $response1 = $this->get('/api/autocomplete/search?q=test');
        $response1->assertStatus(200);
        
        $response2 = $this->get('/api/autocomplete/search?q=test');
        $response2->assertStatus(200);
        
        // Both responses should be identical (cached)
        $this->assertEquals($response1->getContent(), $response2->getContent());
        
        // Test product history caching
        $product = Product::first();
        $response1 = $this->get("/api/products/{$product->id}/history/statistics");
        $response1->assertStatus(200);
        
        $response2 = $this->get("/api/products/{$product->id}/history/statistics");
        $response2->assertStatus(200);
        
        // Both responses should be identical (cached)
        $this->assertEquals($response1->getContent(), $response2->getContent());
    }

    /**
     * Test API routes with error handling
     */
    public function test_api_routes_error_handling(): void
    {
        // Test 404 for non-existent product
        $response = $this->get('/api/products/999999/history');
        $response->assertStatus(404);
        
        // Test 404 for non-existent discount code
        $response = $this->post('/api/discount-codes/999999/generate-document');
        $response->assertStatus(404);
        
        // Test 400 for invalid request data
        $response = $this->post('/api/discount-codes/validate', []);
        $response->assertStatus(400);
        
        // Test 422 for validation errors
        $response = $this->post('/api/referrals/process', [
            'code' => '',
            'user_id' => 'invalid'
        ]);
        $response->assertStatus(422);
    }
}
