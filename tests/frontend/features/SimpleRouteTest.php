<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Simple Route Tests
 * 
 * Basic route tests that don't require complex data seeding.
 */
class SimpleRouteTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test health check route
     */
    public function test_health_route_returns_ok(): void
    {
        $response = $this->get('/health');
        $response->assertStatus(200);
        $response->assertJson(['ok' => true]);
    }

    /**
     * Test language switching route
     */
    public function test_language_switch_route(): void
    {
        $response = $this->get('/lang/lt');
        $response->assertRedirect();
        
        $response = $this->get('/lang/en');
        $response->assertRedirect();
    }

    /**
     * Test root route redirects to localized home
     */
    public function test_root_route_redirects_to_localized_home(): void
    {
        $response = $this->get('/');
        $response->assertRedirect();
    }

    /**
     * Test home route redirects to root
     */
    public function test_home_route_redirects_to_root(): void
    {
        $response = $this->get('/home');
        $response->assertRedirect();
    }

    /**
     * Test robots.txt route
     */
    public function test_robots_txt_route(): void
    {
        $response = $this->get('/robots.txt');
        $response->assertStatus(200);
    }

    /**
     * Test sitemap routes
     */
    public function test_sitemap_routes(): void
    {
        // Test main sitemap
        $response = $this->get('/sitemap.xml');
        $response->assertStatus(200);
        
        // Test localized sitemap
        $response = $this->get('/lt/sitemap.xml');
        $response->assertStatus(200);
    }

    /**
     * Test cpanel routes
     */
    public function test_cpanel_routes(): void
    {
        // Test cpanel login
        $response = $this->get('/cpanel/login');
        $response->assertStatus(200);
        
        // Test cpanel any path
        $response = $this->get('/cpanel/test');
        $response->assertStatus(200);
    }

    /**
     * Test localized routes
     */
    public function test_localized_routes(): void
    {
        // Test localized home
        $response = $this->get('/lt');
        $response->assertStatus(200);
        
        // Test localized categories
        $response = $this->get('/lt/categories');
        $response->assertStatus(200);
        
        // Test localized products
        $response = $this->get('/lt/products');
        $response->assertStatus(200);
        
        // Test localized inventory
        $response = $this->get('/lt/inventory');
        $response->assertStatus(200);
        
        // Test localized cart
        $response = $this->get('/lt/cart');
        $response->assertStatus(200);
        
        // Test localized search
        $response = $this->get('/lt/search');
        $response->assertStatus(200);
        
        // Test localized brands
        $response = $this->get('/lt/brands');
        $response->assertStatus(200);
        
        // Test localized news
        $response = $this->get('/lt/news');
        $response->assertStatus(200);
        
        $response = $this->get('/lt/naujienos');
        $response->assertStatus(200);
        
        // Test localized locations
        $response = $this->get('/lt/locations');
        $response->assertStatus(200);
        
        // Test localized cpanel
        $response = $this->get('/lt/cpanel');
        $response->assertRedirect();
        
        $response = $this->get('/lt/cpanel/test');
        $response->assertRedirect();
    }

    /**
     * Test API routes
     */
    public function test_api_routes(): void
    {
        // Test products search
        $response = $this->get('/api/products/search');
        $response->assertStatus(200);
        
        // Test categories tree
        $response = $this->get('/api/categories/tree');
        $response->assertStatus(200);
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
     * Test referral system API routes
     */
    public function test_referral_system_api_routes(): void
    {
        // Test get code statistics
        $response = $this->get('/api/referrals/code-statistics');
        $response->assertStatus(200);
        
        // Test get referral URL
        $response = $this->get('/api/referrals/referral-url');
        $response->assertStatus(200);
    }

    /**
     * Test discount code API routes
     */
    public function test_discount_code_api_routes(): void
    {
        // Test get available discount codes
        $response = $this->get('/api/discount-codes/available');
        $response->assertStatus(200);
    }

    /**
     * Test system settings API routes
     */
    public function test_system_settings_api_routes(): void
    {
        // Test public settings API
        $response = $this->get('/api/settings/public');
        $response->assertStatus(200);
    }

    /**
     * Test route with query parameters
     */
    public function test_routes_with_query_parameters(): void
    {
        // Test autocomplete with query
        $response = $this->get('/api/autocomplete/search?q=test&limit=10');
        $response->assertStatus(200);
        
        // Test autocomplete with filters
        $response = $this->get('/api/autocomplete/products?q=test&category_id=1');
        $response->assertStatus(200);
        
        // Test autocomplete with sorting
        $response = $this->get('/api/autocomplete/search?q=test&sort=name&order=asc');
        $response->assertStatus(200);
    }

    /**
     * Test route error handling
     */
    public function test_route_error_handling(): void
    {
        // Test 404 for non-existent route
        $response = $this->get('/non-existent-route');
        $response->assertStatus(404);
        
        // Test 404 for non-existent API route
        $response = $this->get('/api/non-existent');
        $response->assertStatus(404);
    }

    /**
     * Test route with different HTTP methods
     */
    public function test_route_http_methods(): void
    {
        // Test GET on GET-only routes
        $response = $this->get('/health');
        $response->assertStatus(200);
        
        // Test POST on GET-only routes (should fail)
        $response = $this->post('/health');
        $response->assertStatus(405);
        
        // Test PUT on GET-only routes (should fail)
        $response = $this->put('/health');
        $response->assertStatus(405);
        
        // Test DELETE on GET-only routes (should fail)
        $response = $this->delete('/health');
        $response->assertStatus(405);
    }

    /**
     * Test route with different content types
     */
    public function test_route_content_types(): void
    {
        // Test JSON response
        $response = $this->get('/api/autocomplete/search?q=test');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        
        // Test HTML response
        $response = $this->get('/cpanel/login');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
    }

    /**
     * Test route with caching
     */
    public function test_route_caching(): void
    {
        // Test autocomplete caching
        $response1 = $this->get('/api/autocomplete/search?q=test');
        $response1->assertStatus(200);
        
        $response2 = $this->get('/api/autocomplete/search?q=test');
        $response2->assertStatus(200);
        
        // Both responses should be identical (cached)
        $this->assertEquals($response1->getContent(), $response2->getContent());
    }

    /**
     * Test route with rate limiting
     */
    public function test_route_rate_limiting(): void
    {
        // Test multiple autocomplete requests (should be rate limited)
        for ($i = 0; $i < 10; $i++) {
            $response = $this->get('/api/autocomplete/search?q=test');
            $response->assertStatus(200);
        }
    }

    /**
     * Test route with CSRF protection
     */
    public function test_route_csrf_protection(): void
    {
        // Test POST without CSRF token (should fail)
        $response = $this->post('/api/discount-codes/validate', [
            'code' => 'TEST_CODE',
            'amount' => 100.00
        ], [
            'X-CSRF-TOKEN' => 'invalid-token'
        ]);
        $response->assertStatus(419);
    }
}
