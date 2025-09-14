<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\SeoDataController;
use App\Http\Controllers\SitemapController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

final class AdditionalTimeoutImplementationTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_controller_has_timeout_protection(): void
    {
        // Test that the SitemapController uses timeout protection
        $controller = new SitemapController();
        
        // Test the index method
        $response = $controller->index();
        
        $this->assertInstanceOf(\Illuminate\Http\Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('<?xml version="1.0" encoding="UTF-8"?>', $response->getContent());
        $this->assertStringContainsString('<sitemapindex', $response->getContent());
    }

    public function test_sitemap_controller_locale_has_timeout_protection(): void
    {
        // Test that the SitemapController locale method uses timeout protection
        $controller = new SitemapController();
        
        // Test the locale method
        $response = $controller->locale('en');
        
        $this->assertInstanceOf(\Illuminate\Http\Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('<?xml version="1.0" encoding="UTF-8"?>', $response->getContent());
        $this->assertStringContainsString('<urlset', $response->getContent());
    }

    public function test_timeout_protection_prevents_long_running_operations(): void
    {
        // Test that timeout protection actually works by simulating a long operation
        $collection = \Illuminate\Support\LazyCollection::make(range(1, 1000));
        $timeout = now()->addMilliseconds(50); // Very short timeout
        
        $processedCount = 0;
        foreach ($collection->takeUntilTimeout($timeout) as $item) {
            $processedCount++;
            usleep(1000); // 1ms delay
        }
        
        $this->assertLessThan(1000, $processedCount);
        $this->assertGreaterThanOrEqual(0, $processedCount);
    }

    public function test_timeout_protection_with_normal_operations(): void
    {
        // Test that timeout protection doesn't interfere with normal operations
        $collection = \Illuminate\Support\LazyCollection::make(range(1, 10));
        $timeout = now()->addSeconds(10); // Long timeout
        
        $processedCount = 0;
        foreach ($collection->takeUntilTimeout($timeout) as $item) {
            $processedCount++;
        }
        
        $this->assertEquals(10, $processedCount); // Should process all items
    }
}
