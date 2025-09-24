<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Campaign;
use App\Models\CampaignConversion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CampaignConversionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->campaign = Campaign::factory()->create();
        $this->campaignConversion = CampaignConversion::factory()->create([
            'campaign_id' => $this->campaign->id,
            'customer_id' => $this->user->id,
            'conversion_value' => 100.5,
            'conversion_type' => 'purchase',
            'status' => 'completed',
        ]);
    }

    public function test_campaign_conversion_belongs_to_campaign(): void
    {
        $this->assertInstanceOf(Campaign::class, $this->campaignConversion->campaign);
        $this->assertEquals($this->campaign->id, $this->campaignConversion->campaign->id);
    }

    public function test_campaign_conversion_belongs_to_customer(): void
    {
        $this->assertInstanceOf(User::class, $this->campaignConversion->customer);
        $this->assertEquals($this->user->id, $this->campaignConversion->customer->id);
    }

    public function test_campaign_conversion_can_calculate_roi(): void
    {
        $roi = $this->campaignConversion->calculateRoi(50.0);
        $this->assertEquals(1.01, $roi);  // (100.50 - 50) / 50 = 1.01
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

    public function test_campaign_conversion_can_get_formatted_conversion_value(): void
    {
        $formatted = $this->campaignConversion->formatted_conversion_value;
        $this->assertEquals('€100.50', $formatted);
    }

    public function test_campaign_conversion_can_get_formatted_roi(): void
    {
        $this->campaignConversion->roi = 0.15;
        $formatted = $this->campaignConversion->formatted_roi;
        $this->assertEquals('15.00%', $formatted);
    }

    public function test_campaign_conversion_can_get_formatted_conversion_rate(): void
    {
        $this->campaignConversion->conversion_rate = 0.05;
        $formatted = $this->campaignConversion->formatted_conversion_rate;
        $this->assertEquals('5.00%', $formatted);
    }

    public function test_campaign_conversion_can_get_device_type_display(): void
    {
        $this->campaignConversion->device_type = 'mobile';
        $display = $this->campaignConversion->device_type_display;
        $this->assertStringContainsString('mobile', strtolower($display));
    }

    public function test_campaign_conversion_can_get_conversion_type_display(): void
    {
        $display = $this->campaignConversion->conversion_type_display;
        $this->assertStringContainsString('purchase', strtolower($display));
    }

    public function test_campaign_conversion_can_get_status_display(): void
    {
        $display = $this->campaignConversion->status_display;
        $this->assertStringContainsString('completed', strtolower($display));
    }

    public function test_campaign_conversion_can_handle_json_casts(): void
    {
        $jsonData = ['key' => 'value', 'number' => 123];

        $this->campaignConversion->conversion_data = $jsonData;
        $this->campaignConversion->save();

        $this->assertEquals($jsonData, $this->campaignConversion->fresh()->conversion_data);
    }

    public function test_campaign_conversion_can_handle_array_casts(): void
    {
        $tags = ['tag1', 'tag2', 'tag3'];
        $customAttributes = ['custom_key' => 'custom_value'];

        $this->campaignConversion->tags = $tags;
        $this->campaignConversion->custom_attributes = $customAttributes;
        $this->campaignConversion->save();

        $this->assertEquals($tags, $this->campaignConversion->fresh()->tags);
        $this->assertEquals($customAttributes, $this->campaignConversion->fresh()->custom_attributes);
    }

    public function test_campaign_conversion_can_handle_boolean_casts(): void
    {
        $this->campaignConversion->is_mobile = true;
        $this->campaignConversion->is_tablet = false;
        $this->campaignConversion->is_desktop = true;
        $this->campaignConversion->save();

        $this->assertTrue($this->campaignConversion->fresh()->is_mobile);
        $this->assertFalse($this->campaignConversion->fresh()->is_tablet);
        $this->assertTrue($this->campaignConversion->fresh()->is_desktop);
    }

    public function test_campaign_conversion_can_handle_decimal_casts(): void
    {
        $this->campaignConversion->conversion_value = 123.45;
        $this->campaignConversion->bounce_rate = 0.25;
        $this->campaignConversion->save();

        $this->assertEquals(123.45, $this->campaignConversion->fresh()->conversion_value);
        $this->assertEquals(0.25, $this->campaignConversion->fresh()->bounce_rate);
    }

    public function test_campaign_conversion_can_handle_datetime_casts(): void
    {
        $now = now();
        $this->campaignConversion->converted_at = $now;
        $this->campaignConversion->save();

        $this->assertEquals($now->format('Y-m-d H:i:s'), $this->campaignConversion->fresh()->converted_at->format('Y-m-d H:i:s'));
    }

    public function test_campaign_conversion_can_handle_integer_casts(): void
    {
        $this->campaignConversion->conversion_duration = 300;
        $this->campaignConversion->page_views = 5;
        $this->campaignConversion->time_on_site = 120;
        $this->campaignConversion->save();

        $this->assertEquals(300, $this->campaignConversion->fresh()->conversion_duration);
        $this->assertEquals(5, $this->campaignConversion->fresh()->page_views);
        $this->assertEquals(120, $this->campaignConversion->fresh()->time_on_site);
    }

    public function test_campaign_conversion_scope_by_campaign(): void
    {
        $conversions = CampaignConversion::byCampaign($this->campaign->id)->get();

        $this->assertCount(1, $conversions);
        $this->assertEquals($this->campaignConversion->id, $conversions->first()->id);
    }

    public function test_campaign_conversion_scope_by_type(): void
    {
        $conversions = CampaignConversion::byType('purchase')->get();

        $this->assertCount(1, $conversions);
        $this->assertEquals($this->campaignConversion->id, $conversions->first()->id);
    }

    public function test_campaign_conversion_scope_by_status(): void
    {
        $conversions = CampaignConversion::byStatus('completed')->get();

        $this->assertCount(1, $conversions);
        $this->assertEquals($this->campaignConversion->id, $conversions->first()->id);
    }

    public function test_campaign_conversion_scope_by_date_range(): void
    {
        $startDate = now()->subDays(1)->format('Y-m-d');
        $endDate = now()->addDays(1)->format('Y-m-d');

        $conversions = CampaignConversion::byDateRange($startDate, $endDate)->get();

        $this->assertCount(1, $conversions);
        $this->assertEquals($this->campaignConversion->id, $conversions->first()->id);
    }

    public function test_campaign_conversion_scope_by_device_type(): void
    {
        $this->campaignConversion->device_type = 'mobile';
        $this->campaignConversion->save();

        $conversions = CampaignConversion::byDeviceType('mobile')->get();

        $this->assertCount(1, $conversions);
        $this->assertEquals($this->campaignConversion->id, $conversions->first()->id);
    }

    public function test_campaign_conversion_scope_by_source(): void
    {
        $this->campaignConversion->source = 'google';
        $this->campaignConversion->save();

        $conversions = CampaignConversion::bySource('google')->get();

        $this->assertCount(1, $conversions);
        $this->assertEquals($this->campaignConversion->id, $conversions->first()->id);
    }

    public function test_campaign_conversion_scope_by_medium(): void
    {
        $this->campaignConversion->medium = 'cpc';
        $this->campaignConversion->save();

        $conversions = CampaignConversion::byMedium('cpc')->get();

        $this->assertCount(1, $conversions);
        $this->assertEquals($this->campaignConversion->id, $conversions->first()->id);
    }

    public function test_campaign_conversion_scope_high_value(): void
    {
        $conversions = CampaignConversion::highValue(50.0)->get();

        $this->assertCount(1, $conversions);
        $this->assertEquals($this->campaignConversion->id, $conversions->first()->id);
    }

    public function test_campaign_conversion_scope_recent(): void
    {
        $conversions = CampaignConversion::recent(30)->get();

        $this->assertCount(1, $conversions);
        $this->assertEquals($this->campaignConversion->id, $conversions->first()->id);
    }

    public function test_campaign_conversion_can_handle_zero_cost_roi(): void
    {
        $roi = $this->campaignConversion->calculateRoi(0);
        $this->assertEquals(0, $roi);
    }

    public function test_campaign_conversion_can_handle_zero_cost_roas(): void
    {
        $roas = $this->campaignConversion->calculateRoas(0);
        $this->assertEquals(0, $roas);
    }

    public function test_campaign_conversion_can_handle_negative_cost_roi(): void
    {
        $roi = $this->campaignConversion->calculateRoi(-10);
        $this->assertEquals(0, $roi);
    }

    public function test_campaign_conversion_can_handle_negative_cost_roas(): void
    {
        $roas = $this->campaignConversion->calculateRoas(-10);
        $this->assertEquals(0, $roas);
    }

    public function test_campaign_conversion_fillable_attributes(): void
    {
        $fillable = $this->campaignConversion->getFillable();

        $this->assertContains('campaign_id', $fillable);
        $this->assertContains('customer_id', $fillable);
        $this->assertContains('conversion_type', $fillable);
        $this->assertContains('conversion_value', $fillable);
        $this->assertContains('status', $fillable);
        $this->assertContains('converted_at', $fillable);
    }

    public function test_campaign_conversion_has_timestamps_disabled(): void
    {
        $this->assertFalse($this->campaignConversion->timestamps);
    }

    public function test_campaign_conversion_has_translation_model(): void
    {
        $this->assertEquals(
            \App\Models\Translations\CampaignConversionTranslation::class,
            $this->campaignConversion->translationModel
        );
    }
}
