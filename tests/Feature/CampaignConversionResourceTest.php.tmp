<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Campaign;
use App\Models\CampaignConversion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CampaignConversionResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data
        $this->user = User::factory()->create();
        $this->campaign = Campaign::factory()->create();
        $this->campaignConversion = CampaignConversion::factory()->create([
            'campaign_id' => $this->campaign->id,
            'customer_id' => $this->user->id,
        ]);
    }

    public function test_can_list_campaign_conversions(): void
    {
        $this->actingAs($this->user);

        $response = $this->get('/admin/campaign-conversions');

        $response->assertOk();
        $response->assertSee($this->campaignConversion->campaign_name);
    }

    public function test_can_view_campaign_conversion(): void
    {
        $this->actingAs($this->user);

        $response = $this->get("/admin/campaign-conversions/{$this->campaignConversion->id}");

        $response->assertOk();
        $response->assertSee($this->campaignConversion->campaign_name);
    }

    public function test_can_create_campaign_conversion(): void
    {
        $this->actingAs($this->user);

        $data = [
            'campaign_id' => $this->campaign->id,
            'customer_id' => $this->user->id,
            'conversion_type' => 'purchase',
            'conversion_value' => 100.5,
            'status' => 'completed',
            'converted_at' => now(),
        ];

        $response = $this->post('/admin/campaign-conversions', $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('campaign_conversions', [
            'campaign_id' => $this->campaign->id,
            'customer_id' => $this->user->id,
            'conversion_type' => 'purchase',
            'conversion_value' => 100.5,
        ]);
    }

    public function test_can_edit_campaign_conversion(): void
    {
        $this->actingAs($this->user);

        $data = [
            'conversion_value' => 200.75,
            'status' => 'confirmed',
        ];

        $response = $this->put("/admin/campaign-conversions/{$this->campaignConversion->id}", $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('campaign_conversions', [
            'id' => $this->campaignConversion->id,
            'conversion_value' => 200.75,
            'status' => 'confirmed',
        ]);
    }

    public function test_can_delete_campaign_conversion(): void
    {
        $this->actingAs($this->user);

        $response = $this->delete("/admin/campaign-conversions/{$this->campaignConversion->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('campaign_conversions', [
            'id' => $this->campaignConversion->id,
        ]);
    }

    public function test_can_filter_by_campaign(): void
    {
        $this->actingAs($this->user);

        $response = $this->get("/admin/campaign-conversions?campaign_id={$this->campaign->id}");

        $response->assertOk();
        $response->assertSee($this->campaignConversion->campaign_name);
    }

    public function test_can_filter_by_conversion_type(): void
    {
        $this->actingAs($this->user);

        $response = $this->get('/admin/campaign-conversions?conversion_type=purchase');

        $response->assertOk();
        $response->assertSee($this->campaignConversion->campaign_name);
    }

    public function test_can_filter_by_status(): void
    {
        $this->actingAs($this->user);

        $response = $this->get('/admin/campaign-conversions?status=completed');

        $response->assertOk();
        $response->assertSee($this->campaignConversion->campaign_name);
    }

    public function test_can_filter_by_device_type(): void
    {
        $this->actingAs($this->user);

        $response = $this->get('/admin/campaign-conversions?device_type=mobile');

        $response->assertOk();
    }

    public function test_can_filter_by_country(): void
    {
        $this->actingAs($this->user);

        $response = $this->get('/admin/campaign-conversions?country=LT');

        $response->assertOk();
    }

    public function test_can_filter_by_utm_source(): void
    {
        $this->actingAs($this->user);

        $response = $this->get('/admin/campaign-conversions?utm_source=google');

        $response->assertOk();
    }

    public function test_can_filter_by_utm_medium(): void
    {
        $this->actingAs($this->user);

        $response = $this->get('/admin/campaign-conversions?utm_medium=cpc');

        $response->assertOk();
    }

    public function test_can_filter_by_attribution_model(): void
    {
        $this->actingAs($this->user);

        $response = $this->get('/admin/campaign-conversions?attribution_model=last_click');

        $response->assertOk();
    }

    public function test_can_filter_verified_only(): void
    {
        $this->actingAs($this->user);

        $response = $this->get('/admin/campaign-conversions?is_verified=1');

        $response->assertOk();
    }

    public function test_can_filter_attributed_only(): void
    {
        $this->actingAs($this->user);

        $response = $this->get('/admin/campaign-conversions?is_attributed=1');

        $response->assertOk();
    }

    public function test_can_filter_mobile_only(): void
    {
        $this->actingAs($this->user);

        $response = $this->get('/admin/campaign-conversions?is_mobile=1');

        $response->assertOk();
    }

    public function test_can_filter_tablet_only(): void
    {
        $this->actingAs($this->user);

        $response = $this->get('/admin/campaign-conversions?is_tablet=1');

        $response->assertOk();
    }

    public function test_can_filter_desktop_only(): void
    {
        $this->actingAs($this->user);

        $response = $this->get('/admin/campaign-conversions?is_desktop=1');

        $response->assertOk();
    }

    public function test_can_verify_conversion(): void
    {
        $this->actingAs($this->user);

        $response = $this->post("/admin/campaign-conversions/{$this->campaignConversion->id}/verify");

        $response->assertRedirect();
        $this->assertDatabaseHas('campaign_conversions', [
            'id' => $this->campaignConversion->id,
            'is_verified' => true,
        ]);
    }

    public function test_can_unverify_conversion(): void
    {
        $this->campaignConversion->update(['is_verified' => true]);
        $this->actingAs($this->user);

        $response = $this->post("/admin/campaign-conversions/{$this->campaignConversion->id}/unverify");

        $response->assertRedirect();
        $this->assertDatabaseHas('campaign_conversions', [
            'id' => $this->campaignConversion->id,
            'is_verified' => false,
        ]);
    }

    public function test_can_attribute_conversion(): void
    {
        $this->actingAs($this->user);

        $response = $this->post("/admin/campaign-conversions/{$this->campaignConversion->id}/attribute");

        $response->assertRedirect();
        $this->assertDatabaseHas('campaign_conversions', [
            'id' => $this->campaignConversion->id,
            'is_attributed' => true,
        ]);
    }

    public function test_can_unattribute_conversion(): void
    {
        $this->campaignConversion->update(['is_attributed' => true]);
        $this->actingAs($this->user);

        $response = $this->post("/admin/campaign-conversions/{$this->campaignConversion->id}/unattribute");

        $response->assertRedirect();
        $this->assertDatabaseHas('campaign_conversions', [
            'id' => $this->campaignConversion->id,
            'is_attributed' => false,
        ]);
    }

    public function test_can_bulk_verify_conversions(): void
    {
        $this->actingAs($this->user);

        $response = $this->post('/admin/campaign-conversions/bulk-verify', [
            'ids' => [$this->campaignConversion->id],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('campaign_conversions', [
            'id' => $this->campaignConversion->id,
            'is_verified' => true,
        ]);
    }

    public function test_can_bulk_unverify_conversions(): void
    {
        $this->campaignConversion->update(['is_verified' => true]);
        $this->actingAs($this->user);

        $response = $this->post('/admin/campaign-conversions/bulk-unverify', [
            'ids' => [$this->campaignConversion->id],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('campaign_conversions', [
            'id' => $this->campaignConversion->id,
            'is_verified' => false,
        ]);
    }

    public function test_can_bulk_attribute_conversions(): void
    {
        $this->actingAs($this->user);

        $response = $this->post('/admin/campaign-conversions/bulk-attribute', [
            'ids' => [$this->campaignConversion->id],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('campaign_conversions', [
            'id' => $this->campaignConversion->id,
            'is_attributed' => true,
        ]);
    }

    public function test_can_bulk_unattribute_conversions(): void
    {
        $this->campaignConversion->update(['is_attributed' => true]);
        $this->actingAs($this->user);

        $response = $this->post('/admin/campaign-conversions/bulk-unattribute', [
            'ids' => [$this->campaignConversion->id],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('campaign_conversions', [
            'id' => $this->campaignConversion->id,
            'is_attributed' => false,
        ]);
    }

    public function test_can_search_campaign_conversions(): void
    {
        $this->actingAs($this->user);

        $response = $this->get("/admin/campaign-conversions?search={$this->campaignConversion->campaign_name}");

        $response->assertOk();
        $response->assertSee($this->campaignConversion->campaign_name);
    }

    public function test_can_sort_campaign_conversions(): void
    {
        $this->actingAs($this->user);

        $response = $this->get('/admin/campaign-conversions?sort=converted_at&direction=desc');

        $response->assertOk();
    }

    public function test_can_export_campaign_conversions(): void
    {
        $this->actingAs($this->user);

        $response = $this->get('/admin/campaign-conversions/export');

        $response->assertOk();
    }

    public function test_can_import_campaign_conversions(): void
    {
        $this->actingAs($this->user);

        $response = $this->get('/admin/campaign-conversions/import');

        $response->assertOk();
    }

    public function test_campaign_conversion_has_required_relationships(): void
    {
        $this->assertInstanceOf(Campaign::class, $this->campaignConversion->campaign);
        $this->assertInstanceOf(User::class, $this->campaignConversion->customer);
    }

    public function test_campaign_conversion_can_calculate_roi(): void
    {
        $roi = $this->campaignConversion->calculateRoi(50.0);
        $this->assertEquals(1.0, $roi);  // (100.50 - 50) / 50 = 1.0
    }

    public function test_campaign_conversion_can_calculate_roas(): void
    {
        $roas = $this->campaignConversion->calculateRoas(50.0);
        $this->assertEquals(2.01, $roas);  // 100.50 / 50 = 2.01
    }

    public function test_campaign_conversion_can_check_high_value(): void
    {
        $this->assertTrue($this->campaignConversion->isHighValue(50.0));
        $this->assertFalse($this->campaignConversion->isHighValue(200.0));
    }

    public function test_campaign_conversion_can_check_recent(): void
    {
        $this->assertTrue($this->campaignConversion->isRecent(30));
        $this->assertFalse($this->campaignConversion->isRecent(0));
    }

    public function test_campaign_conversion_can_get_attribution_value(): void
    {
        $value = $this->campaignConversion->getAttributionValue('last_click');
        $this->assertIsFloat($value);
    }

    public function test_campaign_conversion_form_validation(): void
    {
        $this->actingAs($this->user);

        $response = $this->post('/admin/campaign-conversions', []);

        $response->assertSessionHasErrors(['campaign_id', 'conversion_type', 'conversion_value', 'status']);
    }

    public function test_campaign_conversion_edit_form_validation(): void
    {
        $this->actingAs($this->user);

        $response = $this->put("/admin/campaign-conversions/{$this->campaignConversion->id}", [
            'conversion_value' => 'invalid',
        ]);

        $response->assertSessionHasErrors(['conversion_value']);
    }

    public function test_campaign_conversion_can_handle_json_data(): void
    {
        $jsonData = ['key' => 'value', 'number' => 123];

        $this->campaignConversion->update(['conversion_data' => $jsonData]);

        $this->assertEquals($jsonData, $this->campaignConversion->conversion_data);
    }

    public function test_campaign_conversion_can_handle_tags(): void
    {
        $tags = ['tag1', 'tag2', 'tag3'];

        $this->campaignConversion->update(['tags' => $tags]);

        $this->assertEquals($tags, $this->campaignConversion->tags);
    }

    public function test_campaign_conversion_can_handle_custom_attributes(): void
    {
        $customAttributes = ['custom_key' => 'custom_value'];

        $this->campaignConversion->update(['custom_attributes' => $customAttributes]);

        $this->assertEquals($customAttributes, $this->campaignConversion->custom_attributes);
    }
}
