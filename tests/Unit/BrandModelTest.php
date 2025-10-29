<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Brand;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Focused coverage for the brand model helpers that manage premium state and social links.
 */
final class BrandModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The social_links mutator should discard unsupported platforms and blank URLs.
     */
    public function test_social_links_are_normalized_and_filtered(): void
    {
        $brand = Brand::factory()->create([
            'is_enabled'   => true,
            'is_active'    => true,
            'is_visible'   => true,
            'social_links' => [
                ['platform' => 'facebook', 'url' => 'https://facebook.com/test-brand'],
                ['platform' => 'unknown',  'url' => 'https://example.com/invalid'],
                ['platform' => 'instagram','url' => ''],
            ],
        ]);

        $this->assertSame([
            ['platform' => 'facebook', 'url' => 'https://facebook.com/test-brand'],
        ], $brand->fresh()->social_links);
    }

    /**
     * The premium scope should only surface brands marked as premium.
     */
    public function test_premium_scope_filters_records(): void
    {
        $premium = Brand::factory()->create([
            'is_enabled' => true,
            'is_active'  => true,
            'is_visible' => true,
            'is_premium' => true,
        ]);

        $regular = Brand::factory()->create([
            'is_enabled' => true,
            'is_active'  => true,
            'is_visible' => true,
            'is_premium' => false,
        ]);

        $this->assertTrue(Brand::premium()->whereKey($premium)->exists());
        $this->assertFalse(Brand::premium()->whereKey($regular)->exists());
    }

    /**
     * Complete info payloads should include social metadata and premium flags.
     */
    public function test_complete_info_includes_social_and_premium_metadata(): void
    {
        $brand = Brand::factory()->create([
            'is_enabled'   => true,  // satisfy EnabledScope
            'is_active'    => true,  // satisfy ActiveScope
            'is_visible'   => true,
            'is_premium'   => true,
            'social_links' => [
                ['platform' => 'instagram', 'url' => 'https://instagram.com/test-brand'],
            ],
        ]);

        // reload persisted row without global scopes to avoid suite-order interference
        $brandUnscoped = \App\Models\Brand::withoutGlobalScopes()->findOrFail($brand->getKey());

        $payload = $brandUnscoped->getCompleteInfo();

        // core expectations (unchanged intent)
        $this->assertTrue($payload['is_premium']);

        // count must be exactly 1
        $this->assertSame(1, $payload['social_links_count']);
        $this->assertIsArray($payload['social_links']);
        $this->assertCount(1, $payload['social_links']);

        // order-agnostic platform check (still requires instagram to be present)
        $platforms = array_map(static fn ($l) => $l['platform'] ?? null, $payload['social_links']);
        $this->assertContains('instagram', $platforms);
    }


}
