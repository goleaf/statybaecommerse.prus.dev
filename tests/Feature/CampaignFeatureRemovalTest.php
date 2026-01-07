<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\FeatureToggleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Schema;
use Tests\TestCase;

/**
 * Test campaign feature removal and deprecation handling
 */
class CampaignFeatureRemovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_campaigns_feature_is_disabled(): void
    {
        $featureService = app(FeatureToggleService::class);

        $this->assertFalse($featureService->isCampaignsEnabled());
        $this->assertFalse($featureService->isEnabled('campaigns'));
    }

    public function test_campaign_dependent_features_still_work(): void
    {
        $featureService = app(FeatureToggleService::class);
        $dependentFeatures = $featureService->getCampaignDependentFeatures();

        // These features should still be available
        $this->assertArrayHasKey('analytics', $dependentFeatures);
        $this->assertArrayHasKey('discount', $dependentFeatures);
        $this->assertArrayHasKey('customer_groups', $dependentFeatures);
    }

    public function test_admin_widgets_handle_missing_campaign_data(): void
    {
        // Create an admin user and authenticate
        $adminUser = \App\Models\User::factory()->create([
            'email'    => 'admin@test.com',
            'is_admin' => true,
        ]);

        $this->actingAs($adminUser);

        // Test that widgets don't break when campaign models are missing
        $this->get('/admin')
            ->assertStatus(200);
    }

    public function test_campaign_archive_table_exists(): void
    {
        $this->assertTrue(
            Schema::hasTable('campaign_data_archive'),
            'Campaign data archive table should exist for data preservation'
        );
    }

    public function test_deprecated_campaign_functionality_logs_warnings(): void
    {
        // This would test that deprecated campaign methods log warnings
        // Implementation depends on specific usage patterns
        $this->assertTrue(true); // Placeholder
    }
}
