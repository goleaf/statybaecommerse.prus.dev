<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Models\CampaignConversion;
use App\Models\Campaign;
use App\Models\Order;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CampaignConversionResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->actingAs(User::factory()->create());
    }

    public function test_can_list_campaign_conversions(): void
    {
        $conversions = CampaignConversion::factory()->count(3)->create();

        $response = $this->get('/admin/campaign-conversions');

        $response->assertOk();
        $response->assertSee($conversions->first()->id);
    }

    public function test_can_create_campaign_conversion(): void
    {
        $campaign = Campaign::factory()->create();
        $customer = Customer::factory()->create();
        $order = Order::factory()->create();

        $conversionData = [
            'campaign_id' => $campaign->id,
            'customer_id' => $customer->id,
            'order_id' => $order->id,
            'conversion_type' => 'purchase',
            'conversion_value' => 150.50,
            'status' => 'completed',
            'converted_at' => now()->format('Y-m-d H:i:s'),
            'source' => 'google',
            'medium' => 'cpc',
            'device_type' => 'desktop',
            'country' => 'LT',
            'city' => 'Vilnius',
        ];

        $response = $this->post('/admin/campaign-conversions', $conversionData);

        $this->assertDatabaseHas('campaign_conversions', [
            'campaign_id' => $campaign->id,
            'customer_id' => $customer->id,
            'order_id' => $order->id,
            'conversion_type' => 'purchase',
            'conversion_value' => 150.50,
            'status' => 'completed',
            'source' => 'google',
            'medium' => 'cpc',
            'device_type' => 'desktop',
            'country' => 'LT',
            'city' => 'Vilnius',
        ]);
    }

    public function test_can_view_campaign_conversion(): void
    {
        $conversion = CampaignConversion::factory()->create();

        $response = $this->get("/admin/campaign-conversions/{$conversion->id}");

        $response->assertOk();
        $response->assertSee($conversion->id);
        $response->assertSee($conversion->conversion_type);
        $response->assertSee($conversion->conversion_value);
    }

    public function test_can_edit_campaign_conversion(): void
    {
        $conversion = CampaignConversion::factory()->create([
            'conversion_value' => 100,
            'status' => 'pending',
        ]);

        $updateData = [
            'conversion_value' => 200,
            'status' => 'completed',
            'source' => 'facebook',
            'medium' => 'social',
        ];

        $response = $this->put("/admin/campaign-conversions/{$conversion->id}", $updateData);

        $this->assertDatabaseHas('campaign_conversions', [
            'id' => $conversion->id,
            'conversion_value' => 200,
            'status' => 'completed',
            'source' => 'facebook',
            'medium' => 'social',
        ]);
    }

    public function test_can_delete_campaign_conversion(): void
    {
        $conversion = CampaignConversion::factory()->create();

        $response = $this->delete("/admin/campaign-conversions/{$conversion->id}");

        $this->assertDatabaseMissing('campaign_conversions', [
            'id' => $conversion->id,
        ]);
    }

    public function test_can_filter_campaign_conversions_by_campaign(): void
    {
        $campaign1 = Campaign::factory()->create();
        $campaign2 = Campaign::factory()->create();

        $conversion1 = CampaignConversion::factory()->create(['campaign_id' => $campaign1->id]);
        $conversion2 = CampaignConversion::factory()->create(['campaign_id' => $campaign2->id]);

        $response = $this->get("/admin/campaign-conversions?tableFilters[campaign_id][value]={$campaign1->id}");

        $response->assertOk();
        $response->assertSee($conversion1->id);
        $response->assertDontSee($conversion2->id);
    }

    public function test_can_filter_campaign_conversions_by_type(): void
    {
        $purchaseConversion = CampaignConversion::factory()->create(['conversion_type' => 'purchase']);
        $signupConversion = CampaignConversion::factory()->create(['conversion_type' => 'signup']);

        $response = $this->get('/admin/campaign-conversions?tableFilters[conversion_type][value]=purchase');

        $response->assertOk();
        $response->assertSee($purchaseConversion->id);
        $response->assertDontSee($signupConversion->id);
    }

    public function test_can_filter_campaign_conversions_by_status(): void
    {
        $completedConversion = CampaignConversion::factory()->create(['status' => 'completed']);
        $pendingConversion = CampaignConversion::factory()->create(['status' => 'pending']);

        $response = $this->get('/admin/campaign-conversions?tableFilters[status][value]=completed');

        $response->assertOk();
        $response->assertSee($completedConversion->id);
        $response->assertDontSee($pendingConversion->id);
    }

    public function test_can_filter_campaign_conversions_by_device_type(): void
    {
        $mobileConversion = CampaignConversion::factory()->create(['device_type' => 'mobile']);
        $desktopConversion = CampaignConversion::factory()->create(['device_type' => 'desktop']);

        $response = $this->get('/admin/campaign-conversions?tableFilters[device_type][value]=mobile');

        $response->assertOk();
        $response->assertSee($mobileConversion->id);
        $response->assertDontSee($desktopConversion->id);
    }

    public function test_can_filter_campaign_conversions_by_high_value(): void
    {
        $highValueConversion = CampaignConversion::factory()->create(['conversion_value' => 500]);
        $lowValueConversion = CampaignConversion::factory()->create(['conversion_value' => 50]);

        $response = $this->get('/admin/campaign-conversions?tableFilters[high_value]=1');

        $response->assertOk();
        $response->assertSee($highValueConversion->id);
        $response->assertDontSee($lowValueConversion->id);
    }

    public function test_can_filter_campaign_conversions_by_recent(): void
    {
        $recentConversion = CampaignConversion::factory()->create(['converted_at' => now()->subDays(3)]);
        $oldConversion = CampaignConversion::factory()->create(['converted_at' => now()->subDays(10)]);

        $response = $this->get('/admin/campaign-conversions?tableFilters[recent]=1');

        $response->assertOk();
        $response->assertSee($recentConversion->id);
        $response->assertDontSee($oldConversion->id);
    }

    public function test_can_use_tabs_to_filter_conversions(): void
    {
        $completedConversion = CampaignConversion::factory()->create(['status' => 'completed']);
        $pendingConversion = CampaignConversion::factory()->create(['status' => 'pending']);
        $highValueConversion = CampaignConversion::factory()->create(['conversion_value' => 500]);
        $mobileConversion = CampaignConversion::factory()->create(['device_type' => 'mobile']);

        // Test completed tab
        $response = $this->get('/admin/campaign-conversions?activeTab=completed');
        $response->assertOk();
        $response->assertSee($completedConversion->id);
        $response->assertDontSee($pendingConversion->id);

        // Test pending tab
        $response = $this->get('/admin/campaign-conversions?activeTab=pending');
        $response->assertOk();
        $response->assertSee($pendingConversion->id);
        $response->assertDontSee($completedConversion->id);

        // Test high value tab
        $response = $this->get('/admin/campaign-conversions?activeTab=high_value');
        $response->assertOk();
        $response->assertSee($highValueConversion->id);

        // Test mobile tab
        $response = $this->get('/admin/campaign-conversions?activeTab=mobile');
        $response->assertOk();
        $response->assertSee($mobileConversion->id);
    }

    public function test_can_bulk_update_conversions(): void
    {
        $conversion1 = CampaignConversion::factory()->create(['status' => 'pending']);
        $conversion2 = CampaignConversion::factory()->create(['status' => 'pending']);

        $response = $this->post('/admin/campaign-conversions/bulk-actions/mark_completed', [
            'records' => [$conversion1->id, $conversion2->id],
        ]);

        $this->assertDatabaseHas('campaign_conversions', [
            'id' => $conversion1->id,
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('campaign_conversions', [
            'id' => $conversion2->id,
            'status' => 'completed',
        ]);
    }

    public function test_can_calculate_roi_for_conversion(): void
    {
        $conversion = CampaignConversion::factory()->create(['conversion_value' => 100]);

        $response = $this->post("/admin/campaign-conversions/{$conversion->id}/calculate-roi", [
            'cost' => 50,
        ]);

        $this->assertDatabaseHas('campaign_conversions', [
            'id' => $conversion->id,
            'roi' => 1.0, // (100-50)/50 = 1.0
        ]);
    }

    public function test_validation_works_for_required_fields(): void
    {
        $response = $this->post('/admin/campaign-conversions', []);

        $response->assertSessionHasErrors(['campaign_id', 'conversion_type', 'conversion_value', 'converted_at']);
    }

    public function test_validation_works_for_numeric_fields(): void
    {
        $campaign = Campaign::factory()->create();

        $response = $this->post('/admin/campaign-conversions', [
            'campaign_id' => $campaign->id,
            'conversion_type' => 'purchase',
            'conversion_value' => 'invalid',
            'converted_at' => now()->format('Y-m-d H:i:s'),
        ]);

        $response->assertSessionHasErrors(['conversion_value']);
    }

    public function test_can_export_conversions(): void
    {
        CampaignConversion::factory()->count(5)->create();

        $response = $this->get('/admin/campaign-conversions/export');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_campaign_conversion_relationships_work_in_admin(): void
    {
        $campaign = Campaign::factory()->create(['name' => 'Test Campaign']);
        $customer = Customer::factory()->create(['email' => 'test@example.com']);
        $order = Order::factory()->create();

        $conversion = CampaignConversion::factory()->create([
            'campaign_id' => $campaign->id,
            'customer_id' => $customer->id,
            'order_id' => $order->id,
        ]);

        $response = $this->get("/admin/campaign-conversions/{$conversion->id}");

        $response->assertOk();
        $response->assertSee('Test Campaign');
        $response->assertSee('test@example.com');
        $response->assertSee($order->id);
    }

    public function test_campaign_conversion_widgets_display_correct_data(): void
    {
        CampaignConversion::factory()->count(10)->create([
            'conversion_value' => 100,
            'converted_at' => now()->subDays(5),
        ]);

        CampaignConversion::factory()->count(5)->create([
            'conversion_value' => 200,
            'converted_at' => now()->subDays(10),
        ]);

        $response = $this->get('/admin/campaign-conversions');

        $response->assertOk();
        // Widgets should display total conversions, total value, etc.
        $response->assertSee('15'); // Total conversions
        $response->assertSee('€2,000.00'); // Total value (10*100 + 5*200)
    }

    public function test_campaign_conversion_charts_display_correct_data(): void
    {
        // Create conversions for different days
        CampaignConversion::factory()->create(['converted_at' => now()->subDays(1)]);
        CampaignConversion::factory()->create(['converted_at' => now()->subDays(2)]);
        CampaignConversion::factory()->create(['converted_at' => now()->subDays(3)]);

        $response = $this->get('/admin/campaign-conversions');

        $response->assertOk();
        // Charts should be present and contain data
        $response->assertSee('conversion_trends');
        $response->assertSee('device_breakdown');
    }
}

