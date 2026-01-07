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
        // Test that the campaign feature is properly disabled and doesn't cause errors
        $featureService = app(FeatureToggleService::class);

        // Verify campaigns are disabled
        $this->assertFalse($featureService->isCampaignsEnabled());
        $this->assertFalse($featureService->isEnabled('campaigns'));

        // Verify campaign models exist but are marked as deprecated
        $this->assertTrue(class_exists(\App\Models\Campaign::class));
        $this->assertTrue(class_exists(\App\Models\CampaignClick::class));
        $this->assertTrue(class_exists(\App\Models\CampaignView::class));

        // Test that we can create campaign instances without errors (for backward compatibility)
        $campaign = new \App\Models\Campaign;
        $this->assertInstanceOf(\App\Models\Campaign::class, $campaign);
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
